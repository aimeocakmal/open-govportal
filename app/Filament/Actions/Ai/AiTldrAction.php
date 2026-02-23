<?php

namespace App\Filament\Actions\Ai;

use App\Services\AiService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class AiTldrAction extends Action
{
    protected string $aiLocale = 'ms';

    protected string $sourceFieldName = '';

    public static function make(?string $id = null): static
    {
        return parent::make($id ?? 'ai_tldr');
    }

    public function locale(string $locale): static
    {
        $this->aiLocale = $locale;

        return $this;
    }

    public function sourceField(string $field): static
    {
        $this->sourceFieldName = $field;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('ai_admin.tldr'))
            ->icon('heroicon-o-bolt')
            ->color('gray')
            ->size('sm')
            ->visible(fn (): bool => AiGrammarAction::isAiEditorEnabled())
            ->action(function (Get $schemaGet, Set $schemaSet): void {
                $source = $this->sourceFieldName;
                $fieldName = $source !== '' ? $source : $this->getSchemaComponent()?->getName();

                if ($fieldName === null) {
                    return;
                }

                $text = $schemaGet($fieldName);

                if (blank($text)) {
                    Notification::make()->warning()
                        ->title(__('ai_admin.field_empty'))->send();

                    return;
                }

                $result = app(AiService::class)->tldr(strip_tags($text), $this->aiLocale);

                if ($result === '') {
                    Notification::make()->danger()
                        ->title(__('ai_admin.ai_error'))->send();

                    return;
                }

                $cleanResult = strip_tags($result, '<ul><li>');

                $tldrBlock = '<p><strong>TL;DR</strong></p>'
                    .$cleanResult
                    .'<hr>';

                $schemaSet($fieldName, $tldrBlock.$text);
                Notification::make()->success()
                    ->title(__('ai_admin.tldr_generated'))->send();
            });
    }
}
