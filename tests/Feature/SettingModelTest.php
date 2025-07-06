<?php

namespace Cubecoding\Settings\Tests\Feature;

use Cubecoding\Settings\Models\Setting;
use Cubecoding\Settings\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SettingModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_setting_with_string_value()
    {
        $setting = Setting::create([
            'key' => 'test_string',
            'value' => 'test value',
        ]);

        $this->assertEquals('test_string', $setting->key);
        $this->assertEquals('test value', $setting->value);
        $this->assertEquals('string', $setting->type);
    }

    /** @test */
    public function it_automatically_sets_type_for_integer_values()
    {
        $setting = Setting::create([
            'key' => 'test_integer',
            'value' => 42,
        ]);

        $this->assertEquals(42, $setting->value);
        $this->assertEquals('integer', $setting->type);
        $this->assertIsInt($setting->value);
    }

    /** @test */
    public function it_automatically_sets_type_for_float_values()
    {
        $setting = Setting::create([
            'key' => 'test_float',
            'value' => 3.14,
        ]);

        $this->assertEquals(3.14, $setting->value);
        $this->assertEquals('float', $setting->type);
        $this->assertIsFloat($setting->value);
    }

    /** @test */
    public function it_automatically_sets_type_for_boolean_values()
    {
        $trueSetting = Setting::create([
            'key' => 'test_boolean_true',
            'value' => true,
        ]);

        $falseSetting = Setting::create([
            'key' => 'test_boolean_false',
            'value' => false,
        ]);

        $this->assertTrue($trueSetting->value);
        $this->assertEquals('boolean', $trueSetting->type);
        $this->assertIsBool($trueSetting->value);

        $this->assertFalse($falseSetting->value);
        $this->assertEquals('boolean', $falseSetting->type);
        $this->assertIsBool($falseSetting->value);
    }

    /** @test */
    public function it_automatically_sets_type_for_array_values()
    {
        $setting = Setting::create([
            'key' => 'test_array',
            'value' => ['item1', 'item2', 'item3'],
        ]);

        $this->assertEquals(['item1', 'item2', 'item3'], $setting->value);
        $this->assertEquals('json', $setting->type);
        $this->assertIsArray($setting->value);
    }

    /** @test */
    public function it_automatically_sets_type_for_object_values()
    {
        $object = (object) ['key' => 'value', 'number' => 123];
        $setting = Setting::create([
            'key' => 'test_object',
            'value' => $object,
        ]);

        $this->assertEquals(['key' => 'value', 'number' => 123], $setting->value);
        $this->assertEquals('json', $setting->type);
        $this->assertIsArray($setting->value);
    }

    /** @test */
    public function it_properly_casts_boolean_values_from_database()
    {
        // Create setting directly in database to test casting
        Setting::create(['key' => 'bool_true', 'value' => '1', 'type' => 'boolean']);
        Setting::create(['key' => 'bool_false', 'value' => '0', 'type' => 'boolean']);

        $trueValue = Setting::where('key', 'bool_true')->first()->value;
        $falseValue = Setting::where('key', 'bool_false')->first()->value;

        $this->assertTrue($trueValue);
        $this->assertFalse($falseValue);
        $this->assertIsBool($trueValue);
        $this->assertIsBool($falseValue);
    }

    /** @test */
    public function it_properly_casts_integer_values_from_database()
    {
        Setting::create(['key' => 'int_value', 'value' => '123', 'type' => 'integer']);

        $value = Setting::where('key', 'int_value')->first()->value;

        $this->assertEquals(123, $value);
        $this->assertIsInt($value);
    }

    /** @test */
    public function it_properly_casts_float_values_from_database()
    {
        Setting::create(['key' => 'float_value', 'value' => '3.14159', 'type' => 'float']);

        $value = Setting::where('key', 'float_value')->first()->value;

        $this->assertEquals(3.14159, $value);
        $this->assertIsFloat($value);
    }

    /** @test */
    public function it_properly_casts_json_values_from_database()
    {
        $jsonData = json_encode(['key1' => 'value1', 'key2' => 'value2']);
        Setting::create(['key' => 'json_value', 'value' => $jsonData, 'type' => 'json']);

        $value = Setting::where('key', 'json_value')->first()->value;

        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $value);
        $this->assertIsArray($value);
    }

    /** @test */
    public function it_can_store_description()
    {
        $setting = Setting::create([
            'key' => 'documented_setting',
            'value' => 'some value',
            'description' => 'This is a documented setting',
        ]);

        $this->assertEquals('This is a documented setting', $setting->description);
    }

    /** @test */
    public function it_has_timestamps()
    {
        $setting = Setting::create([
            'key' => 'timestamped_setting',
            'value' => 'value',
        ]);

        $this->assertNotNull($setting->created_at);
        $this->assertNotNull($setting->updated_at);
    }

    /** @test */
    public function it_enforces_unique_keys()
    {
        Setting::create([
            'key' => 'unique_key',
            'value' => 'first value',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Setting::create([
            'key' => 'unique_key',
            'value' => 'second value',
        ]);
    }
}
