<?php

namespace App\Filament\Pages;

use App\Models\MinisterProfile;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

/**
 * @property-read Schema $form
 */
class ManageMinisterProfile extends Page
{
    protected string $view = 'filament.pages.manage-minister-profile';

    protected static \UnitEnum|string|null $navigationGroup = null;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.settings');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    protected static ?int $navigationSort = 21;

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
        $this->form->fill($this->getRecord()?->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    TextInput::make('name')
                        ->label(__('filament.settings.minister.full_name'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('photo')
                        ->label(__('filament.common.photo_url'))
                        ->url()
                        ->maxLength(2048),
                    DatePicker::make('appointed_at')
                        ->label(__('filament.settings.minister.appointed_date')),
                    Toggle::make('is_current')
                        ->label(__('filament.settings.minister.current_minister'))
                        ->default(true),
                    Tabs::make(__('filament.settings.minister.locales'))
                        ->tabs([
                            Tab::make(__('filament.common.bahasa_malaysia'))
                                ->schema([
                                    TextInput::make('title_ms')
                                        ->label(__('filament.settings.minister.title_bm'))
                                        ->maxLength(500),
                                    RichEditor::make('bio_ms')
                                        ->label(__('filament.settings.minister.bio_bm')),
                                ]),
                            Tab::make(__('filament.common.english'))
                                ->schema([
                                    TextInput::make('title_en')
                                        ->label(__('filament.settings.minister.title_en'))
                                        ->maxLength(500),
                                    RichEditor::make('bio_en')
                                        ->label(__('filament.settings.minister.bio_en')),
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
            ->record($this->getRecord())
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $record = $this->getRecord();

        if (! $record) {
            $record = new MinisterProfile;
        }

        $record->fill($data);
        $record->save();

        if ($record->wasRecentlyCreated) {
            $this->form->record($record)->saveRelationships();
        }

        Notification::make()
            ->success()
            ->title(__('filament.settings.minister.saved'))
            ->send();
    }

    public function getRecord(): ?MinisterProfile
    {
        return MinisterProfile::query()
            ->where('is_current', true)
            ->first();
    }
}
