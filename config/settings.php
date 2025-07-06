<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Settings Models
    |--------------------------------------------------------------------------
    |
    | When using custom models, you can define which Eloquent model should
    | be used to retrieve your settings. The default is usually fine.
    |
    */
    'models' => [
        'setting' => Cubecoding\Settings\Models\Setting::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Settings Table
    |--------------------------------------------------------------------------
    |
    | Define the table name used to store settings in the database.
    |
    */
    'table_names' => [
        'settings' => 'settings',
    ],

    /*
    |--------------------------------------------------------------------------
    | Settings Cache
    |--------------------------------------------------------------------------
    |
    | By default all settings are cached for 1 hour to speed up performance.
    | When settings are updated the cache is flushed automatically.
    |
    */
    'cache' => [
        'enabled' => env('SETTINGS_CACHE_ENABLED', true),
        'store' => env('SETTINGS_CACHE_STORE', 'default'),
        'key' => env('SETTINGS_CACHE_KEY', 'cubecoding-settings'),
        'ttl' => env('SETTINGS_CACHE_TTL', 3600),
    ],
];
