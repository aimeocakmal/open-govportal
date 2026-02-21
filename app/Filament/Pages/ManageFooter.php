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

    protected static \UnitEnum|string|null $navigationGroup = null;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.settings');
    }

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
                        ->label(__('filament.settings.footer.footer_links'))
                        ->schema([
                            Select::make('section')
                                ->options([
                                    'links' => __('filament.settings.footer.section_links'),
                                    'social' => __('filament.settings.footer.section_social'),
                                    'legal' => __('filament.settings.footer.section_legal'),
                                ])
                                ->required(),
                            TextInput::make('label_ms')
                                ->label(__('filament.common.label_bm'))
                                ->required()
                                ->maxLength(200),
                            TextInput::make('label_en')
                                ->label(__('filament.common.label_en'))
                                ->required()
                                ->maxLength(200),
                            TextInput::make('url')
                                ->label(__('filament.common.url'))
                                ->url()
                                ->maxLength(2048),
                            TextInput::make('sort_order')
                                ->label(__('filament.common.sort_order'))
                                ->numeric()
                                ->default(0),
                            Toggle::make('is_active')
                                ->label(__('filament.common.active'))
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
            ->title(__('filament.settings.footer.saved'))
            ->send();
    }
}
