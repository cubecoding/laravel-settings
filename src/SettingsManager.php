<?php

namespace Cubecoding\Settings;

use Cubecoding\Settings\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SettingsManager
{
    protected array $settings = [];

    /**
     * Boot the settings service and load settings from cache or database
     */
    public function boot(): void
    {
        try {
            // Check if caching is enabled
            if (! config('settings.cache.enabled', true)) {
                $this->loadSettingsFromDatabase();

                return;
            }

            $cacheStore = config('settings.cache.store', 'default');
            $cacheKey = config('settings.cache.key', 'cubecoding-settings');
            $cacheTtl = config('settings.cache.ttl', 3600);

            $cache = $cacheStore === 'default' ? Cache::getFacadeRoot() : Cache::store($cacheStore);

            $this->settings = $cache->remember($cacheKey, $cacheTtl, function () {
                return $this->loadSettingsFromDatabase();
            });
        } catch (\Exception $e) {
            // If something goes wrong, start with empty settings
            $this->settings = [];
        }
    }

    /**
     * Load settings from database
     */
    protected function loadSettingsFromDatabase(): array
    {
        $tableName = config('settings.table_names.settings', 'settings');

        // Check if the settings table exists
        if (! Schema::hasTable($tableName)) {
            return [];
        }

        // Get all settings from the database using configured model
        $settingModel = config('settings.models.setting', Setting::class);
        $dbSettings = $settingModel::all();

        // Initialize the result array
        $result = [];

        // Process each setting to build a nested structure
        foreach ($dbSettings as $setting) {
            // The value is automatically cast by the model accessor
            $value = $setting->value;

            // Add to a result array using dot notation
            Arr::set($result, $setting->key, $value);
        }

        return $result;
    }

    /**
     * Get a setting value by key with dot notation support
     */
    public function get(?string $key = null, mixed $default = null): mixed
    {
        // Ensure settings are loaded
        if (empty($this->settings)) {
            $this->boot();
        }

        if (is_null($key)) {
            return $this->settings;
        }

        return Arr::get($this->settings, $key, $default);
    }

    /**
     * Set a setting value or multiple values with dot notation support
     */
    public function set(string|array $key, mixed $value = null, ?string $description = null): bool
    {
        if (is_array($key)) {
            // If key is an array, we can use Arr::dot to flatten it
            foreach (Arr::dot($key) as $k => $v) {
                $this->store($k, $v);
            }
        } else {
            if (is_array($value)) {
                // If value is an array, flatten it with the key as prefix
                $prefix = $key ? $key.'.' : '';
                foreach (Arr::dot($value) as $k => $v) {
                    $this->store($prefix.$k, $v, $description);
                }
            } else {
                // Simple key-value pair
                $this->store($key, $value, $description);
            }
        }

        // Update the cached settings
        $this->flushCache();
        $this->boot();

        return true;
    }

    /**
     * Check if a setting exists with dot notation support
     */
    public function has(string $key): bool
    {
        // Ensure settings are loaded
        if (empty($this->settings)) {
            $this->boot();
        }

        return Arr::has($this->settings, $key);
    }

    /**
     * Delete a setting with dot notation support
     */
    public function forget(string $key): int
    {
        // Remove from the memory structure
        Arr::forget($this->settings, $key);

        // Remove from the database - we need to delete exactly the key
        $deleted = Setting::where('key', $key)->delete();

        // If it's a parent key, remove all children too
        if (! str_ends_with($key, '.')) {
            $deleted += Setting::where('key', 'like', $key.'.%')->delete();
        }

        // Update cache
        $this->flushCache();
        $this->boot();

        return $deleted;
    }

    /**
     * Get all settings as key-value pairs
     */
    public function all(): array
    {
        // Ensure settings are loaded
        if (empty($this->settings)) {
            $this->boot();
        }

        return $this->settings;
    }

    /**
     * Get multiple settings by keys with dot notation support
     */
    public function getMany(array $keys, mixed $default = null): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * Set multiple settings at once
     */
    public function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Flush all settings from cache
     */
    public function flushCache(): void
    {
        if (config('settings.cache.enabled', true)) {
            $cacheStore = config('settings.cache.store', 'default');
            $cacheKey = config('settings.cache.key', 'cubecoding-settings');

            $cache = $cacheStore === 'default' ? Cache::getFacadeRoot() : Cache::store($cacheStore);
            $cache->forget($cacheKey);
        }

        $this->settings = [];
    }

    /**
     * Store a setting value in the database
     */
    protected function store(string $key, mixed $value, ?string $description = null): void
    {
        // Update the in-memory settings
        Arr::set($this->settings, $key, $value);

        // Store in the database as a flat key
        $setting = Setting::firstOrNew(['key' => $key]);
        $setting->value = $value;
        if ($description !== null) {
            $setting->description = $description;
        }
        $setting->save();
    }

    /**
     * Convert a string value from the database to the appropriate PHP type
     */
    protected function castValueFromString(string $value, string $type = 'string'): mixed
    {
        return match ($type) {
            'boolean' => $value === '1' || $value === 'true',
            'integer' => (int) $value,
            'float' => (float) $value,
            default => $value,
        };
    }
}
