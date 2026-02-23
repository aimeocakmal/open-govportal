<?php

namespace Tests\Feature;

use App\Livewire\AiChat;
use App\Models\Setting;
use App\Services\AiService;
use App\Services\RagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class AiChatTest extends TestCase
{
    use RefreshDatabase;

    private function enableChatbot(): void
    {
        Setting::set('ai_chatbot_enabled', 'true');
        Setting::set('ai_llm_api_key', Crypt::encrypt('sk-test-key'), 'encrypted');
        Setting::set('ai_chatbot_display_location', 'all_pages');
    }

    public function test_widget_hidden_when_chatbot_disabled(): void
    {
        Setting::set('ai_chatbot_enabled', 'false');

        $component = Livewire::test(AiChat::class);
        $this->assertFalse($component->instance()->shouldRender());
    }

    public function test_widget_hidden_when_no_api_key(): void
    {
        Setting::set('ai_chatbot_enabled', 'true');
        // No API key set

        $component = Livewire::test(AiChat::class);
        $this->assertFalse($component->instance()->shouldRender());
    }

    public function test_widget_visible_when_enabled_with_api_key(): void
    {
        $this->enableChatbot();

        $component = Livewire::test(AiChat::class);
        $this->assertTrue($component->instance()->shouldRender());
    }

    public function test_widget_renders_bot_name(): void
    {
        $this->enableChatbot();
        Setting::set('ai_chatbot_name_ms', 'Pembantu Ujian');

        Livewire::test(AiChat::class)
            ->assertSee('Pembantu Ujian');
    }

    public function test_widget_renders_default_bot_name_when_not_configured(): void
    {
        $this->enableChatbot();

        Livewire::test(AiChat::class)
            ->assertSee(__('ai.default_name'));
    }

    public function test_welcome_message_displayed(): void
    {
        $this->enableChatbot();
        Setting::set('ai_chatbot_welcome_ms', 'Selamat datang ujian!');

        Livewire::test(AiChat::class)
            ->call('acceptDisclaimer')
            ->assertSee('Selamat datang ujian!');
    }

    public function test_welcome_message_uses_default_when_not_configured(): void
    {
        $this->enableChatbot();

        Livewire::test(AiChat::class)
            ->call('acceptDisclaimer')
            ->assertSee(__('ai.default_welcome'));
    }

    public function test_disclaimer_shown_before_acceptance(): void
    {
        $this->enableChatbot();

        Livewire::test(AiChat::class)
            ->assertSee(__('ai.default_disclaimer'))
            ->assertSee(__('ai.disclaimer_accept'));
    }

    public function test_disclaimer_acceptance_stored_in_session(): void
    {
        $this->enableChatbot();

        Livewire::test(AiChat::class)
            ->call('acceptDisclaimer')
            ->assertSet('disclaimerAccepted', true);
    }

    public function test_send_message_happy_path(): void
    {
        $this->enableChatbot();

        $this->mock(AiService::class, function ($mock) {
            $mock->shouldReceive('chat')
                ->once()
                ->andReturn('Ini adalah jawapan AI.');
        });

        $this->mock(RagService::class, function ($mock) {
            $mock->shouldReceive('retrieveChunks')
                ->once()
                ->andReturn([['content' => 'test chunk', 'metadata' => []]]);
            $mock->shouldReceive('buildContext')
                ->once()
                ->andReturn('test context');
        });

        Livewire::test(AiChat::class)
            ->call('acceptDisclaimer')
            ->set('input', 'Apakah perkhidmatan kementerian?')
            ->call('send')
            ->assertSet('isThinking', false)
            ->assertSet('hasError', false)
            ->assertSet('input', '');

        // Verify messages contain both user and assistant
        $component = Livewire::test(AiChat::class);
        $messages = $component->get('messages');
        $this->assertCount(2, $messages);
        $this->assertEquals('user', $messages[0]['role']);
        $this->assertEquals('Apakah perkhidmatan kementerian?', $messages[0]['content']);
        $this->assertEquals('assistant', $messages[1]['role']);
        $this->assertEquals('Ini adalah jawapan AI.', $messages[1]['content']);
    }

    public function test_empty_input_rejected(): void
    {
        $this->enableChatbot();

        Livewire::test(AiChat::class)
            ->call('acceptDisclaimer')
            ->set('input', '')
            ->call('send')
            ->assertHasErrors(['input']);
    }

    public function test_short_input_rejected(): void
    {
        $this->enableChatbot();

        Livewire::test(AiChat::class)
            ->call('acceptDisclaimer')
            ->set('input', 'a')
            ->call('send')
            ->assertHasErrors(['input']);
    }

    public function test_rate_limiting_enforced(): void
    {
        $this->enableChatbot();
        Setting::set('ai_chatbot_rate_limit', '3');

        $this->mock(AiService::class, function ($mock) {
            $mock->shouldReceive('chat')->andReturn('response');
        });

        $this->mock(RagService::class, function ($mock) {
            $mock->shouldReceive('retrieveChunks')->andReturn([]);
            $mock->shouldReceive('buildContext')->andReturn('');
        });

        $ip = '127.0.0.1';
        $key = "ai-chat:{$ip}";

        // Simulate hitting rate limit
        for ($i = 0; $i < 3; $i++) {
            RateLimiter::hit($key, 3600);
        }

        Livewire::test(AiChat::class)
            ->call('acceptDisclaimer')
            ->set('input', 'Test message here')
            ->call('send')
            ->assertSet('rateLimited', true);
    }

    public function test_error_state_on_empty_llm_response(): void
    {
        $this->enableChatbot();

        $this->mock(AiService::class, function ($mock) {
            $mock->shouldReceive('chat')
                ->once()
                ->andReturn('');
        });

        $this->mock(RagService::class, function ($mock) {
            $mock->shouldReceive('retrieveChunks')->andReturn([]);
            $mock->shouldReceive('buildContext')->andReturn('');
        });

        Livewire::test(AiChat::class)
            ->call('acceptDisclaimer')
            ->set('input', 'Test question here')
            ->call('send')
            ->assertSet('hasError', true)
            ->assertSet('isThinking', false);
    }

    public function test_clear_chat_resets_messages(): void
    {
        $this->enableChatbot();

        $this->mock(AiService::class, function ($mock) {
            $mock->shouldReceive('chat')->andReturn('response text');
        });

        $this->mock(RagService::class, function ($mock) {
            $mock->shouldReceive('retrieveChunks')->andReturn([]);
            $mock->shouldReceive('buildContext')->andReturn('');
        });

        Livewire::test(AiChat::class)
            ->call('acceptDisclaimer')
            ->set('input', 'Hello there test')
            ->call('send')
            ->call('clearChat')
            ->assertSet('messages', [])
            ->assertSet('hasError', false)
            ->assertSet('rateLimited', false);
    }

    public function test_display_location_homepage_only(): void
    {
        $this->enableChatbot();
        Setting::set('ai_chatbot_display_location', 'homepage_only');

        // When not on homepage, shouldRender returns false
        $component = Livewire::test(AiChat::class);
        // Route::currentRouteName() won't be 'home' in test context
        $this->assertFalse($component->instance()->shouldRender());
    }

    public function test_display_location_specific_pages(): void
    {
        $this->enableChatbot();
        Setting::set('ai_chatbot_display_location', 'specific_pages');
        Setting::set('ai_chatbot_display_pages', 'home, siaran.index');

        // Not on any of the specific pages
        $component = Livewire::test(AiChat::class);
        $this->assertFalse($component->instance()->shouldRender());
    }

    public function test_language_toggle_sets_preferred_language(): void
    {
        $this->enableChatbot();

        Livewire::test(AiChat::class)
            ->call('setLanguage', 'en')
            ->assertSet('preferredLanguage', 'en');
    }

    public function test_language_toggle_rejects_invalid_language(): void
    {
        $this->enableChatbot();

        Livewire::test(AiChat::class)
            ->call('setLanguage', 'fr')
            ->assertSet('preferredLanguage', '');
    }

    public function test_language_toggle_shown_for_user_choice(): void
    {
        $this->enableChatbot();
        Setting::set('ai_chatbot_language_preference', 'user_choice');

        Livewire::test(AiChat::class)
            ->call('acceptDisclaimer')
            ->assertSee(__('ai.language_toggle_ms'))
            ->assertSee(__('ai.language_toggle_en'));
    }

    public function test_language_toggle_hidden_for_same_as_page(): void
    {
        $this->enableChatbot();
        Setting::set('ai_chatbot_language_preference', 'same_as_page');

        Livewire::test(AiChat::class)
            ->call('acceptDisclaimer')
            ->assertDontSee(__('ai.language_toggle_ms'));
    }

    public function test_custom_disclaimer_from_settings(): void
    {
        $this->enableChatbot();
        Setting::set('ai_chatbot_disclaimer_ms', 'Penafian khas: jangan kongsi maklumat sulit.');

        Livewire::test(AiChat::class)
            ->assertSee('Penafian khas: jangan kongsi maklumat sulit.');
    }

    public function test_custom_placeholder_from_settings(): void
    {
        $this->enableChatbot();
        Setting::set('ai_chatbot_placeholder_ms', 'Tanya saya apa sahaja...');

        Livewire::test(AiChat::class)
            ->call('acceptDisclaimer')
            ->assertSee('Tanya saya apa sahaja...');
    }

    public function test_messages_persisted_in_session(): void
    {
        $this->enableChatbot();

        $this->mock(AiService::class, function ($mock) {
            $mock->shouldReceive('chat')->andReturn('Jawapan ujian');
        });

        $this->mock(RagService::class, function ($mock) {
            $mock->shouldReceive('retrieveChunks')->andReturn([]);
            $mock->shouldReceive('buildContext')->andReturn('');
        });

        Livewire::test(AiChat::class)
            ->call('acceptDisclaimer')
            ->set('input', 'Soalan ujian dua')
            ->call('send');

        // Verify session has the messages
        $sessionMessages = session('ai_chat_messages');
        $this->assertNotNull($sessionMessages);
        $this->assertCount(2, $sessionMessages);
    }

    public function test_config_fallback_for_api_key(): void
    {
        Setting::set('ai_chatbot_enabled', 'true');
        Setting::set('ai_chatbot_display_location', 'all_pages');
        // No api key in settings, but set in config
        config(['ai.llm_api_key' => 'sk-config-key']);

        $component = Livewire::test(AiChat::class);
        $this->assertTrue($component->instance()->shouldRender());
    }
}
