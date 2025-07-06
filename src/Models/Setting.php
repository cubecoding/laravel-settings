<?php

namespace Cubecoding\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Only set table from config if config is available (not in Unit tests)
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
        switch ($this->type) {
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'array':
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Set the value attribute with proper type handling
     */
    public function setValueAttribute(mixed $value): void
    {
        if (is_array($value) || is_object($value)) {
            $this->attributes['value'] = json_encode($value);
            $this->attributes['type'] = 'json';
        } elseif (is_bool($value)) {
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
