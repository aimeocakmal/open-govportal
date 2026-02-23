<?php

namespace App\Filament\Actions\Ai;

use App\Services\AiService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class AiTranslateAction extends Action
{
    protected string $fromLocale = 'ms';

    protected string $toLocale = 'en';

    protected string $sourceFieldName = '';

    protected string $targetFieldName = '';

    public static function make(?string $id = null): static
    {
        return parent::make($id ?? 'ai_translate');
    }

    public function from(string $locale): static
    {
        $this->fromLocale = $locale;

        return $this;
    }

    public function to(string $locale): static
    {
        $this->toLocale = $locale;

        return $this;
    }

    public function sourceField(string $field): static
    {
        $this->sourceFieldName = $field;

        return $this;
    }

    public function targetField(string $field): static
    {
        $this->targetFieldName = $field;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $from = $this->fromLocale;
        $to = $this->toLocale;
        $source = $this->sourceFieldName;
        $target = $this->targetFieldName;

        $label = $to === 'en' ? __('ai_admin.translate_to_en') : __('ai_admin.translate_to_ms');

        $this
            ->label($label)
            ->icon('heroicon-o-language')
            ->color('gray')
            ->size('sm')
            ->visible(fn (): bool => AiGrammarAction::isAiEditorEnabled())
            ->requiresConfirmation()
            ->action(function (Get $schemaGet, Set $schemaSet) use ($from, $to, $source, $target): void {
                $sourceField = $source !== '' ? $source : $this->getSchemaComponent()?->getName();

                if ($sourceField === null) {
                    return;
                }

                $text = $schemaGet($sourceField);

                if (blank($text)) {
                    Notification::make()->warning()
                        ->title(__('ai_admin.field_empty'))->send();

                    return;
                }

                $result = app(AiService::class)->translate($text, $from, $to);

                if ($result === '') {
                    Notification::make()->danger()
                        ->title(__('ai_admin.ai_error'))->send();

                    return;
                }

                $targetField = $target !== '' ? $target : $sourceField;
                $schemaSet($targetField, $result);
                Notification::make()->success()
                    ->title(__('ai_admin.translated'))->send();
            });
    }
}
