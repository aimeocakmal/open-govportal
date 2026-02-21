<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
class ManageEmailSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?int $navigationSort = 2;

    protected static \UnitEnum|string|null $navigationGroup = 'Settings';

    protected static ?string $title = 'Email Settings';

    protected string $view = 'filament.pages.manage-email-settings';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return Auth::user()?->can('manage_settings') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'mail_mailer' => Setting::get('mail_mailer', 'ses'),
            'mail_host' => Setting::get('mail_host', ''),
            'mail_port' => Setting::get('mail_port', '587'),
            'mail_username' => Setting::get('mail_username', ''),
            'mail_password' => $this->decryptSetting('mail_password'),
            'mail_encryption' => Setting::get('mail_encryption', 'tls'),
            'mail_from_address' => Setting::get('mail_from_address', ''),
            'mail_from_name_ms' => Setting::get('mail_from_name_ms', ''),
            'mail_from_name_en' => Setting::get('mail_from_name_en', ''),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('Mail Driver')
                        ->description('Select the mail transport to use for sending emails.')
                        ->schema([
                            Select::make('mail_mailer')
                                ->label('Mail Driver')
                                ->options([
                                    'ses' => 'Amazon SES',
                                    'smtp' => 'SMTP',
                                    'mailgun' => 'Mailgun',
                                    'log' => 'Log (Testing)',
                                ])
                                ->required()
                                ->live(),
                        ]),

                    Section::make('SMTP Configuration')
                        ->description('Configure SMTP server credentials. Only required when mail driver is SMTP.')
                        ->schema([
                            TextInput::make('mail_host')
                                ->label('SMTP Host')
                                ->placeholder('smtp.example.com')
                                ->maxLength(255),
                            TextInput::make('mail_port')
                                ->label('SMTP Port')
                                ->placeholder('587')
                                ->numeric()
                                ->maxLength(5),
                            TextInput::make('mail_username')
                                ->label('SMTP Username')
                                ->maxLength(255),
                            TextInput::make('mail_password')
                                ->label('SMTP Password')
                                ->password()
                                ->revealable()
                                ->maxLength(255),
                            Select::make('mail_encryption')
                                ->label('Encryption')
                                ->options([
                                    'tls' => 'TLS',
                                    'ssl' => 'SSL',
                                    '' => 'None',
                                ]),
                        ])
                        ->columns(2)
                        ->visible(fn (Get $get): bool => $get('mail_mailer') === 'smtp'),

                    Section::make('Sender Information')
                        ->description('Configure the default sender address and display name.')
                        ->schema([
                            TextInput::make('mail_from_address')
                                ->label('From Address')
                                ->email()
                                ->maxLength(255),
                            Tabs::make('from_name_tabs')
                                ->tabs([
                                    Tab::make('Bahasa Malaysia')
                                        ->schema([
                                            TextInput::make('mail_from_name_ms')
                                                ->label('From Name (BM)')
                                                ->maxLength(255),
                                        ]),
                                    Tab::make('English')
                                        ->schema([
                                            TextInput::make('mail_from_name_en')
                                                ->label('From Name (EN)')
                                                ->maxLength(255),
                                        ]),
                                ]),
                        ]),
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
            'mail_mailer',
            'mail_host',
            'mail_port',
            'mail_username',
            'mail_encryption',
            'mail_from_address',
            'mail_from_name_ms',
            'mail_from_name_en',
        ];

        foreach ($plainKeys as $key) {
            Setting::set($key, $data[$key] ?? '');
        }

        $this->saveEncryptedSetting('mail_password', $data['mail_password'] ?? '');

        Notification::make()
            ->success()
            ->title('Email settings saved')
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
