<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasConfigurableNavigation;
use App\Models\Setting;
use App\Services\AiProviderValidator;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

/**
 * @property-read Schema $form
 */
class ManageAiSettings extends Page
{
    use HasConfigurableNavigation;

    protected static string $sidebarKey = 'manage-ai-settings';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?int $navigationSort = 5;

    protected static \UnitEnum|string|null $navigationGroup = null;

    protected static ?string $title = null;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('ai.settings_title');
    }

    public function getTitle(): string
    {
        return static::getNavigationLabel();
    }

    protected string $view = 'filament.pages.manage-ai-settings';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    /**
     * Settings keys that are stored encrypted.
     *
     * @var array<int, string>
     */
    private const ENCRYPTED_KEYS = [
        'ai_llm_api_key',
        'ai_embedding_api_key',
    ];

    public static function canAccess(): bool
    {
        return Auth::user()?->can('manage_ai_settings') ?? false;
    }

    public function mount(): void
    {
        $llmProvider = Setting::get('ai_llm_provider', config('ai.llm_provider', 'anthropic'));
        $llmModel = Setting::get('ai_llm_model', config('ai.llm_model', 'claude-sonnet-4-6'));
        $embeddingProvider = Setting::get('ai_embedding_provider', config('ai.embedding_provider', 'openai'));
        $embeddingModel = Setting::get('ai_embedding_model', config('ai.embedding_model', 'text-embedding-3-small'));

        // Reverse-map stored model to Select/custom fields
        $knownLlmModels = AiProviderValidator::llmModels($llmProvider);
        $llmModelSelect = array_key_exists($llmModel, $knownLlmModels) ? $llmModel : '__other__';
        $llmModelCustom = $llmModelSelect === '__other__' ? $llmModel : '';

        $knownEmbeddingModels = AiProviderValidator::embeddingModels($embeddingProvider);
        $embeddingModelSelect = array_key_exists($embeddingModel, $knownEmbeddingModels) ? $embeddingModel : '__other__';
        $embeddingModelCustom = $embeddingModelSelect === '__other__' ? $embeddingModel : '';

        $this->form->fill([
            // Provider config
            'ai_llm_provider' => $llmProvider,
            'ai_llm_model_select' => $llmModelSelect,
            'ai_llm_model_custom' => $llmModelCustom,
            'ai_llm_api_key' => $this->decryptSetting('ai_llm_api_key'),
            'ai_llm_base_url' => Setting::get('ai_llm_base_url', config('ai.llm_base_url', '')),

            // Embedding config
            'ai_embedding_provider' => $embeddingProvider,
            'ai_embedding_model_select' => $embeddingModelSelect,
            'ai_embedding_model_custom' => $embeddingModelCustom,
            'ai_embedding_api_key' => $this->decryptSetting('ai_embedding_api_key'),
            'ai_embedding_dimension' => (int) Setting::get('ai_embedding_dimension', config('ai.embedding_dimension', 1536)),

            // Feature flags
            'ai_chatbot_enabled' => (bool) Setting::get('ai_chatbot_enabled', config('ai.chatbot_enabled', false)),
            'ai_admin_editor_enabled' => (bool) Setting::get('ai_admin_editor_enabled', config('ai.admin_editor_enabled', false)),
            'ai_chatbot_rate_limit' => (int) Setting::get('ai_chatbot_rate_limit', config('ai.chatbot_rate_limit', 10)),

            // Chatbot settings
            'ai_chatbot_name_ms' => Setting::get('ai_chatbot_name_ms', ''),
            'ai_chatbot_name_en' => Setting::get('ai_chatbot_name_en', ''),
            'ai_chatbot_avatar' => Setting::get('ai_chatbot_avatar', ''),
            'ai_chatbot_persona_ms' => Setting::get('ai_chatbot_persona_ms', ''),
            'ai_chatbot_persona_en' => Setting::get('ai_chatbot_persona_en', ''),
            'ai_chatbot_language_preference' => Setting::get('ai_chatbot_language_preference', 'same_as_page'),
            'ai_chatbot_restrictions_ms' => Setting::get('ai_chatbot_restrictions_ms', ''),
            'ai_chatbot_restrictions_en' => Setting::get('ai_chatbot_restrictions_en', ''),
            'ai_chatbot_display_location' => Setting::get('ai_chatbot_display_location', 'all_pages'),
            'ai_chatbot_display_pages' => Setting::get('ai_chatbot_display_pages', ''),
            'ai_chatbot_welcome_ms' => Setting::get('ai_chatbot_welcome_ms', ''),
            'ai_chatbot_welcome_en' => Setting::get('ai_chatbot_welcome_en', ''),
            'ai_chatbot_placeholder_ms' => Setting::get('ai_chatbot_placeholder_ms', ''),
            'ai_chatbot_placeholder_en' => Setting::get('ai_chatbot_placeholder_en', ''),
            'ai_chatbot_disclaimer_ms' => Setting::get('ai_chatbot_disclaimer_ms', ''),
            'ai_chatbot_disclaimer_en' => Setting::get('ai_chatbot_disclaimer_en', ''),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    // Section 1: Provider Configuration
                    Section::make(__('ai.provider_config'))
                        ->description(__('ai.provider_config_desc'))
                        ->schema([
                            Select::make('ai_llm_provider')
                                ->label(__('ai.llm_provider'))
                                ->options($this->getLlmProviderOptions())
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (callable $set): void {
                                    $set('ai_llm_model_select', null);
                                    $set('ai_llm_model_custom', '');
                                }),
                            Select::make('ai_llm_model_select')
                                ->label(__('ai.llm_model'))
                                ->options(function (Get $get): array {
                                    $provider = $get('ai_llm_provider');
                                    if (! $provider) {
                                        return [];
                                    }
                                    $models = AiProviderValidator::llmModels($provider);
                                    $models['__other__'] = __('ai.model_other');

                                    return $models;
                                })
                                ->required()
                                ->live()
                                ->visible(fn (Get $get): bool => filled($get('ai_llm_provider')))
                                ->searchable(),
                            TextInput::make('ai_llm_model_custom')
                                ->label(__('ai.llm_model_custom'))
                                ->helperText(__('ai.llm_model_custom_help'))
                                ->required(fn (Get $get): bool => $get('ai_llm_model_select') === '__other__')
                                ->visible(fn (Get $get): bool => $get('ai_llm_model_select') === '__other__')
                                ->maxLength(255),
                            TextInput::make('ai_llm_api_key')
                                ->label(__('ai.llm_api_key'))
                                ->password()
                                ->revealable()
                                ->maxLength(255),
                            TextInput::make('ai_llm_base_url')
                                ->label(__('ai.llm_base_url'))
                                ->url()
                                ->helperText(__('ai.llm_base_url_help'))
                                ->maxLength(2048)
                                ->visible(fn (Get $get): bool => $get('ai_llm_provider') === 'openai-compatible'),
                        ])
                        ->columns(2),

                    // Section 2: Embedding Configuration
                    Section::make(__('ai.embedding_config'))
                        ->description(__('ai.embedding_config_desc'))
                        ->schema([
                            Select::make('ai_embedding_provider')
                                ->label(__('ai.embedding_provider'))
                                ->options($this->getEmbeddingProviderOptions())
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (callable $set): void {
                                    $set('ai_embedding_model_select', null);
                                    $set('ai_embedding_model_custom', '');
                                }),
                            Select::make('ai_embedding_model_select')
                                ->label(__('ai.embedding_model'))
                                ->options(function (Get $get): array {
                                    $provider = $get('ai_embedding_provider');
                                    if (! $provider) {
                                        return [];
                                    }
                                    $models = AiProviderValidator::embeddingModels($provider);
                                    $models['__other__'] = __('ai.model_other');

                                    return $models;
                                })
                                ->required()
                                ->live()
                                ->visible(fn (Get $get): bool => filled($get('ai_embedding_provider')))
                                ->searchable(),
                            TextInput::make('ai_embedding_model_custom')
                                ->label(__('ai.embedding_model_custom'))
                                ->helperText(__('ai.embedding_model_custom_help'))
                                ->required(fn (Get $get): bool => $get('ai_embedding_model_select') === '__other__')
                                ->visible(fn (Get $get): bool => $get('ai_embedding_model_select') === '__other__')
                                ->maxLength(255),
                            TextInput::make('ai_embedding_api_key')
                                ->label(__('ai.embedding_api_key'))
                                ->password()
                                ->revealable()
                                ->helperText(__('ai.embedding_api_key_help'))
                                ->maxLength(255),
                            TextInput::make('ai_embedding_dimension')
                                ->label(__('ai.embedding_dimension'))
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->maxValue(8192)
                                ->helperText(__('ai.embedding_dimension_help')),
                        ])
                        ->columns(2),

                    // Section 3: Feature Flags
                    Section::make(__('ai.feature_flags'))
                        ->description(__('ai.feature_flags_desc'))
                        ->schema([
                            Toggle::make('ai_chatbot_enabled')
                                ->label(__('ai.chatbot_enabled')),
                            Toggle::make('ai_admin_editor_enabled')
                                ->label(__('ai.admin_editor_enabled')),
                            TextInput::make('ai_chatbot_rate_limit')
                                ->label(__('ai.chatbot_rate_limit'))
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->maxValue(1000)
                                ->helperText(__('ai.chatbot_rate_limit_help')),
                        ])
                        ->columns(3),

                    // Section 4: Chatbot Settings
                    Section::make(__('ai.chatbot_settings'))
                        ->description(__('ai.chatbot_settings_desc'))
                        ->schema([
                            Tabs::make('chatbot_name_tabs')
                                ->tabs([
                                    Tab::make(__('ai.tab_ms'))
                                        ->schema([
                                            TextInput::make('ai_chatbot_name_ms')
                                                ->label(__('ai.chatbot_name'))
                                                ->maxLength(100),
                                        ]),
                                    Tab::make(__('ai.tab_en'))
                                        ->schema([
                                            TextInput::make('ai_chatbot_name_en')
                                                ->label(__('ai.chatbot_name'))
                                                ->maxLength(100),
                                        ]),
                                ]),

                            FileUpload::make('ai_chatbot_avatar')
                                ->label(__('ai.chatbot_avatar'))
                                ->image()
                                ->directory('chatbot')
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/svg+xml', 'image/webp'])
                                ->maxSize(1024)
                                ->helperText(__('ai.chatbot_avatar_help')),

                            Tabs::make('chatbot_persona_tabs')
                                ->tabs([
                                    Tab::make(__('ai.tab_ms'))
                                        ->schema([
                                            Textarea::make('ai_chatbot_persona_ms')
                                                ->label(__('ai.chatbot_persona'))
                                                ->helperText(__('ai.chatbot_persona_help'))
                                                ->rows(4),
                                        ]),
                                    Tab::make(__('ai.tab_en'))
                                        ->schema([
                                            Textarea::make('ai_chatbot_persona_en')
                                                ->label(__('ai.chatbot_persona'))
                                                ->helperText(__('ai.chatbot_persona_help'))
                                                ->rows(4),
                                        ]),
                                ]),

                            Select::make('ai_chatbot_language_preference')
                                ->label(__('ai.chatbot_language_preference'))
                                ->options([
                                    'same_as_page' => __('ai.lang_same_as_page'),
                                    'always_ms' => __('ai.lang_always_ms'),
                                    'always_en' => __('ai.lang_always_en'),
                                    'user_choice' => __('ai.lang_user_choice'),
                                    'ms_en_only' => __('ai.lang_ms_en_only'),
                                ])
                                ->required(),

                            Tabs::make('chatbot_restrictions_tabs')
                                ->tabs([
                                    Tab::make(__('ai.tab_ms'))
                                        ->schema([
                                            Textarea::make('ai_chatbot_restrictions_ms')
                                                ->label(__('ai.chatbot_restrictions'))
                                                ->helperText(__('ai.chatbot_restrictions_help'))
                                                ->rows(3),
                                        ]),
                                    Tab::make(__('ai.tab_en'))
                                        ->schema([
                                            Textarea::make('ai_chatbot_restrictions_en')
                                                ->label(__('ai.chatbot_restrictions'))
                                                ->helperText(__('ai.chatbot_restrictions_help'))
                                                ->rows(3),
                                        ]),
                                ]),

                            Select::make('ai_chatbot_display_location')
                                ->label(__('ai.chatbot_display_location'))
                                ->options([
                                    'all_pages' => __('ai.display_all_pages'),
                                    'homepage_only' => __('ai.display_homepage_only'),
                                    'specific_pages' => __('ai.display_specific_pages'),
                                ])
                                ->required()
                                ->live(),

                            TextInput::make('ai_chatbot_display_pages')
                                ->label(__('ai.chatbot_display_pages'))
                                ->helperText(__('ai.chatbot_display_pages_help'))
                                ->maxLength(1000)
                                ->visible(fn (Get $get): bool => $get('ai_chatbot_display_location') === 'specific_pages'),

                            Tabs::make('chatbot_welcome_tabs')
                                ->tabs([
                                    Tab::make(__('ai.tab_ms'))
                                        ->schema([
                                            Textarea::make('ai_chatbot_welcome_ms')
                                                ->label(__('ai.chatbot_welcome'))
                                                ->rows(2),
                                        ]),
                                    Tab::make(__('ai.tab_en'))
                                        ->schema([
                                            Textarea::make('ai_chatbot_welcome_en')
                                                ->label(__('ai.chatbot_welcome'))
                                                ->rows(2),
                                        ]),
                                ]),

                            Tabs::make('chatbot_placeholder_tabs')
                                ->tabs([
                                    Tab::make(__('ai.tab_ms'))
                                        ->schema([
                                            TextInput::make('ai_chatbot_placeholder_ms')
                                                ->label(__('ai.chatbot_placeholder'))
                                                ->maxLength(255),
                                        ]),
                                    Tab::make(__('ai.tab_en'))
                                        ->schema([
                                            TextInput::make('ai_chatbot_placeholder_en')
                                                ->label(__('ai.chatbot_placeholder'))
                                                ->maxLength(255),
                                        ]),
                                ]),

                            Tabs::make('chatbot_disclaimer_tabs')
                                ->tabs([
                                    Tab::make(__('ai.tab_ms'))
                                        ->schema([
                                            Textarea::make('ai_chatbot_disclaimer_ms')
                                                ->label(__('ai.chatbot_disclaimer'))
                                                ->helperText(__('ai.chatbot_disclaimer_help'))
                                                ->rows(3),
                                        ]),
                                    Tab::make(__('ai.tab_en'))
                                        ->schema([
                                            Textarea::make('ai_chatbot_disclaimer_en')
                                                ->label(__('ai.chatbot_disclaimer'))
                                                ->helperText(__('ai.chatbot_disclaimer_help'))
                                                ->rows(3),
                                        ]),
                                ]),
                        ]),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label(__('filament.actions.save'))
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                            Action::make('reset')
                                ->label(__('filament.actions.reset'))
                                ->color('gray')
                                ->action(fn () => $this->mount()),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Resolve actual model values from Select + custom TextInput
        $llmModel = ($data['ai_llm_model_select'] ?? '') === '__other__'
            ? ($data['ai_llm_model_custom'] ?? '')
            : ($data['ai_llm_model_select'] ?? '');

        $embeddingModel = ($data['ai_embedding_model_select'] ?? '') === '__other__'
            ? ($data['ai_embedding_model_custom'] ?? '')
            : ($data['ai_embedding_model_select'] ?? '');

        // Validate API keys
        $llmApiKey = $data['ai_llm_api_key'] ?? '';
        $llmKeyValid = AiProviderValidator::validateApiKey(
            $data['ai_llm_provider'] ?? 'anthropic',
            $llmApiKey,
            $data['ai_llm_base_url'] ?? '',
        );

        $embeddingApiKey = $data['ai_embedding_api_key'] ?? '';
        $embeddingKeyValid = AiProviderValidator::validateApiKey(
            $data['ai_embedding_provider'] ?? 'openai',
            $embeddingApiKey,
        );

        // Save all plain settings unconditionally
        $plainKeys = [
            'ai_llm_provider',
            'ai_llm_base_url',
            'ai_embedding_provider',
            'ai_embedding_dimension',
            'ai_chatbot_enabled',
            'ai_admin_editor_enabled',
            'ai_chatbot_rate_limit',
            'ai_chatbot_name_ms',
            'ai_chatbot_name_en',
            'ai_chatbot_avatar',
            'ai_chatbot_persona_ms',
            'ai_chatbot_persona_en',
            'ai_chatbot_language_preference',
            'ai_chatbot_restrictions_ms',
            'ai_chatbot_restrictions_en',
            'ai_chatbot_display_location',
            'ai_chatbot_display_pages',
            'ai_chatbot_welcome_ms',
            'ai_chatbot_welcome_en',
            'ai_chatbot_placeholder_ms',
            'ai_chatbot_placeholder_en',
            'ai_chatbot_disclaimer_ms',
            'ai_chatbot_disclaimer_en',
        ];

        foreach ($plainKeys as $key) {
            Setting::set($key, $data[$key] ?? '');
        }

        // Save resolved model values
        Setting::set('ai_llm_model', $llmModel);
        Setting::set('ai_embedding_model', $embeddingModel);

        // Save keys only if valid; invalid keys are not stored
        if ($llmKeyValid) {
            $this->saveEncryptedSetting('ai_llm_api_key', $llmApiKey);
        }

        if ($embeddingKeyValid) {
            $this->saveEncryptedSetting('ai_embedding_api_key', $embeddingApiKey);
        }

        if (! $llmKeyValid || ! $embeddingKeyValid) {
            $invalidKeys = [];
            if (! $llmKeyValid) {
                $invalidKeys[] = __('ai.llm_api_key');
            }
            if (! $embeddingKeyValid) {
                $invalidKeys[] = __('ai.embedding_api_key');
            }

            Notification::make()
                ->warning()
                ->title(__('ai.saved_with_key_errors'))
                ->body(__('ai.invalid_keys_not_saved', ['keys' => implode(', ', $invalidKeys)]))
                ->persistent()
                ->send();
        } else {
            Notification::make()
                ->success()
                ->title(__('ai.saved'))
                ->send();
        }
    }

    /**
     * @return array<string, string>
     */
    private function getLlmProviderOptions(): array
    {
        return [
            'anthropic' => __('ai.provider_anthropic'),
            'openai' => __('ai.provider_openai'),
            'google' => __('ai.provider_google'),
            'groq' => __('ai.provider_groq'),
            'mistral' => __('ai.provider_mistral'),
            'xai' => __('ai.provider_xai'),
            'ollama' => __('ai.provider_ollama'),
            'deepseek' => __('ai.provider_deepseek'),
            'openai-compatible' => __('ai.provider_openai_compatible'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getEmbeddingProviderOptions(): array
    {
        return [
            'openai' => __('ai.provider_openai'),
            'google' => __('ai.provider_google'),
            'cohere' => __('ai.embed_provider_cohere'),
            'voyageai' => __('ai.embed_provider_voyageai'),
            'ollama' => __('ai.provider_ollama'),
        ];
    }

    /**
     * Decrypt an encrypted setting value, returning empty string on failure.
     */
    private function decryptSetting(string $key): string
    {
        $raw = Setting::get($key, '');

        if ($raw === '' || $raw === null) {
            return '';
        }

        try {
            return Crypt::decrypt($raw);
        } catch (DecryptException) {
            return '';
        }
    }

    /**
     * Encrypt and save a setting value. Stores empty string as-is.
     */
    private function saveEncryptedSetting(string $key, string $value): void
    {
        if ($value === '' || $value === null) {
            Setting::set($key, '', 'encrypted');

            return;
        }

        Setting::set($key, Crypt::encrypt($value), 'encrypted');
    }
}
