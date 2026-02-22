<?php

namespace App\Livewire;

use App\Models\StaffDirectory;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class DirektoriSearch extends Component
{
    use WithPagination;

    public string $query = '';

    public string $jabatan = '';

    public function updatedQuery(): void
    {
        $this->resetPage();
    }

    public function updatedJabatan(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $locale = app()->getLocale();
        $departmentField = "department_{$locale}";

        $staff = StaffDirectory::active()
            ->when($this->query, function ($q) use ($locale) {
                $search = '%'.$this->query.'%';
                $q->where(function ($sub) use ($search, $locale) {
                    $sub->where('name', 'like', $search)
                        ->orWhere("position_{$locale}", 'like', $search)
                        ->orWhere("department_{$locale}", 'like', $search);
                });
            })
            ->when($this->jabatan, fn ($q) => $q->where($departmentField, $this->jabatan))
            ->paginate(12);

        $departments = StaffDirectory::query()
            ->where('is_active', true)
            ->whereNotNull($departmentField)
            ->select($departmentField)
            ->distinct()
            ->orderBy($departmentField)
            ->pluck($departmentField);

        return view('livewire.direktori-search', [
            'staff' => $staff,
            'departments' => $departments,
        ]);
    }
}
