<?php

namespace App\Filament\Pages;

use App\Models\FooterSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
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
class ManageFooter extends Page
{
    protected string $view = 'filament.pages.manage-footer';

    protected static \UnitEnum|string|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3BottomLeft;

    protected static ?int $navigationSort = 20;

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
        $items = FooterSetting::query()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (FooterSetting $item): array => $item->toArray())
            ->values()
            ->all();

        $this->form->fill([
            'items' => $items,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Repeater::make('items')
                        ->label('Footer Links')
                        ->schema([
                            Select::make('section')
                                ->options([
                                    'links' => 'Links',
                                    'social' => 'Social',
                                    'legal' => 'Legal',
                                ])
                                ->required(),
                            TextInput::make('label_ms')
                                ->label('Label (BM)')
                                ->required()
                                ->maxLength(200),
                            TextInput::make('label_en')
                                ->label('Label (EN)')
                                ->required()
                                ->maxLength(200),
                            TextInput::make('url')
                                ->label('URL')
                                ->url()
                                ->maxLength(2048),
                            TextInput::make('sort_order')
                                ->label('Sort Order')
                                ->numeric()
                                ->default(0),
                            Toggle::make('is_active')
                                ->label('Active')
                                ->default(true),
                        ])
                        ->columns(3)
                        ->defaultItems(0)
                        ->reorderable()
                        ->collapsible(),
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
        $items = $data['items'] ?? [];

        FooterSetting::query()->delete();

        foreach ($items as $index => $item) {
            FooterSetting::create([
                'section' => $item['section'],
                'label_ms' => $item['label_ms'],
                'label_en' => $item['label_en'],
                'url' => $item['url'] ?? null,
                'sort_order' => $item['sort_order'] ?? $index,
                'is_active' => $item['is_active'] ?? true,
            ]);
        }

        Notification::make()
            ->success()
            ->title('Footer settings saved')
            ->send();
    }
}
