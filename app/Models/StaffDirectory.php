<?php

namespace App\Models;

use App\Models\Concerns\HasSearchableContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StaffDirectory extends Model
{
    /** @use HasFactory<\Database\Factories\StaffDirectoryFactory> */
    use HasFactory;

    use HasSearchableContent;
    use LogsActivity;

    protected $fillable = [
        'name',
        'position_ms',
        'position_en',
        'department_ms',
        'department_en',
        'division_ms',
        'division_en',
        'email',
        'phone',
        'fax',
        'photo',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function toSearchableContent(): array
    {
        return [
            'title_ms' => $this->name,
            'title_en' => $this->name,
            'content_ms' => ($this->position_ms ?? '').' '.($this->department_ms ?? ''),
            'content_en' => ($this->position_en ?? '').' '.($this->department_en ?? ''),
            'url_ms' => '/ms/direktori',
            'url_en' => '/en/direktori',
            'priority' => 30,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'position_ms', 'department_ms', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
