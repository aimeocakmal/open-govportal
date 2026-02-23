<?php

namespace App\Livewire;

use App\Jobs\GenerateConversationTitleJob;
use App\Models\AiChatConversation;
use App\Models\AiChatMessage;
use App\Models\Setting;
use App\Services\AiService;
use App\Services\RagService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Livewire\Component;

class AiChat extends Component
{
    /** @var array<int, array{role: string, content: string}> */
    public array $messages = [];

    public string $input = '';

    public bool $isThinking = false;

    public bool $hasError = false;

    public bool $rateLimited = false;

    public bool $disclaimerAccepted = false;

    public string $preferredLanguage = '';

    public ?int $conversationId = null;

    public function mount(): void
    {
        $this->messages = session('ai_chat_messages', []);
        $this->disclaimerAccepted = (bool) session('ai_chat_disclaimer_accepted', false);
        $this->preferredLanguage = session('ai_chat_preferred_language', '');
        $this->conversationId = session('ai_chat_conversation_id');
    }

    public function send(): void
    {
        $this->hasError = false;
        $this->rateLimited = false;

        $this->validate([
            'input' => ['required', 'string', 'min:2', 'max:1000'],
        ], [
            'input.required' => __('ai.input_too_short'),
            'input.min' => __('ai.input_too_short'),
            'input.max' => __('ai.input_too_long'),
        ]);

        $ip = request()->ip();
        $rateLimitKey = "ai-chat:{$ip}";
        $rateLimit = (int) Setting::get('ai_chatbot_rate_limit', config('ai.chatbot_rate_limit', 10));

        if (RateLimiter::tooManyAttempts($rateLimitKey, $rateLimit)) {
            $this->rateLimited = true;

            return;
        }

        RateLimiter::hit($rateLimitKey, 3600);

        $this->isThinking = true;

        // Append user message
        $userMessage = trim($this->input);
        $this->messages[] = ['role' => 'user', 'content' => $userMessage];
        $this->input = '';

        $locale = $this->getResolvedLocale();

        /** @var RagService $ragService */
        $ragService = app(RagService::class);

        /** @var AiService $aiService */
        $aiService = app(AiService::class);

        // Retrieve RAG context
        $chunks = $ragService->retrieveChunks($userMessage, $locale);
        $ragContext = $ragService->buildContext($chunks);

        // Build system prompt
        $systemPrompt = $this->buildSystemPrompt($ragContext, $locale);

        // Build history (last 10 turns, excluding the current message we just added)
        $history = array_slice($this->messages, 0, -1);
        $history = array_slice($history, -10);

        // Create conversation on first message
        if ($this->conversationId === null) {
            $conversation = AiChatConversation::create([
                'session_id' => session()->getId(),
                'ip_address' => request()->ip(),
                'locale' => $locale,
                'started_at' => now(),
            ]);
            $this->conversationId = $conversation->id;
            session()->put('ai_chat_conversation_id', $this->conversationId);
        }

        // Call LLM
        $response = $aiService->chat($userMessage, $history, $systemPrompt, $locale);

        if ($response === '') {
            $this->hasError = true;
            $this->isThinking = false;
            // Remove the user message since we got no response
            array_pop($this->messages);

            return;
        }

        $usage = $aiService->getLastUsage();

        // Store messages to DB
        AiChatMessage::create([
            'conversation_id' => $this->conversationId,
            'role' => 'user',
            'content' => $userMessage,
            'created_at' => now(),
        ]);
        AiChatMessage::create([
            'conversation_id' => $this->conversationId,
            'role' => 'assistant',
            'content' => $response,
            'prompt_tokens' => $usage?->promptTokens,
            'completion_tokens' => $usage?->completionTokens,
            'duration_ms' => $usage?->durationMs,
            'created_at' => now(),
        ]);

        // Update conversation counters
        AiChatConversation::where('id', $this->conversationId)->update([
            'message_count' => DB::raw('message_count + 2'),
            'total_prompt_tokens' => DB::raw('total_prompt_tokens + '.((int) ($usage?->promptTokens ?? 0))),
            'total_completion_tokens' => DB::raw('total_completion_tokens + '.((int) ($usage?->completionTokens ?? 0))),
            'last_message_at' => now(),
        ]);

        // Dispatch title generation after 3rd exchange (6 messages)
        $conversation = AiChatConversation::find($this->conversationId);
        if ($conversation && $conversation->message_count >= 6 && $conversation->title === null) {
            GenerateConversationTitleJob::dispatch($this->conversationId);
        }

        // Append assistant response
        $this->messages[] = ['role' => 'assistant', 'content' => $response];

        // Store in session
        session()->put('ai_chat_messages', $this->messages);

        $this->isThinking = false;

        // Scroll to bottom
        $this->dispatch('ai-chat-scroll-bottom');
    }

    public function acceptDisclaimer(): void
    {
        $this->disclaimerAccepted = true;
        session()->put('ai_chat_disclaimer_accepted', true);
    }

