<?php

namespace Cubecoding\Settings\Tests\Feature;

use Cubecoding\Settings\Models\Setting;
use Cubecoding\Settings\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_set_and_get_a_string_setting()
    {
        \Settings::set('app_name', 'Test Application');

        $this->assertEquals('Test Application', \Settings::get('app_name'));
    }

    #[Test]
    public function it_can_set_and_get_an_integer_setting()
    {
        \Settings::set('max_users', 100);

        $this->assertEquals(100, \Settings::get('max_users'));
        $this->assertIsInt(\Settings::get('max_users'));
    }

    #[Test]
    public function it_can_set_and_get_a_boolean_setting()
    {
        \Settings::set('is_maintenance', true);
        \Settings::set('debug_mode', false);

        $this->assertTrue(\Settings::get('is_maintenance'));
        $this->assertFalse(\Settings::get('debug_mode'));
        $this->assertIsBool(\Settings::get('is_maintenance'));
        $this->assertIsBool(\Settings::get('debug_mode'));
    }

    #[Test]
    public function it_can_set_and_get_a_float_setting()
    {
        \Settings::set('price', 19.99);

        $this->assertEquals(19.99, \Settings::get('price'));
        $this->assertIsFloat(\Settings::get('price'));
    }

    #[Test]
    public function it_can_set_and_get_an_array_setting()
    {
        $features = ['feature1', 'feature2', 'feature3'];
        \Settings::set('features', $features);

        $this->assertEquals($features, \Settings::get('features'));
        $this->assertIsArray(\Settings::get('features'));
    }

    #[Test]
    public function it_can_set_and_get_a_json_setting()
    {
        $config = ['database' => 'mysql', 'cache' => 'redis'];
        \Settings::set('config', $config);

        $this->assertEquals($config, \Settings::get('config'));
        $this->assertIsArray(\Settings::get('config'));
    }

    #[Test]
    public function it_returns_default_value_when_setting_does_not_exist()
    {
        $this->assertEquals('default', \Settings::get('non_existent', 'default'));
        $this->assertNull(\Settings::get('non_existent'));
    }

    #[Test]
    public function it_can_check_if_setting_exists()
    {
        \Settings::set('existing_key', 'value');

        $this->assertTrue(\Settings::has('existing_key'));
        $this->assertFalse(\Settings::has('non_existent_key'));
    }

    #[Test]
    public function it_can_delete_a_setting()
    {
        \Settings::set('temp_setting', 'temporary');
        $this->assertTrue(\Settings::has('temp_setting'));

        \Settings::forget('temp_setting');
        $this->assertFalse(\Settings::has('temp_setting'));
    }

    #[Test]
    public function it_can_get_all_settings()
    {
        \Settings::set('key1', 'value1');
        \Settings::set('key2', 'value2');
        \Settings::set('key3', 123);

        $all = \Settings::all();

        $this->assertIsArray($all);
        $this->assertEquals('value1', $all['key1']);
        $this->assertEquals('value2', $all['key2']);
        $this->assertEquals(123, $all['key3']);
    }

    #[Test]
    public function it_can_set_multiple_settings_at_once()
    {
        $settings = [
            'app_name' => 'Bulk App',
            'version' => '2.0.0',
            'debug' => true,
        ];

        \Settings::setMany($settings);

        $this->assertEquals('Bulk App', \Settings::get('app_name'));
        $this->assertEquals('2.0.0', \Settings::get('version'));
        $this->assertTrue(\Settings::get('debug'));
    }

    #[Test]
    public function it_can_get_multiple_settings_at_once()
    {
        \Settings::set('key1', 'value1');
        \Settings::set('key2', 'value2');
        \Settings::set('key3', 'value3');

        $result = \Settings::getMany(['key1', 'key2', 'key3', 'non_existent']);

        $this->assertEquals([
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
            'non_existent' => null,
        ], $result);
    }

    #[Test]
    public function it_can_get_multiple_settings_with_default_value()
    {
        \Settings::set('existing_key', 'existing_value');

        $result = \Settings::getMany(['existing_key', 'non_existent'], 'default');

        $this->assertEquals([
            'existing_key' => 'existing_value',
            'non_existent' => 'default',
        ], $result);
    }

    #[Test]
    public function it_can_set_setting_with_description()
    {
        \Settings::set('api_key', 'secret-key', 'API key for external services');

        $setting = Setting::where('key', 'api_key')->first();
        $this->assertEquals('secret-key', $setting->value);
        $this->assertEquals('API key for external services', $setting->description);
    }

    #[Test]
    public function it_updates_existing_setting()
    {
        \Settings::set('updatable_key', 'original_value');
        $this->assertEquals('original_value', \Settings::get('updatable_key'));

        \Settings::set('updatable_key', 'updated_value');
        $this->assertEquals('updated_value', \Settings::get('updatable_key'));

        // Should only have one record in database
        $this->assertEquals(1, Setting::where('key', 'updatable_key')->count());
    }

    #[Test]
    public function it_supports_dot_notation_for_nested_settings()
    {
        \Settings::set('app.name', 'My Application');
        \Settings::set('app.version', '1.0.0');
        \Settings::set('database.host', 'localhost');
        \Settings::set('database.port', 3306);

        $this->assertEquals('My Application', \Settings::get('app.name'));
        $this->assertEquals('1.0.0', \Settings::get('app.version'));
        $this->assertEquals('localhost', \Settings::get('database.host'));
        $this->assertEquals(3306, \Settings::get('database.port'));
        $this->assertIsInt(\Settings::get('database.port'));
    }

    #[Test]
    public function it_can_get_nested_settings_as_array()
    {
        \Settings::set('app.name', 'My Application');
        \Settings::set('app.version', '1.0.0');
        \Settings::set('app.debug', true);

        $appSettings = \Settings::get('app');

        $this->assertIsArray($appSettings);
        $this->assertEquals('My Application', $appSettings['name']);
        $this->assertEquals('1.0.0', $appSettings['version']);
        $this->assertTrue($appSettings['debug']);
    }

    #[Test]
    public function it_can_set_nested_array_values()
    {
        $config = [
            'database' => [
                'host' => 'localhost',
                'port' => 3306,
                'name' => 'myapp',
            ],
            'cache' => [
                'driver' => 'redis',
                'ttl' => 3600,
            ],
        ];

        \Settings::set('config', $config);

        $this->assertEquals('localhost', \Settings::get('config.database.host'));
        $this->assertEquals(3306, \Settings::get('config.database.port'));
        $this->assertEquals('myapp', \Settings::get('config.database.name'));
        $this->assertEquals('redis', \Settings::get('config.cache.driver'));
        $this->assertEquals(3600, \Settings::get('config.cache.ttl'));
    }

    #[Test]
    public function it_can_check_nested_settings_existence()
    {
        \Settings::set('app.name', 'Test App');
        \Settings::set('app.features.auth', true);

        $this->assertTrue(\Settings::has('app.name'));
        $this->assertTrue(\Settings::has('app.features.auth'));
        $this->assertTrue(\Settings::has('app'));
        $this->assertTrue(\Settings::has('app.features'));
        $this->assertFalse(\Settings::has('app.nonexistent'));
    }

    #[Test]
    public function it_can_delete_nested_settings()
    {
        \Settings::set('temp.setting1', 'value1');
        \Settings::set('temp.setting2', 'value2');
        \Settings::set('temp.nested.deep', 'deep_value');

        $this->assertTrue(\Settings::has('temp.setting1'));
        $this->assertTrue(\Settings::has('temp.nested.deep'));

        // Delete specific nested setting
        \Settings::forget('temp.setting1');
        $this->assertFalse(\Settings::has('temp.setting1'));
        $this->assertTrue(\Settings::has('temp.setting2'));

        // Delete parent should remove all children
        \Settings::forget('temp');
        $this->assertFalse(\Settings::has('temp.setting2'));
        $this->assertFalse(\Settings::has('temp.nested.deep'));
    }

    #[Test]
    public function it_can_flush_cache()
    {
        \Settings::set('cached_setting', 'cached_value');
        $this->assertEquals('cached_value', \Settings::get('cached_setting'));

        // Flush cache
        \Settings::flushCache();

        // Setting should still be available (loaded from database)
        $this->assertEquals('cached_value', \Settings::get('cached_setting'));
    }

    #[Test]
    public function it_can_get_all_settings_without_key()
    {
        \Settings::set('key1', 'value1');
        \Settings::set('nested.key', 'nested_value');

        $all = \Settings::get();

        $this->assertIsArray($all);
        $this->assertEquals('value1', $all['key1']);
        $this->assertEquals('nested_value', $all['nested']['key']);
    }

    #[Test]
    public function it_handles_array_input_in_set_method()
    {
        $settings = [
            'bulk.setting1' => 'value1',
            'bulk.setting2' => 'value2',
            'simple' => 'simple_value',
        ];

        \Settings::set($settings);

        $this->assertEquals('value1', \Settings::get('bulk.setting1'));
        $this->assertEquals('value2', \Settings::get('bulk.setting2'));
        $this->assertEquals('simple_value', \Settings::get('simple'));
    }

    #[Test]
    public function it_returns_default_for_nested_non_existent_keys()
    {
        \Settings::set('app.name', 'Test App');

        $this->assertEquals('default', \Settings::get('app.nonexistent', 'default'));
        $this->assertEquals('default', \Settings::get('nonexistent.nested.key', 'default'));
        $this->assertNull(\Settings::get('app.nonexistent'));
    }
}
