<?php

namespace Cubecoding\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Only set table from config if config is available
        if (function_exists('config') && app()->bound('config')) {
            $this->table = config('settings.table_names.settings', 'settings');
        } else {
            $this->table = 'settings';
        }
    }

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    /**
     * Get the value attribute with proper type casting
     */
    public function getValueAttribute(?string $value): mixed
    {
        return match ($this->type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'float' => (float) $value,
            default => $value,
        };
    }

    /**
     * Set the value attribute with proper type handling
     */
    public function setValueAttribute(mixed $value): void
    {
        if (is_bool($value)) {
            $this->attributes['value'] = $value ? '1' : '0';
            $this->attributes['type'] = 'boolean';
        } elseif (is_int($value)) {
            $this->attributes['value'] = (string) $value;
            $this->attributes['type'] = 'integer';
        } elseif (is_float($value)) {
            $this->attributes['value'] = (string) $value;
            $this->attributes['type'] = 'float';
        } else {
            $this->attributes['value'] = (string) $value;
            $this->attributes['type'] = 'string';
        }
    }
}
