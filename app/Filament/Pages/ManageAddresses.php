<?php

namespace App\Filament\Pages;

use App\Models\Address;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
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
class ManageAddresses extends Page
{
    protected string $view = 'filament.pages.manage-addresses';

    protected static \UnitEnum|string|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?int $navigationSort = 22;

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
        $items = Address::query()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Address $item): array => $item->toArray())
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
                        ->label('Addresses')
                        ->schema([
                            TextInput::make('label_ms')
                                ->label('Label (BM)')
                                ->required()
                                ->maxLength(200),
                            TextInput::make('label_en')
                                ->label('Label (EN)')
                                ->required()
                                ->maxLength(200),
                            Textarea::make('address_ms')
                                ->label('Address (BM)')
                                ->rows(3),
                            Textarea::make('address_en')
                                ->label('Address (EN)')
                                ->rows(3),
                            TextInput::make('phone')
                                ->label('Phone')
                                ->tel()
                                ->maxLength(50),
                            TextInput::make('fax')
                                ->label('Fax')
                                ->maxLength(50),
                            TextInput::make('email')
                                ->label('Email')
                                ->email()
                                ->maxLength(255),
                            TextInput::make('google_maps_url')
                                ->label('Google Maps URL')
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
                        ->columns(2)
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

        Address::query()->delete();

        foreach ($items as $index => $item) {
            Address::create([
                'label_ms' => $item['label_ms'],
                'label_en' => $item['label_en'],
                'address_ms' => $item['address_ms'] ?? null,
                'address_en' => $item['address_en'] ?? null,
                'phone' => $item['phone'] ?? null,
                'fax' => $item['fax'] ?? null,
                'email' => $item['email'] ?? null,
                'google_maps_url' => $item['google_maps_url'] ?? null,
                'sort_order' => $item['sort_order'] ?? $index,
                'is_active' => $item['is_active'] ?? true,
            ]);
        }

        Notification::make()
            ->success()
            ->title('Addresses saved')
            ->send();
    }
}
