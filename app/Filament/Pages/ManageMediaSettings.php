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

    protected static \UnitEnum|string|null $navigationGroup = 'Settings';

    protected static ?string $title = 'Media Settings';

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
                    Section::make('Storage Driver')
                        ->description('Select where uploaded media files will be stored.')
                        ->schema([
                            Select::make('media_disk')
                                ->label('Media Disk')
                                ->options([
                                    'local' => 'Local Filesystem',
                                    's3' => 'Amazon S3',
                                    'r2' => 'Cloudflare R2',
                                    'gcs' => 'Google Cloud Storage',
                                    'azure' => 'Azure Blob Storage',
                                ])
                                ->required()
                                ->live(),
                        ]),

                    Section::make('Amazon S3')
                        ->description('Credentials for AWS S3 storage.')
                        ->schema([
                            TextInput::make('media_s3_key')
                                ->label('Access Key ID')
                                ->password()
                                ->revealable()
                                ->maxLength(255),
                            TextInput::make('media_s3_secret')
                                ->label('Secret Access Key')
                                ->password()
                                ->revealable()
                                ->maxLength(255),
                            TextInput::make('media_s3_region')
                                ->label('Region')
                                ->placeholder('ap-southeast-1')
                                ->maxLength(50),
                            TextInput::make('media_s3_bucket')
                                ->label('Bucket')
                                ->maxLength(255),
                            TextInput::make('media_s3_url')
                                ->label('CDN / Public URL')
                                ->url()
                                ->placeholder('https://cdn.example.com')
                                ->helperText('Optional. Leave blank for auto-generated AWS URL.')
                                ->maxLength(2048),
                            TextInput::make('media_s3_endpoint')
                                ->label('Custom Endpoint')
                                ->url()
                                ->placeholder('https://s3.example.com')
                                ->helperText('Leave blank for standard AWS S3. Set for S3-compatible services.')
                                ->maxLength(2048),
                        ])
                        ->columns(2)
                        ->visible(fn (Get $get): bool => $get('media_disk') === 's3'),

                    Section::make('Cloudflare R2')
                        ->description('Credentials for Cloudflare R2 storage (S3-compatible).')
                        ->schema([
                            TextInput::make('media_r2_account_id')
                                ->label('Cloudflare Account ID')
                                ->maxLength(255),
                            TextInput::make('media_r2_access_key')
                                ->label('Access Key')
                                ->password()
                                ->revealable()
                                ->maxLength(255),
                            TextInput::make('media_r2_secret_key')
                                ->label('Secret Key')
                                ->password()
                                ->revealable()
                                ->maxLength(255),
                            TextInput::make('media_r2_bucket')
                                ->label('Bucket')
                                ->maxLength(255),
                            TextInput::make('media_r2_public_url')
                                ->label('Public URL')
                                ->url()
                                ->placeholder('https://media.example.com')
                                ->helperText('Custom domain or Workers URL for public access.')
                                ->maxLength(2048),
                        ])
                        ->columns(2)
                        ->visible(fn (Get $get): bool => $get('media_disk') === 'r2'),

                    Section::make('Google Cloud Storage')
                        ->description('Credentials for GCP Cloud Storage.')
                        ->schema([
                            TextInput::make('media_gcs_project_id')
                                ->label('Project ID')
                                ->maxLength(255),
                            TextInput::make('media_gcs_bucket')
                                ->label('Bucket')
                                ->maxLength(255),
                            Textarea::make('media_gcs_key_json')
                                ->label('Service Account Key (JSON)')
                                ->rows(6)
                                ->helperText('Paste the full JSON key file content. It will be encrypted at rest.'),
                        ])
                        ->visible(fn (Get $get): bool => $get('media_disk') === 'gcs'),

                    Section::make('Azure Blob Storage')
                        ->description('Credentials for Azure Blob Storage.')
                        ->schema([
                            TextInput::make('media_azure_account')
                                ->label('Storage Account Name')
                                ->maxLength(255),
                            TextInput::make('media_azure_key')
                                ->label('Account Key')
                                ->password()
                                ->revealable()
                                ->maxLength(255),
                            TextInput::make('media_azure_container')
                                ->label('Container')
                                ->maxLength(255),
                            TextInput::make('media_azure_url')
                                ->label('CDN / Custom Domain URL')
                                ->url()
                                ->placeholder('https://media.example.com')
                                ->helperText('Optional custom domain URL.')
                                ->maxLength(2048),
                        ])
                        ->columns(2)
                        ->visible(fn (Get $get): bool => $get('media_disk') === 'azure'),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
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
            ->title('Media settings saved')
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
