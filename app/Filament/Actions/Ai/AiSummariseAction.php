<?php

namespace App\Filament\Actions\Ai;

use App\Services\AiService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class AiSummariseAction extends Action
{
    protected string $aiLocale = 'ms';

    public static function make(?string $id = null): static
    {
        return parent::make($id ?? 'ai_summarise');
    }

    public function locale(string $locale): static
    {
        $this->aiLocale = $locale;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $locale = $this->aiLocale;

        $this
            ->label(__('ai_admin.summarise'))
            ->icon('heroicon-o-document-minus')
            ->color('gray')
            ->size('sm')
            ->visible(fn (): bool => AiGrammarAction::isAiEditorEnabled())
            ->action(function (Get $schemaGet, Set $schemaSet) use ($locale): void {
                $fieldName = $this->getSchemaComponent()?->getName();

                if ($fieldName === null) {
                    return;
                }

                $text = $schemaGet($fieldName);

                if (blank($text)) {
                    Notification::make()->warning()
                        ->title(__('ai_admin.field_empty'))->send();

                    return;
                }

                $result = app(AiService::class)->summarise($text, $locale);

                if ($result === '') {
                    Notification::make()->danger()
                        ->title(__('ai_admin.ai_error'))->send();

                    return;
                }

                $schemaSet($fieldName, $result);
                Notification::make()->success()
                    ->title(__('ai_admin.summarised'))->send();
            });
    }
}
