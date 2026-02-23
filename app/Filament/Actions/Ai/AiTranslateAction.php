<?php

namespace App\Filament\Actions\Ai;

use App\Services\AiService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class AiTranslateAction extends Action
{
    protected string $currentLocale = 'ms';

    /** @var array<string, string> locale => field name */
    protected array $localeFieldMap = [];

    public static function make(?string $id = null): static
    {
        return parent::make($id ?? 'ai_translate');
    }

    public function currentLocale(string $locale): static
    {
        $this->currentLocale = $locale;

        return $this;
    }

    /**
     * Map of locale code to form field name (e.g. ['ms' => 'content_ms', 'en' => 'content_en']).
     *
     * @param  array<string, string>  $fields
     */
    public function localeFields(array $fields): static
    {
        $this->localeFieldMap = $fields;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $currentLocale = $this->currentLocale;
        $localeFields = $this->localeFieldMap;

        $this
            ->label(__('ai_admin.translate'))
            ->icon('heroicon-o-language')
            ->color('gray')
            ->size('sm')
            ->visible(fn (): bool => AiGrammarAction::isAiEditorEnabled())
            ->schema([
                Select::make('target_locale')
                    ->label(__('ai_admin.translate_to'))
                    ->options([
                        'ms' => 'Bahasa Malaysia (BM)',
                        'en' => 'English (EN)',
                    ])
                    ->required(),
            ])
            ->action(function (array $data, Get $schemaGet, Set $schemaSet) use ($currentLocale, $localeFields): void {
                $targetLocale = $data['target_locale'];
                $sourceField = $localeFields[$currentLocale] ?? $this->getSchemaComponent()?->getName();
                $targetField = $localeFields[$targetLocale] ?? $sourceField;

                if ($sourceField === null) {
                    return;
                }

                $text = $schemaGet($sourceField);

                if (blank($text)) {
                    Notification::make()->warning()
                        ->title(__('ai_admin.field_empty'))->send();

                    return;
                }

                $result = app(AiService::class)->translate($text, $currentLocale, $targetLocale);

                if ($result === '') {
                    Notification::make()->danger()
                        ->title(__('ai_admin.ai_error'))->send();

                    return;
                }

                $schemaSet($targetField, $result);
                Notification::make()->success()
                    ->title(__('ai_admin.translated'))->send();
            });
    }
}
