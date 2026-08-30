<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $row = DB::table('settings')->where('key', $key)->first();

            if (! $row) {
                return $default;
            }

            $value = $row->value;

            if (is_null($value)) {
                return $default;
            }

            $decoded = json_decode($value, true);

            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        });
    }
}

if (! function_exists('setting_set')) {
    function setting_set(string $key, mixed $value): void
    {
        $encoded = is_array($value) || is_object($value) ? json_encode($value) : $value;

        DB::table('settings')->updateOrInsert(
            ['key' => $key],
            ['value' => $encoded, 'updated_at' => now()]
        );

        Cache::forget("setting_{$key}");
    }
}

if (! function_exists('setting_group')) {
    function setting_group(string $group, array $defaults = []): array
    {
        $rows = DB::table('settings')->where('group', $group)->get();
        $results = [];

        foreach ($rows as $row) {
            $decoded = json_decode($row->value, true);
            $results[$row->key] = json_last_error() === JSON_ERROR_NONE ? $decoded : $row->value;
        }

        foreach ($defaults as $key => $default) {
            if (! array_key_exists($key, $results)) {
                $results[$key] = $default;
            }
        }

        return $results;
    }
}
