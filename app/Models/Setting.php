<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['key', 'value', 'type'];

    /**
     * Get a setting value by key, cached for 1 hour.
     * Returns $default if the key does not exist or the table is not yet migrated.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    /**
     * Set a setting value and clear its cache.
     */
    public static function set(string $key, mixed $value, string $type = 'string'): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'type' => $type]);
        Cache::forget("setting.{$key}");
    }
}
