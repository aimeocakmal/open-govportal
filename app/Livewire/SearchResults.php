<?php

namespace App\Livewire;

use App\Models\SearchableContent;
use App\Models\SearchOverride;
use Illuminate\View\View;
use Livewire\Component;

class SearchResults extends Component
{
    public string $query = '';

    public function updatedQuery(): void
    {
        // Reset on query change — handled reactively in render()
    }

    public function render(): View
    {
        $locale = app()->getLocale();
        $results = collect();
        $overrides = collect();

        if (mb_strlen(trim($this->query)) >= 2) {
            $search = trim($this->query);

            // Check for search overrides first
            $overrides = SearchOverride::active()
                ->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(query) like ?', ['%'.mb_strtolower($search).'%']);
                })
                ->get();

            // Full-text search on searchable_content
            $titleField = "title_{$locale}";
            $contentField = "content_{$locale}";
            $urlField = "url_{$locale}";
            $searchLower = '%'.mb_strtolower($search).'%';

            $results = SearchableContent::query()
                ->where(function ($q) use ($titleField, $contentField, $searchLower) {
                    $q->whereRaw("LOWER({$titleField}) like ?", [$searchLower])
                        ->orWhereRaw("LOWER({$contentField}) like ?", [$searchLower]);
                })
                ->orderByDesc('priority')
                ->limit(20)
                ->get()
                ->map(function ($item) use ($titleField, $contentField, $urlField) {
                    return [
                        'title' => $item->{$titleField},
                        'excerpt' => \Illuminate\Support\Str::limit(strip_tags($item->{$contentField} ?? ''), 160),
                        'url' => $item->{$urlField},
                    ];
                });
        }

        return view('livewire.search-results', [
            'results' => $results,
            'overrides' => $overrides,
        ]);
    }
}
