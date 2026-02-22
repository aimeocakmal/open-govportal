<?php

namespace App\Livewire;

use App\Models\Broadcast;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class SiaranList extends Component
{
    use WithPagination;

    public string $type = '';

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $broadcasts = Broadcast::published()
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->orderByDesc('published_at')
            ->paginate(15);

        return view('livewire.siaran-list', [
            'broadcasts' => $broadcasts,
        ]);
    }
}
