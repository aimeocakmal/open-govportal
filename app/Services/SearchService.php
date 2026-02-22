<?php

namespace App\Services;

use App\Models\SearchableContent;
use App\Models\SearchOverride;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SearchService
{
    /**
     * Search content using PostgreSQL FTS with SearchOverride priority.
     *
     * @return Collection<int, object{title: string, content: string, url: string, type: string, priority: int}>
     */
    public function search(string $query, string $locale = 'ms', int $limit = 20): Collection
    {
        $query = trim($query);
        if ($query === '') {
            return collect();
        }

        $results = collect();

        // 1. Check SearchOverride for exact keyword matches
        $overrides = SearchOverride::active()
            ->whereRaw('LOWER(query) = ?', [strtolower($query)])
            ->get()
            ->map(fn (SearchOverride $o) => (object) [
                'title' => $locale === 'en' ? ($o->title_en ?? $o->title_ms) : $o->title_ms,
                'content' => $locale === 'en' ? ($o->description_en ?? $o->description_ms) : $o->description_ms,
                'url' => $o->url,
                'type' => 'override',
                'priority' => -1, // Always first
            ]);

        $results = $results->merge($overrides);

        // 2. FTS search on searchable_content
        $titleCol = "title_{$locale}";
        $contentCol = "content_{$locale}";
        $urlCol = "url_{$locale}";

        if (DB::getDriverName() === 'pgsql') {
            $tsConfig = $locale === 'en' ? 'english' : 'simple';
            $tsvectorCol = "tsvector_{$locale}";

            $ftsResults = SearchableContent::query()
                ->whereRaw("{$tsvectorCol} @@ plainto_tsquery(?, ?)", [$tsConfig, $query])
                ->orderBy('priority')
                ->orderByRaw("ts_rank({$tsvectorCol}, plainto_tsquery(?, ?)) DESC", [$tsConfig, $query])
                ->limit($limit)
                ->get();
        } else {
            // SQLite fallback: LIKE search
            $likeQuery = '%'.$query.'%';
            $ftsResults = SearchableContent::query()
                ->where(function ($q) use ($titleCol, $contentCol, $likeQuery) {
                    $q->where($titleCol, 'LIKE', $likeQuery)
                        ->orWhere($contentCol, 'LIKE', $likeQuery);
                })
                ->orderBy('priority')
                ->limit($limit)
                ->get();
        }

        $mapped = $ftsResults->map(fn (SearchableContent $r) => (object) [
            'title' => $r->{$titleCol} ?? '',
            'content' => Str::limit(strip_tags($r->{$contentCol} ?? ''), 200),
            'url' => $r->{$urlCol} ?? '',
            'type' => class_basename($r->searchable_type),
            'priority' => $r->priority,
        ]);

        return $results->merge($mapped)->take($limit);
    }
}
