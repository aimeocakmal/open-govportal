<?php

namespace App\Livewire;

use App\Models\Achievement;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class PencapaianList extends Component
{
    use WithPagination;

    public string $year = '';

    public function updatedYear(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $achievements = Achievement::published()
            ->when($this->year, fn ($q) => $q->whereYear('date', $this->year))
            ->orderByDesc('date')
            ->paginate(15);

        $years = Achievement::published()
            ->whereNotNull('date')
            ->orderByDesc('date')
            ->pluck('date')
            ->map(fn ($date) => (int) $date->format('Y'))
            ->unique()
            ->values();

        return view('livewire.pencapaian-list', [
            'achievements' => $achievements,
            'years' => $years,
        ]);
    }
}
