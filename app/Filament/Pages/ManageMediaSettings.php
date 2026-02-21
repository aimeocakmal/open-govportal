<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

/**
 * @property-read Schema $form
 */
class ManageMediaSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCloud;

    protected static ?int $navigationSort = 3;

    protected static \UnitEnum|string|null $navigationGroup = null;

    protected static ?string $title = null;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.settings');
    }

    public static function getLabel(): string
    {
        return __('filament.settings.media.title');
    }

    protected string $view = 'filament.pages.manage-media-settings';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    /**
     * Settings keys that are stored encrypted.
     *
     * @var array<int, string>
     */
    private const ENCRYPTED_KEYS = [
        'media_s3_key',
        'media_s3_secret',
        'media_r2_access_key',
        'media_r2_secret_key',
        'media_gcs_key_json',
        'media_azure_key',
    ];

    public static function canAccess(): bool
    {
        return Auth::user()?->can('manage_settings') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'media_disk' => Setting::get('media_disk', 'local'),

            // S3
            'media_s3_key' => $this->decryptSetting('media_s3_key'),
            'media_s3_secret' => $this->decryptSetting('media_s3_secret'),
            'media_s3_region' => Setting::get('media_s3_region', 'ap-southeast-1'),
            'media_s3_bucket' => Setting::get('media_s3_bucket', ''),
            'media_s3_url' => Setting::get('media_s3_url', ''),
            'media_s3_endpoint' => Setting::get('media_s3_endpoint', ''),

            // R2
            'media_r2_account_id' => Setting::get('media_r2_account_id', ''),
            'media_r2_access_key' => $this->decryptSetting('media_r2_access_key'),
            'media_r2_secret_key' => $this->decryptSetting('media_r2_secret_key'),
            'media_r2_bucket' => Setting::get('media_r2_bucket', ''),
            'media_r2_public_url' => Setting::get('media_r2_public_url', ''),

            // GCS
            'media_gcs_project_id' => Setting::get('media_gcs_project_id', ''),
            'media_gcs_bucket' => Setting::get('media_gcs_bucket', ''),
            'media_gcs_key_json' => $this->decryptSetting('media_gcs_key_json'),

            // Azure
            'media_azure_account' => Setting::get('media_azure_account', ''),
            'media_azure_key' => $this->decryptSetting('media_azure_key'),
            'media_azure_container' => Setting::get('media_azure_container', ''),
            'media_azure_url' => Setting::get('media_azure_url', ''),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make(__('filament.settings.media.storage_driver'))
                        ->description(__('filament.settings.media.storage_driver_desc'))
                        ->schema([
                            Select::make('media_disk')
                                ->label(__('filament.settings.media.media_disk'))
                                ->options([
                                    'local' => __('filament.settings.media.local'),
                                    's3' => __('filament.settings.media.amazon_s3'),
                                    'r2' => __('filament.settings.media.cloudflare_r2'),
                                    'gcs' => __('filament.settings.media.google_cloud'),
                                    'azure' => __('filament.settings.media.azure_blob'),
                                ])
                                ->required()
                                ->live(),
                        ]),

                    Section::make(__('filament.settings.media.amazon_s3'))
                        ->description(__('filament.settings.media.s3_desc'))
                        ->schema([
                            TextInput::make('media_s3_key')
                                ->label(__('filament.settings.media.access_key_id'))
                                ->password()
                                ->revealable()
                                ->maxLength(255),
                            TextInput::make('media_s3_secret')
                                ->label(__('filament.settings.media.secret_access_key'))
                                ->password()
                                ->revealable()
                                ->maxLength(255),
                            TextInput::make('media_s3_region')
                                ->label(__('filament.settings.media.region'))
                                ->placeholder('ap-southeast-1')
                                ->maxLength(50),
                            TextInput::make('media_s3_bucket')
                                ->label(__('filament.settings.media.bucket'))
                                ->maxLength(255),
                            TextInput::make('media_s3_url')
                                ->label(__('filament.settings.media.cdn_url'))
                                ->url()
                                ->placeholder('https://cdn.example.com')
                                ->helperText(__('filament.settings.media.cdn_url_help'))
                                ->maxLength(2048),
                            TextInput::make('media_s3_endpoint')
                                ->label(__('filament.settings.media.custom_endpoint'))
                                ->url()
                                ->placeholder('https://s3.example.com')
                                ->helperText(__('filament.settings.media.custom_endpoint_help'))
                                ->maxLength(2048),
                        ])
                        ->columns(2)
                        ->visible(fn (Get $get): bool => $get('media_disk') === 's3'),

                    Section::make(__('filament.settings.media.cloudflare_r2'))
                        ->description(__('filament.settings.media.r2_desc'))
                        ->schema([
                            TextInput::make('media_r2_account_id')
                                ->label(__('filament.settings.media.r2_account_id'))
                                ->maxLength(255),
                            TextInput::make('media_r2_access_key')
                                ->label(__('filament.settings.media.r2_access_key'))
                                ->password()
                                ->revealable()
                                ->maxLength(255),
                            TextInput::make('media_r2_secret_key')
                                ->label(__('filament.settings.media.r2_secret_key'))
                                ->password()
                                ->revealable()
                                ->maxLength(255),
                            TextInput::make('media_r2_bucket')
                                ->label(__('filament.settings.media.bucket'))
                                ->maxLength(255),
                            TextInput::make('media_r2_public_url')
                                ->label(__('filament.settings.media.r2_public_url'))
                                ->url()
                                ->placeholder('https://media.example.com')
                                ->helperText(__('filament.settings.media.r2_public_url_help'))
                                ->maxLength(2048),
                        ])
                        ->columns(2)
                        ->visible(fn (Get $get): bool => $get('media_disk') === 'r2'),

                    Section::make(__('filament.settings.media.google_cloud'))
                        ->description(__('filament.settings.media.gcs_desc'))
                        ->schema([
                            TextInput::make('media_gcs_project_id')
                                ->label(__('filament.settings.media.gcs_project_id'))
                                ->maxLength(255),
                            TextInput::make('media_gcs_bucket')
                                ->label(__('filament.settings.media.bucket'))
                                ->maxLength(255),
                            Textarea::make('media_gcs_key_json')
                                ->label(__('filament.settings.media.gcs_key_json'))
                                ->rows(6)
                                ->helperText(__('filament.settings.media.gcs_key_json_help')),
                        ])
                        ->visible(fn (Get $get): bool => $get('media_disk') === 'gcs'),

                    Section::make(__('filament.settings.media.azure_blob'))
                        ->description(__('filament.settings.media.azure_desc'))
                        ->schema([
                            TextInput::make('media_azure_account')
                                ->label(__('filament.settings.media.azure_account'))
                                ->maxLength(255),
                            TextInput::make('media_azure_key')
                                ->label(__('filament.settings.media.azure_key'))
                                ->password()
                                ->revealable()
                                ->maxLength(255),
                            TextInput::make('media_azure_container')
                                ->label(__('filament.settings.media.azure_container'))
                                ->maxLength(255),
                            TextInput::make('media_azure_url')
                                ->label(__('filament.settings.media.azure_url'))
                                ->url()
                                ->placeholder('https://media.example.com')
                                ->helperText(__('filament.settings.media.azure_url_help'))
                                ->maxLength(2048),
                        ])
                        ->columns(2)
                        ->visible(fn (Get $get): bool => $get('media_disk') === 'azure'),
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

        $plainKeys = [
            'media_disk',
            'media_s3_region',
            'media_s3_bucket',
            'media_s3_url',
            'media_s3_endpoint',
            'media_r2_account_id',
            'media_r2_bucket',
            'media_r2_public_url',
            'media_gcs_project_id',
            'media_gcs_bucket',
            'media_azure_account',
            'media_azure_container',
            'media_azure_url',
        ];

        foreach ($plainKeys as $key) {
            Setting::set($key, $data[$key] ?? '');
        }

        foreach (self::ENCRYPTED_KEYS as $key) {
            $this->saveEncryptedSetting($key, $data[$key] ?? '');
        }

        Notification::make()
            ->success()
            ->title(__('filament.settings.media.saved'))
            ->send();
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
