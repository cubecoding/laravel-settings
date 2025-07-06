<?php

namespace Cubecoding\Settings\Tests\Feature;

use Cubecoding\Settings\Models\Setting;
use Cubecoding\Settings\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class SettingModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function it_properly_casts_integer_values_from_database()
    {
        Setting::create(['key' => 'int_value', 'value' => '123', 'type' => 'integer']);

        $value = Setting::where('key', 'int_value')->first()->value;

        $this->assertEquals(123, $value);
        $this->assertIsInt($value);
    }

    #[Test]
    public function it_properly_casts_float_values_from_database()
    {
        Setting::create(['key' => 'float_value', 'value' => '3.14159', 'type' => 'float']);

        $value = Setting::where('key', 'float_value')->first()->value;

        $this->assertEquals(3.14159, $value);
        $this->assertIsFloat($value);
    }

    #[Test]
    public function it_can_store_description()
    {
        $setting = Setting::create([
            'key' => 'documented_setting',
            'value' => 'some value',
            'description' => 'This is a documented setting',
        ]);

        $this->assertEquals('This is a documented setting', $setting->description);
    }

    #[Test]
    public function it_has_timestamps()
    {
        $setting = Setting::create([
            'key' => 'timestamped_setting',
            'value' => 'value',
        ]);

        $this->assertNotNull($setting->created_at);
        $this->assertNotNull($setting->updated_at);
    }

    #[Test]
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
