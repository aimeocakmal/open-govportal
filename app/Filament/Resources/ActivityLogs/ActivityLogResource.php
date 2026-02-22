<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Concerns\HasConfigurableNavigation;
use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Filament\Resources\ActivityLogs\Pages\ViewActivityLog;
use App\Filament\Resources\ActivityLogs\Tables\ActivityLogsTable;
use BackedEnum;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    use HasConfigurableNavigation;

    protected static string $sidebarKey = 'activity-logs';

    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static \UnitEnum|string|null $navigationGroup = null;

    protected static ?int $navigationSort = 9;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.settings');
    }

    public static function getModelLabel(): string
    {
        return trans_choice('filament.models.activity_log', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('filament.models.activity_log', 2);
    }

    public static function table(Table $table): Table
    {
        return ActivityLogsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Infolists\Components\Section::make(__('filament.resource.activity_logs.activity_details'))
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label(__('filament.resource.activity_logs.timestamp'))
                            ->dateTime('d M Y H:i:s'),
                        Infolists\Components\TextEntry::make('causer.name')
                            ->label(__('filament.resource.activity_logs.user'))
                            ->placeholder(__('filament.resource.activity_logs.system')),
                        Infolists\Components\TextEntry::make('causer.email')
                            ->label(__('filament.common.email'))
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('description')
                            ->label(__('filament.resource.activity_logs.event'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'created' => 'success',
                                'updated' => 'info',
                                'deleted' => 'danger',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('subject_type')
                            ->label(__('filament.resource.activity_logs.module'))
                            ->formatStateUsing(fn (?string $state) => static::getModuleName($state)),
                        Infolists\Components\TextEntry::make('subject_id')
                            ->label(__('filament.resource.activity_logs.record_id')),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make(__('filament.resource.activity_logs.changes'))
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('properties.old')
                            ->label(__('filament.resource.activity_logs.old_values'))
                            ->placeholder(__('filament.resource.activity_logs.no_changes'))
                            ->columnSpanFull(),
                        Infolists\Components\KeyValueEntry::make('properties.attributes')
                            ->label(__('filament.resource.activity_logs.new_values'))
                            ->placeholder(__('filament.resource.activity_logs.no_changes'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
            'view' => ViewActivityLog::route('/{record}'),
        ];
    }

    /** @var array<string, string> */
    private const MODULE_MAP = [
        'App\Models\Broadcast' => 'broadcast',
        'App\Models\Achievement' => 'achievement',
        'App\Models\Celebration' => 'celebration',
        'App\Models\Policy' => 'policy',
        'App\Models\PolicyFile' => 'policy_file',
        'App\Models\StaffDirectory' => 'staff_directory',
        'App\Models\HeroBanner' => 'hero_banner',
        'App\Models\QuickLink' => 'quick_link',
        'App\Models\Media' => 'media',
        'App\Models\Feedback' => 'feedback',
        'App\Models\SearchOverride' => 'search_override',
        'App\Models\Menu' => 'menu',
        'App\Models\MenuItem' => 'menu',
        'App\Models\PageCategory' => 'page_category',
        'App\Models\StaticPage' => 'static_page',
        'App\Models\User' => 'user',
    ];

    public static function getModuleName(?string $morphClass): string
    {
        if ($morphClass === null) {
            return __('filament.resource.activity_logs.unknown_module');
        }

        $key = self::MODULE_MAP[$morphClass] ?? null;

        if ($key === null) {
            return class_basename($morphClass);
        }

        return trans_choice("filament.models.{$key}", 1);
    }

    /**
     * @return array<string, string>
     */
    public static function getModuleOptions(): array
    {
        $types = Activity::query()
            ->distinct()
            ->whereNotNull('subject_type')
            ->pluck('subject_type')
            ->all();

        $options = [];
        foreach ($types as $type) {
            $options[$type] = static::getModuleName($type);
        }

        return $options;
    }
}
