<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasConfigurableNavigation;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

/**
 * @property-read Schema $form
 */
class ManageHomepage extends Page
{
    use HasConfigurableNavigation;

    protected static string $sidebarKey = 'manage-homepage';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?int $navigationSort = 1;

    protected static \UnitEnum|string|null $navigationGroup = null;

    protected static ?string $title = null;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.homepage');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.settings.homepage.title');
    }

    public function getTitle(): string
    {
        return static::getNavigationLabel();
    }

    protected string $view = 'filament.pages.manage-homepage';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return Auth::user()?->can('manage_settings') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'homepage_show_hero_banner' => (bool) Setting::get('homepage_show_hero_banner', true),
            'homepage_show_quick_links' => (bool) Setting::get('homepage_show_quick_links', true),
            'homepage_show_broadcasts' => (bool) Setting::get('homepage_show_broadcasts', true),
            'homepage_show_achievements' => (bool) Setting::get('homepage_show_achievements', true),
            'homepage_show_feedback' => (bool) Setting::get('homepage_show_feedback', true),
            'homepage_broadcasts_count' => (int) Setting::get('homepage_broadcasts_count', 6),
            'homepage_achievements_count' => (int) Setting::get('homepage_achievements_count', 7),
            'homepage_section_order' => Setting::get('homepage_section_order', '["hero_banner","quick_links","broadcasts","achievements","feedback"]'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make(__('filament.settings.homepage.section_visibility'))
                        ->description(__('filament.settings.homepage.section_visibility_desc'))
                        ->schema([
                            Toggle::make('homepage_show_hero_banner')
                                ->label(__('filament.settings.homepage.show_hero_banner')),
                            Toggle::make('homepage_show_quick_links')
                                ->label(__('filament.settings.homepage.show_quick_links')),
                            Toggle::make('homepage_show_broadcasts')
                                ->label(__('filament.settings.homepage.show_broadcasts')),
                            Toggle::make('homepage_show_achievements')
                                ->label(__('filament.settings.homepage.show_achievements')),
                            Toggle::make('homepage_show_feedback')
                                ->label(__('filament.settings.homepage.show_feedback')),
                        ])
                        ->columns(2),

                    Section::make(__('filament.settings.homepage.content_limits'))
                        ->description(__('filament.settings.homepage.content_limits_desc'))
                        ->schema([
                            TextInput::make('homepage_broadcasts_count')
                                ->label(__('filament.settings.homepage.broadcasts_count'))
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(50)
                                ->required(),
                            TextInput::make('homepage_achievements_count')
                                ->label(__('filament.settings.homepage.achievements_count'))
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(50)
                                ->required(),
                        ])
                        ->columns(2),

                    Section::make(__('filament.settings.homepage.section_order'))
                        ->description(__('filament.settings.homepage.section_order_desc'))
                        ->schema([
                            Textarea::make('homepage_section_order')
                                ->label(__('filament.settings.homepage.section_order'))
                                ->helperText(__('filament.settings.homepage.section_order_help'))
                                ->rows(3)
                                ->required(),
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

        $booleanKeys = [
            'homepage_show_hero_banner',
            'homepage_show_quick_links',
            'homepage_show_broadcasts',
            'homepage_show_achievements',
            'homepage_show_feedback',
        ];

        foreach ($booleanKeys as $key) {
            Setting::set($key, $data[$key] ? '1' : '0');
        }

        Setting::set('homepage_broadcasts_count', (string) ($data['homepage_broadcasts_count'] ?? 6));
        Setting::set('homepage_achievements_count', (string) ($data['homepage_achievements_count'] ?? 7));
        Setting::set('homepage_section_order', $data['homepage_section_order'] ?? '["hero_banner","quick_links","broadcasts","achievements","feedback"]');

        Notification::make()
            ->success()
            ->title(__('filament.settings.homepage.saved'))
            ->send();
    }
}
