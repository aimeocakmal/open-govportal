<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

/**
 * @property-read Schema $form
 */
class ManageSiteInfo extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 1;

    protected static \UnitEnum|string|null $navigationGroup = null;

    protected static ?string $title = null;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.settings');
    }

    public static function getLabel(): string
    {
        return __('filament.settings.site_info.title');
    }

    protected string $view = 'filament.pages.manage-site-info';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return Auth::user()?->can('manage_settings') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'site_name_ms' => Setting::get('site_name_ms', ''),
            'site_name_en' => Setting::get('site_name_en', ''),
            'site_description_ms' => Setting::get('site_description_ms', ''),
            'site_description_en' => Setting::get('site_description_en', ''),
            'site_logo' => Setting::get('site_logo', ''),
            'site_logo_dark' => Setting::get('site_logo_dark', ''),
            'site_logo_alt_ms' => Setting::get('site_logo_alt_ms', ''),
            'site_logo_alt_en' => Setting::get('site_logo_alt_en', ''),
            'site_favicon' => Setting::get('site_favicon', ''),
            'facebook_url' => Setting::get('facebook_url', ''),
            'twitter_url' => Setting::get('twitter_url', ''),
            'instagram_url' => Setting::get('instagram_url', ''),
            'youtube_url' => Setting::get('youtube_url', ''),
            'google_analytics_id' => Setting::get('google_analytics_id', ''),
            'site_default_theme' => Setting::get('site_default_theme', 'default'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make(__('filament.settings.site_info.site_identity'))
                        ->description(__('filament.settings.site_info.site_identity_desc'))
                        ->schema([
                            Tabs::make('site_identity_tabs')
                                ->tabs([
                                    Tab::make(__('filament.common.bahasa_malaysia'))
                                        ->schema([
                                            TextInput::make('site_name_ms')
                                                ->label(__('filament.settings.site_info.site_name_bm'))
                                                ->required()
                                                ->maxLength(255),
                                            Textarea::make('site_description_ms')
                                                ->label(__('filament.settings.site_info.site_desc_bm'))
                                                ->rows(3)
                                                ->maxLength(1000),
                                        ]),
                                    Tab::make(__('filament.common.english'))
                                        ->schema([
                                            TextInput::make('site_name_en')
                                                ->label(__('filament.settings.site_info.site_name_en'))
                                                ->maxLength(255),
                                            Textarea::make('site_description_en')
                                                ->label(__('filament.settings.site_info.site_desc_en'))
                                                ->rows(3)
                                                ->maxLength(1000),
                                        ]),
                                ]),
                        ]),

                    Section::make(__('filament.settings.site_info.branding'))
                        ->description(__('filament.settings.site_info.branding_desc'))
                        ->schema([
                            FileUpload::make('site_logo')
                                ->label(__('filament.settings.site_info.site_logo_light'))
                                ->image()
                                ->directory('branding')
                                ->maxSize(2048),
                            FileUpload::make('site_logo_dark')
                                ->label(__('filament.settings.site_info.site_logo_dark'))
                                ->image()
                                ->directory('branding')
                                ->maxSize(2048),
                            Tabs::make('logo_alt_tabs')
                                ->tabs([
                                    Tab::make(__('filament.common.bahasa_malaysia'))
                                        ->schema([
                                            TextInput::make('site_logo_alt_ms')
                                                ->label(__('filament.settings.site_info.logo_alt_bm'))
                                                ->maxLength(255),
                                        ]),
                                    Tab::make(__('filament.common.english'))
                                        ->schema([
                                            TextInput::make('site_logo_alt_en')
                                                ->label(__('filament.settings.site_info.logo_alt_en'))
                                                ->maxLength(255),
                                        ]),
                                ]),
                            FileUpload::make('site_favicon')
                                ->label(__('filament.settings.site_info.favicon'))
                                ->acceptedFileTypes(['image/x-icon', 'image/png', 'image/svg+xml'])
                                ->directory('branding')
                                ->maxSize(512),
                        ]),

                    Section::make(__('filament.settings.site_info.social_media'))
                        ->description(__('filament.settings.site_info.social_media_desc'))
                        ->schema([
                            TextInput::make('facebook_url')
                                ->label(__('filament.settings.site_info.facebook_url'))
                                ->url()
                                ->maxLength(2048),
                            TextInput::make('twitter_url')
                                ->label(__('filament.settings.site_info.twitter_url'))
                                ->url()
                                ->maxLength(2048),
                            TextInput::make('instagram_url')
                                ->label(__('filament.settings.site_info.instagram_url'))
                                ->url()
                                ->maxLength(2048),
                            TextInput::make('youtube_url')
                                ->label(__('filament.settings.site_info.youtube_url'))
                                ->url()
                                ->maxLength(2048),
                        ])
                        ->columns(2),

                    Section::make(__('filament.settings.site_info.analytics'))
                        ->description(__('filament.settings.site_info.analytics_desc'))
                        ->schema([
                            TextInput::make('google_analytics_id')
                                ->label(__('filament.settings.site_info.google_analytics_id'))
                                ->placeholder('G-XXXXXXXXXX')
                                ->maxLength(50),
                            Select::make('site_default_theme')
                                ->label(__('filament.settings.site_info.default_theme'))
                                ->options([
                                    'default' => __('filament.settings.site_info.theme_default'),
                                    'dark' => __('filament.settings.site_info.theme_dark'),
                                ])
                                ->required(),
                        ])
                        ->columns(2),
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

        $keys = [
            'site_name_ms',
            'site_name_en',
            'site_description_ms',
            'site_description_en',
            'site_logo',
            'site_logo_dark',
            'site_logo_alt_ms',
            'site_logo_alt_en',
            'site_favicon',
            'facebook_url',
            'twitter_url',
            'instagram_url',
            'youtube_url',
            'google_analytics_id',
            'site_default_theme',
        ];

        foreach ($keys as $key) {
            Setting::set($key, $data[$key] ?? '');
        }

        Notification::make()
            ->success()
            ->title(__('filament.settings.site_info.saved'))
            ->send();
    }
}
