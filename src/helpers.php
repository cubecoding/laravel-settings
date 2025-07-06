<?php

if (! function_exists('settings')) {
    /**
     * Get / set settings values
     */
    function settings(string|array|null $key = null, mixed $default = null): mixed
    {
        $manager = app('cubecoding-settings');

        if (is_array($key)) {
            // Set multiple settings
            $manager->setMany($key);

            return null;
        }

        if (is_null($key)) {
            // Get all settings
            return $manager->all();
        }

        // Check if this is a "set" operation (2 parameters provided)
        if (func_num_args() >= 2 && ! is_null($default)) {
            // Set a single setting
            $manager->set($key, $default);

            return $default;
        }

        // Get a single setting
        return $manager->get($key, $default);
    }
}
