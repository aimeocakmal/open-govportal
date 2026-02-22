<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasConfigurableNavigation;
use App\Models\FeedbackSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

/**
 * @property-read Schema $form
 */
class ManageFeedbackSettings extends Page
{
    use HasConfigurableNavigation;

    protected static string $sidebarKey = 'manage-feedback-settings';

    protected string $view = 'filament.pages.manage-feedback-settings';

    protected static \UnitEnum|string|null $navigationGroup = null;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.settings.feedback.title');
    }

    public function getTitle(): string
    {
        return static::getNavigationLabel();
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 23;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return Auth::user()?->can('manage_settings') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'is_enabled' => filter_var(FeedbackSetting::get('is_enabled', false), FILTER_VALIDATE_BOOLEAN),
            'recipient_email' => FeedbackSetting::get('recipient_email'),
            'success_message_ms' => FeedbackSetting::get('success_message_ms'),
            'success_message_en' => FeedbackSetting::get('success_message_en'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Toggle::make('is_enabled')
                        ->label(__('filament.settings.feedback.enable_form'))
                        ->default(false),
                    TextInput::make('recipient_email')
                        ->label(__('filament.settings.feedback.recipient_email'))
                        ->email()
                        ->maxLength(255),
                    Textarea::make('success_message_ms')
                        ->label(__('filament.settings.feedback.success_message_bm'))
                        ->rows(3),
                    Textarea::make('success_message_en')
                        ->label(__('filament.settings.feedback.success_message_en'))
                        ->rows(3),
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

        FeedbackSetting::set('is_enabled', $data['is_enabled'] ? '1' : '0');
        FeedbackSetting::set('recipient_email', $data['recipient_email'] ?? '');
        FeedbackSetting::set('success_message_ms', $data['success_message_ms'] ?? '');
        FeedbackSetting::set('success_message_en', $data['success_message_en'] ?? '');

        Notification::make()
            ->success()
            ->title(__('filament.settings.feedback.saved'))
            ->send();
    }
}
