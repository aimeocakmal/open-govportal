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

/**
 * @property-read Schema $form
 */
class ManageMinisterProfile extends Page
{
    protected string $view = 'filament.pages.manage-minister-profile';

    protected static \UnitEnum|string|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    protected static ?int $navigationSort = 21;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

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
                        ->label('Full Name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('photo')
                        ->label('Photo URL')
                        ->url()
                        ->maxLength(2048),
                    DatePicker::make('appointed_at')
                        ->label('Appointed Date'),
                    Toggle::make('is_current')
                        ->label('Current Minister')
                        ->default(true),
                    Tabs::make('Locales')
                        ->tabs([
                            Tab::make('Bahasa Malaysia')
                                ->schema([
                                    TextInput::make('title_ms')
                                        ->label('Title (BM)')
                                        ->maxLength(500),
                                    RichEditor::make('bio_ms')
                                        ->label('Biography (BM)'),
                                ]),
                            Tab::make('English')
                                ->schema([
                                    TextInput::make('title_en')
                                        ->label('Title (EN)')
                                        ->maxLength(500),
                                    RichEditor::make('bio_en')
                                        ->label('Biography (EN)'),
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
            ->title('Minister profile saved')
            ->send();
    }

    public function getRecord(): ?MinisterProfile
    {
        return MinisterProfile::query()
            ->where('is_current', true)
            ->first();
    }
}
