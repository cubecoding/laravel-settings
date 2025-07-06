<?php

namespace Cubecoding\Settings\Tests\Unit;

use Cubecoding\Settings\Models\Setting;
use PHPUnit\Framework\TestCase;

class SettingModelTest extends TestCase
{
    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $setting = new Setting;
        $expected = ['key', 'value', 'type', 'description'];

        $this->assertEquals($expected, $setting->getFillable());
    }

    /** @test */
    public function it_has_correct_table_name()
    {
        $setting = new Setting;

        $this->assertEquals('settings', $setting->getTable());
    }

    /** @test */
    public function it_casts_string_values_correctly()
    {
        $setting = new Setting;
        $setting->type = 'string';
        $setting->setRawAttributes(['value' => 'test string', 'type' => 'string']);

        $this->assertEquals('test string', $setting->getValueAttribute('test string'));
        $this->assertIsString($setting->getValueAttribute('test string'));
    }

    /** @test */
    public function it_casts_boolean_values_correctly()
    {
        $setting = new Setting;
        $setting->type = 'boolean';

        $this->assertTrue($setting->getValueAttribute('1'));
        $this->assertFalse($setting->getValueAttribute('0'));
        $this->assertIsBool($setting->getValueAttribute('1'));
        $this->assertIsBool($setting->getValueAttribute('0'));
    }

    /** @test */
    public function it_casts_integer_values_correctly()
    {
        $setting = new Setting;
        $setting->type = 'integer';

        $result = $setting->getValueAttribute('42');
        $this->assertEquals(42, $result);
        $this->assertIsInt($result);
    }

    /** @test */
    public function it_casts_float_values_correctly()
    {
        $setting = new Setting;
        $setting->type = 'float';

        $result = $setting->getValueAttribute('3.14');
        $this->assertEquals(3.14, $result);
        $this->assertIsFloat($result);
    }

    /** @test */
    public function it_casts_json_values_correctly()
    {
        $setting = new Setting;
        $setting->type = 'json';

        $jsonString = '{"key":"value","number":123}';
        $result = $setting->getValueAttribute($jsonString);

        $this->assertEquals(['key' => 'value', 'number' => 123], $result);
        $this->assertIsArray($result);
    }

    /** @test */
    public function it_casts_array_values_correctly()
    {
        $setting = new Setting;
        $setting->type = 'array';

        $jsonString = '["item1","item2","item3"]';
        $result = $setting->getValueAttribute($jsonString);

        $this->assertEquals(['item1', 'item2', 'item3'], $result);
        $this->assertIsArray($result);
    }

    /** @test */
    public function it_sets_string_values_correctly()
    {
        $setting = new Setting;
        $setting->setValueAttribute('test string');

        $this->assertEquals('test string', $setting->getAttributes()['value']);
        $this->assertEquals('string', $setting->getAttributes()['type']);
    }

    /** @test */
    public function it_sets_boolean_values_correctly()
    {
        $setting = new Setting;

        $setting->setValueAttribute(true);
        $this->assertEquals('1', $setting->getAttributes()['value']);
        $this->assertEquals('boolean', $setting->getAttributes()['type']);

        $setting->setValueAttribute(false);
        $this->assertEquals('0', $setting->getAttributes()['value']);
        $this->assertEquals('boolean', $setting->getAttributes()['type']);
    }

    /** @test */
    public function it_sets_integer_values_correctly()
    {
        $setting = new Setting;
        $setting->setValueAttribute(42);

        $this->assertEquals('42', $setting->getAttributes()['value']);
        $this->assertEquals('integer', $setting->getAttributes()['type']);
    }

    /** @test */
    public function it_sets_float_values_correctly()
    {
        $setting = new Setting;
        $setting->setValueAttribute(3.14);

        $this->assertEquals('3.14', $setting->getAttributes()['value']);
        $this->assertEquals('float', $setting->getAttributes()['type']);
    }

    /** @test */
    public function it_sets_array_values_correctly()
    {
        $setting = new Setting;
        $array = ['item1', 'item2', 'item3'];
        $setting->setValueAttribute($array);

        $this->assertEquals(json_encode($array), $setting->getAttributes()['value']);
        $this->assertEquals('json', $setting->getAttributes()['type']);
    }

    /** @test */
    public function it_sets_object_values_correctly()
    {
        $setting = new Setting;
        $object = (object) ['key' => 'value', 'number' => 123];
        $setting->setValueAttribute($object);

        $this->assertEquals(json_encode($object), $setting->getAttributes()['value']);
        $this->assertEquals('json', $setting->getAttributes()['type']);
    }

    /** @test */
    public function it_handles_null_values_as_string()
    {
        $setting = new Setting;
        $setting->setValueAttribute(null);

        $this->assertEquals('', $setting->getAttributes()['value']);
        $this->assertEquals('string', $setting->getAttributes()['type']);
    }

    /** @test */
    public function it_handles_zero_as_integer()
    {
        $setting = new Setting;
        $setting->setValueAttribute(0);

        $this->assertEquals('0', $setting->getAttributes()['value']);
        $this->assertEquals('integer', $setting->getAttributes()['type']);
    }

    /** @test */
    public function it_handles_empty_array()
    {
        $setting = new Setting;
        $setting->setValueAttribute([]);

        $this->assertEquals('[]', $setting->getAttributes()['value']);
        $this->assertEquals('json', $setting->getAttributes()['type']);
    }

    /** @test */
    public function it_returns_original_value_for_unknown_type()
    {
        $setting = new Setting;
        $setting->type = 'unknown_type';

        $result = $setting->getValueAttribute('test value');
        $this->assertEquals('test value', $result);
    }
}
