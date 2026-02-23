<?php

namespace App\Filament\Actions\Ai;

use App\Services\AiService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Set;

class AiGenerateAction extends Action
{
    protected string $aiLocale = 'ms';

    public static function make(?string $id = null): static
    {
        return parent::make($id ?? 'ai_generate');
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
            ->label(__('ai_admin.generate_from_prompt'))
            ->icon('heroicon-o-sparkles')
            ->color('gray')
            ->size('sm')
            ->visible(fn (): bool => AiGrammarAction::isAiEditorEnabled())
            ->schema([
                Textarea::make('prompt')
                    ->label(__('ai_admin.enter_prompt'))
                    ->required()
                    ->rows(4),
            ])
            ->modalHeading(__('ai_admin.generate_from_prompt'))
            ->modalSubmitActionLabel(__('ai_admin.generate'))
            ->action(function (array $data, Set $schemaSet) use ($locale): void {
                $result = app(AiService::class)->generateFromPrompt($data['prompt'], $locale);

                if ($result === '') {
                    Notification::make()->danger()
                        ->title(__('ai_admin.ai_error'))->send();

                    return;
                }

                $fieldName = $this->getSchemaComponent()?->getName();

                if ($fieldName !== null) {
                    $schemaSet($fieldName, $result);
                }

                Notification::make()->success()
                    ->title(__('ai_admin.generated'))->send();
            });
    }
}
