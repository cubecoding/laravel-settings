<?php

namespace Cubecoding\Settings\Tests\Unit;

use Cubecoding\Settings\Models\Setting;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SettingModelTest extends TestCase
{
    #[Test]
    public function it_has_correct_fillable_attributes()
    {
        $setting = new Setting;
        $expected = ['key', 'value', 'type', 'description'];

        $this->assertEquals($expected, $setting->getFillable());
    }

    #[Test]
    public function it_has_correct_table_name()
    {
        $setting = new Setting;

        $this->assertEquals('settings', $setting->getTable());
    }

    #[Test]
    public function it_casts_string_values_correctly()
    {
        $setting = new Setting;
        $setting->type = 'string';
        $setting->setRawAttributes(['value' => 'test string', 'type' => 'string']);

        $this->assertEquals('test string', $setting->getValueAttribute('test string'));
        $this->assertIsString($setting->getValueAttribute('test string'));
    }

    #[Test]
    public function it_casts_boolean_values_correctly()
    {
        $setting = new Setting;
        $setting->type = 'boolean';

        $this->assertTrue($setting->getValueAttribute('1'));
        $this->assertFalse($setting->getValueAttribute('0'));
        $this->assertIsBool($setting->getValueAttribute('1'));
        $this->assertIsBool($setting->getValueAttribute('0'));
    }

    #[Test]
    public function it_casts_integer_values_correctly()
    {
        $setting = new Setting;
        $setting->type = 'integer';

        $result = $setting->getValueAttribute('42');
        $this->assertEquals(42, $result);
        $this->assertIsInt($result);
    }

    #[Test]
    public function it_casts_float_values_correctly()
    {
        $setting = new Setting;
        $setting->type = 'float';

        $result = $setting->getValueAttribute('3.14');
        $this->assertEquals(3.14, $result);
        $this->assertIsFloat($result);
    }

    #[Test]
    public function it_sets_string_values_correctly()
    {
        $setting = new Setting;
        $setting->setValueAttribute('test string');

        $this->assertEquals('test string', $setting->getAttributes()['value']);
        $this->assertEquals('string', $setting->getAttributes()['type']);
    }

    #[Test]
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

    #[Test]
    public function it_sets_integer_values_correctly()
    {
        $setting = new Setting;
        $setting->setValueAttribute(42);

        $this->assertEquals('42', $setting->getAttributes()['value']);
        $this->assertEquals('integer', $setting->getAttributes()['type']);
    }

    #[Test]
    public function it_sets_float_values_correctly()
    {
        $setting = new Setting;
        $setting->setValueAttribute(3.14);

        $this->assertEquals('3.14', $setting->getAttributes()['value']);
        $this->assertEquals('float', $setting->getAttributes()['type']);
    }

    #[Test]
    public function it_handles_null_values_as_string()
    {
        $setting = new Setting;
        $setting->setValueAttribute(null);

        $this->assertEquals('', $setting->getAttributes()['value']);
        $this->assertEquals('string', $setting->getAttributes()['type']);
    }

    #[Test]
    public function it_handles_zero_as_integer()
    {
        $setting = new Setting;
        $setting->setValueAttribute(0);

        $this->assertEquals('0', $setting->getAttributes()['value']);
        $this->assertEquals('integer', $setting->getAttributes()['type']);
    }

    #[Test]
    public function it_returns_original_value_for_unknown_type()
    {
        $setting = new Setting;
        $setting->type = 'unknown_type';

        $result = $setting->getValueAttribute('test value');
        $this->assertEquals('test value', $result);
    }
}
