<?php

namespace App\Filament\Concerns;

use App\Filament\Actions\Ai\AiExpandAction;
use App\Filament\Actions\Ai\AiGenerateAction;
use App\Filament\Actions\Ai\AiGrammarAction;
use App\Filament\Actions\Ai\AiSummariseAction;
use App\Filament\Actions\Ai\AiTldrAction;
use App\Filament\Actions\Ai\AiTranslateAction;
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
                AiTranslateAction::make("translate_{$locale}_to_{$otherLocale}")
                    ->sourceField($contentField)->targetField($otherContentField)
                    ->from($locale)->to($otherLocale),
                AiTldrAction::make("tldr_{$locale}")
                    ->sourceField($contentField)
                    ->locale($locale),
                AiGenerateAction::make("generate_{$locale}")->locale($locale),
            ])
                ->label(__('ai_admin.generate_ai'))
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->button()
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
                AiTranslateAction::make("translate_{$locale}_to_{$otherLocale}")
                    ->sourceField($field)->targetField($otherField)
                    ->from($locale)->to($otherLocale),
            ])
                ->label(__('ai_admin.generate_ai'))
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->button()
                ->size('sm')
                ->visible(fn (): bool => AiGrammarAction::isAiEditorEnabled()),
        ];
    }

    /**
     * AI actions dropdown for excerpt/short text fields (grammar, translate).
     *
     * @return array<int, ActionGroup>
     */
    protected static function excerptAiActions(
        string $locale,
        string $otherLocale,
        string $field,
        string $otherField,
    ): array {
        return [
            ActionGroup::make([
                AiGrammarAction::make("grammar_excerpt_{$locale}")->locale($locale),
                AiTranslateAction::make("translate_excerpt_{$locale}_to_{$otherLocale}")
                    ->sourceField($field)->targetField($otherField)
                    ->from($locale)->to($otherLocale),
            ])
                ->label(__('ai_admin.generate_ai'))
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->button()
                ->size('sm')
                ->visible(fn (): bool => AiGrammarAction::isAiEditorEnabled()),
        ];
    }
}