    public function clearChat(): void
    {
        if ($this->conversationId !== null) {
            AiChatConversation::where('id', $this->conversationId)->update(['ended_at' => now()]);
        }

        $this->messages = [];
        $this->hasError = false;
        $this->rateLimited = false;
        $this->conversationId = null;
        session()->forget(['ai_chat_messages', 'ai_chat_conversation_id']);
    }

    public function setLanguage(string $lang): void
    {
        if (in_array($lang, ['ms', 'en'], true)) {
            $this->preferredLanguage = $lang;
            session()->put('ai_chat_preferred_language', $lang);
        }
    }

    public function shouldRender(): bool
    {
        // Check if chatbot is enabled
        if (! filter_var(Setting::get('ai_chatbot_enabled', config('ai.chatbot_enabled', false)), FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        // Check if API key is configured
        $apiKey = Setting::get('ai_llm_api_key', '');
        if ($apiKey === '' || $apiKey === null) {
            // Also check env fallback
            if (config('ai.llm_api_key', '') === '') {
                return false;
            }
        }

        // Check display location
        $displayLocation = Setting::get('ai_chatbot_display_location', 'all_pages');

        if ($displayLocation === 'all_pages') {
            return true;
        }

        if ($displayLocation === 'homepage_only') {
            return Route::currentRouteName() === 'home';
        }

        if ($displayLocation === 'specific_pages') {
            $allowedRoutes = Setting::get('ai_chatbot_display_pages', '');
            $routeNames = array_map('trim', explode(',', $allowedRoutes));

            return in_array(Route::currentRouteName(), $routeNames, true);
        }

        return true;
    }

    public function render(): View
    {
        return view('livewire.ai-chat', [
            'botName' => $this->getBotName(),
            'botAvatar' => $this->getBotAvatar(),
            'welcomeMessage' => $this->getWelcomeMessage(),
            'placeholder' => $this->getPlaceholder(),
            'disclaimer' => $this->getDisclaimer(),
            'languagePreference' => $this->getLanguagePreference(),
            'showLanguageToggle' => in_array($this->getLanguagePreference(), ['user_choice', 'ms_en_only'], true),
        ]);
    }

    private function getResolvedLocale(): string
    {
        $preference = $this->getLanguagePreference();

        return match ($preference) {
            'always_ms' => 'ms',
            'always_en' => 'en',
            'user_choice', 'ms_en_only' => $this->preferredLanguage !== '' ? $this->preferredLanguage : app()->getLocale(),
            default => app()->getLocale(), // same_as_page
        };
    }

    private function buildSystemPrompt(string $ragContext, string $locale): string
    {
        $parts = [];

        // Persona
        $persona = Setting::get("ai_chatbot_persona_{$locale}", '');
        if ($persona === '' || $persona === null) {
            $persona = __('ai.default_persona');
        }
        $parts[] = $persona;

        // Language instruction
        if ($locale === 'ms') {
            $parts[] = __('ai.language_instruction');
        } else {
            $parts[] = __('ai.language_instruction_en');
        }

        // Restrictions
        $restrictions = Setting::get("ai_chatbot_restrictions_{$locale}", '');
        if ($restrictions !== '' && $restrictions !== null) {
            $parts[] = $restrictions;
        }

        // RAG instruction + context
        if ($ragContext !== '') {
            $parts[] = __('ai.rag_instruction');
            $parts[] = __('ai.rag_context_header');
            $parts[] = $ragContext;
        }

        return implode("\n\n", $parts);
    }

    private function getBotName(): string
    {
        $locale = app()->getLocale();
        $name = Setting::get("ai_chatbot_name_{$locale}", '');

        return ($name !== '' && $name !== null) ? $name : __('ai.default_name');
    }

    private function getBotAvatar(): string
    {
        $avatar = Setting::get('ai_chatbot_avatar', '');

        if ($avatar !== '' && $avatar !== null) {
            return $avatar;
        }

        // Fall back to site logo
        $logo = Setting::get('site_logo', '');

        return ($logo !== '' && $logo !== null) ? $logo : '';
    }

    private function getWelcomeMessage(): string
    {
        $locale = app()->getLocale();
        $message = Setting::get("ai_chatbot_welcome_{$locale}", '');

        return ($message !== '' && $message !== null) ? $message : __('ai.default_welcome');
    }

    private function getPlaceholder(): string
    {
        $locale = app()->getLocale();
        $placeholder = Setting::get("ai_chatbot_placeholder_{$locale}", '');

        return ($placeholder !== '' && $placeholder !== null) ? $placeholder : __('ai.default_placeholder');
    }

    private function getDisclaimer(): string
    {
        $locale = app()->getLocale();
        $disclaimer = Setting::get("ai_chatbot_disclaimer_{$locale}", '');

        return ($disclaimer !== '' && $disclaimer !== null) ? $disclaimer : __('ai.default_disclaimer');
    }

    private function getLanguagePreference(): string
    {
        return Setting::get('ai_chatbot_language_preference', 'same_as_page') ?? 'same_as_page';
    }
}
