<?php

namespace App\Filament\Actions\Ai;

use App\Services\AiService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class AiWriteExcerptAction extends Action
{
    protected string $aiLocale = 'ms';

    protected string $contentFieldName = '';

    public static function make(?string $id = null): static
    {
        return parent::make($id ?? 'ai_write_excerpt');
    }

    public function locale(string $locale): static
    {
        $this->aiLocale = $locale;

        return $this;
    }

    /**
     * The content/article field to read from for generating the excerpt.
     */
    public function contentField(string $field): static
    {
        $this->contentFieldName = $field;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $locale = $this->aiLocale;
        $contentField = $this->contentFieldName;

        $this
            ->label(__('ai_admin.write_excerpt'))
            ->icon('heroicon-o-pencil-square')
            ->color('gray')
            ->size('sm')
            ->visible(fn (): bool => AiGrammarAction::isAiEditorEnabled())
            ->action(function (Get $schemaGet, Set $schemaSet) use ($locale, $contentField): void {
                $excerptField = $this->getSchemaComponent()?->getName();

                if ($excerptField === null) {
                    return;
                }

                $sourceField = $contentField !== '' ? $contentField : $excerptField;
                $text = $schemaGet($sourceField);

                if (blank($text)) {
                    Notification::make()->warning()
                        ->title(__('ai_admin.content_empty_for_excerpt'))->send();

                    return;
                }

                $result = app(AiService::class)->writeExcerpt(strip_tags($text), $locale);

                if ($result === '') {
                    Notification::make()->danger()
                        ->title(__('ai_admin.ai_error'))->send();

                    return;
                }

                $schemaSet($excerptField, $result);
                Notification::make()->success()
                    ->title(__('ai_admin.excerpt_written'))->send();
            });
    }
}
