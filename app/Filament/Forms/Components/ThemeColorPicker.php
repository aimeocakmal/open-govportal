<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class ThemeColorPicker extends Field
{
    protected string $view = 'filament.forms.components.theme-color-picker';

    /** @var array<string, string> */
    protected array $colorOptions = [
        'orange' => '#F97316',
        'yellow' => '#EAB308',
        'lime' => '#84CC16',
        'green' => '#22C55E',
        'sky' => '#0EA5E9',
        'blue' => '#3B82F6',
        'indigo' => '#6366F1',
        'purple' => '#A855F7',
        'pink' => '#EC4899',
        'slate' => '#64748B',
    ];

    /**
     * @return array<string, string>
     */
    public function getColorOptions(): array
    {
        return $this->colorOptions;
    }
}
