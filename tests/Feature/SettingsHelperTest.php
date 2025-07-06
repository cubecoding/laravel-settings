<?php

namespace Cubecoding\Settings\Tests\Feature;

use Cubecoding\Settings\Facades\Settings;
use Cubecoding\Settings\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class SettingsHelperTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_get_a_setting_using_helper_function()
    {
        // Arrange - set a setting via facade first
        Settings::set('app.name', 'Test App');

        // Act - use helper function
        $result = settings('app.name');

        // Assert
        $this->assertEquals('Test App', $result);
    }

    #[Test]
    public function it_can_get_a_setting_with_default_value_using_helper()
    {
        // Act
        $result = settings('non.existent.key', 'default value');

        // Assert
        $this->assertEquals('default value', $result);
    }

    #[Test]
    public function it_can_get_all_settings_using_helper_without_parameters()
    {
        // Arrange
        Settings::set('key1', 'value1');
        Settings::set('key2', 'value2');

        // Act
        $result = settings();

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals('value1', $result['key1']);
        $this->assertEquals('value2', $result['key2']);
    }

    #[Test]
    public function it_can_set_multiple_settings_using_helper_with_array()
    {
        // Act
        settings([
            'app.name' => 'Helper App',
            'app.version' => '2.0.0',
            'debug' => true,
        ]);

        // Assert - verify via facade
        $this->assertEquals('Helper App', Settings::get('app.name'));
        $this->assertEquals('2.0.0', Settings::get('app.version'));
        $this->assertTrue(Settings::get('debug'));
    }

    #[Test]
    public function it_can_set_single_setting_using_helper_with_second_parameter()
    {
        // Act
        $result = settings('new.setting', 'new value');

        // Assert - this should work like config() where second param means set
        $this->assertEquals('new value', Settings::get('new.setting'));
    }

    #[Test]
    public function it_supports_dot_notation_in_helper()
    {
        // Act
        settings('app.database.host', 'localhost');

        // Assert
        $this->assertEquals('localhost', settings('app.database.host'));
        $this->assertEquals('localhost', Settings::get('app.database.host'));
    }

    #[Test]
    public function it_preserves_data_types_through_helper()
    {
        // Arrange
        settings([
            'string_val' => 'text',
            'int_val' => 42,
            'float_val' => 3.14,
            'bool_val' => true,
        ]);

        // Assert
        $this->assertIsString(settings('string_val'));
        $this->assertIsInt(settings('int_val'));
        $this->assertIsFloat(settings('float_val'));
        $this->assertIsBool(settings('bool_val'));
    }
}
