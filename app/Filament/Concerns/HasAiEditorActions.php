<?php

namespace App\Filament\Concerns;

use App\Filament\Actions\Ai\AiExpandAction;
use App\Filament\Actions\Ai\AiGenerateAction;
use App\Filament\Actions\Ai\AiGrammarAction;
use App\Filament\Actions\Ai\AiSummariseAction;
use App\Filament\Actions\Ai\AiTldrAction;
use App\Filament\Actions\Ai\AiTranslateAction;
use App\Filament\Actions\Ai\AiWriteExcerptAction;
use Filament\Actions\ActionGroup;

trait HasAiEditorActions
{
    /**
     * AI actions dropdown for RichEditor content fields (grammar, expand, summarise, translate, TLDR, generate).
     *
     * @return array<int, ActionGroup>
     */
    protected static function richEditorAiActions(
        string $locale,
        string $otherLocale,
        string $contentField,
        string $otherContentField,
    ): array {
        return [
            ActionGroup::make([
                AiGrammarAction::make("grammar_{$locale}")->locale($locale),
                AiExpandAction::make("expand_{$locale}")->locale($locale),
                AiSummariseAction::make("summarise_{$locale}")->locale($locale),
                AiTranslateAction::make("translate_{$locale}")
                    ->currentLocale($locale)
                    ->localeFields([$locale => $contentField, $otherLocale => $otherContentField]),
                AiTldrAction::make("tldr_{$locale}")
                    ->sourceField($contentField)
                    ->locale($locale),
                AiGenerateAction::make("generate_{$locale}")->locale($locale),
            ])
                ->label(__('ai_admin.generate_ai'))
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->button()
                ->outlined()
                ->size('sm')
                ->visible(fn (): bool => AiGrammarAction::isAiEditorEnabled()),
        ];
    }

    /**
     * AI actions dropdown for Textarea description fields (grammar, expand, summarise, translate).
     *
     * @return array<int, ActionGroup>
     */
    protected static function textareaAiActions(
        string $locale,
        string $otherLocale,
        string $field,
        string $otherField,
    ): array {
        return [
            ActionGroup::make([
                AiGrammarAction::make("grammar_{$locale}")->locale($locale),
                AiExpandAction::make("expand_{$locale}")->locale($locale),
                AiSummariseAction::make("summarise_{$locale}")->locale($locale),
                AiTranslateAction::make("translate_{$locale}")
                    ->currentLocale($locale)
                    ->localeFields([$locale => $field, $otherLocale => $otherField]),
            ])
                ->label(__('ai_admin.generate_ai'))
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->button()
                ->outlined()
                ->size('sm')
                ->visible(fn (): bool => AiGrammarAction::isAiEditorEnabled()),
        ];
    }

    /**
     * AI actions dropdown for excerpt/short text fields (write excerpt, grammar, translate).
     *
     * @return array<int, ActionGroup>
     */
    protected static function excerptAiActions(
        string $locale,
        string $otherLocale,
        string $field,
        string $otherField,
        string $contentField = '',
    ): array {
        return [
            ActionGroup::make(array_filter([
                $contentField !== '' ? AiWriteExcerptAction::make("write_excerpt_{$locale}")
                    ->contentField($contentField)
                    ->locale($locale) : null,
                AiGrammarAction::make("grammar_excerpt_{$locale}")->locale($locale),
                AiTranslateAction::make("translate_excerpt_{$locale}")
                    ->currentLocale($locale)
                    ->localeFields([$locale => $field, $otherLocale => $otherField]),
            ]))
                ->label(__('ai_admin.generate_ai'))
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->button()
                ->outlined()
                ->size('sm')
                ->visible(fn (): bool => AiGrammarAction::isAiEditorEnabled()),
        ];
    }
}
