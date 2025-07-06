<?php

namespace Cubecoding\Settings\Tests\Feature;

use Cubecoding\Settings\Models\Setting;
use Cubecoding\Settings\SettingsManager;
use Cubecoding\Settings\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class SettingsManagerTest extends TestCase
{
    use RefreshDatabase;

    private SettingsManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new SettingsManager;
    }

    #[Test]
    public function it_can_set_and_get_a_setting()
    {
        $this->manager->set('test_key', 'test_value');

        $this->assertEquals('test_value', $this->manager->get('test_key'));
    }

    #[Test]
    public function it_returns_default_value_when_setting_not_found()
    {
        $this->assertEquals('default', $this->manager->get('non_existent', 'default'));
        $this->assertNull($this->manager->get('non_existent'));
    }

    #[Test]
    public function it_can_check_if_setting_exists()
    {
        $this->manager->set('existing_key', 'value');

        $this->assertTrue($this->manager->has('existing_key'));
        $this->assertFalse($this->manager->has('non_existent_key'));
    }

    #[Test]
    public function it_can_delete_a_setting()
    {
        $this->manager->set('deletable_key', 'value');
        $this->assertTrue($this->manager->has('deletable_key'));

        $result = $this->manager->forget('deletable_key');

        $this->assertTrue($result > 0); // Should return number of deleted records
        $this->assertFalse($this->manager->has('deletable_key'));
    }

    #[Test]
    public function it_returns_zero_when_deleting_non_existent_setting()
    {
        $result = $this->manager->forget('non_existent_key');

        $this->assertEquals(0, $result);
    }

    #[Test]
    public function it_can_get_all_settings()
    {
        $this->manager->set('key1', 'value1');
        $this->manager->set('key2', 'value2');
        $this->manager->set('key3', 123);

        $all = $this->manager->all();

        $this->assertIsArray($all);
        $this->assertCount(3, $all);
        $this->assertEquals('value1', $all['key1']);
        $this->assertEquals('value2', $all['key2']);
        $this->assertEquals(123, $all['key3']);
    }

    #[Test]
    public function it_returns_empty_array_when_no_settings_exist()
    {
        $all = $this->manager->all();

        $this->assertIsArray($all);
        $this->assertEmpty($all);
    }

    #[Test]
    public function it_can_get_multiple_settings()
    {
        $this->manager->set('key1', 'value1');
        $this->manager->set('key2', 'value2');
        $this->manager->set('key3', 'value3');

        $result = $this->manager->getMany(['key1', 'key2', 'key3', 'non_existent']);

        $expected = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
            'non_existent' => null,
        ];

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_can_get_multiple_settings_with_default_value()
    {
        $this->manager->set('existing_key', 'existing_value');

        $result = $this->manager->getMany(['existing_key', 'non_existent'], 'default');

        $expected = [
            'existing_key' => 'existing_value',
            'non_existent' => 'default',
        ];

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_can_set_multiple_settings()
    {
        $settings = [
            'bulk_key1' => 'bulk_value1',
            'bulk_key2' => 'bulk_value2',
            'bulk_key3' => 123,
        ];

        $this->manager->setMany($settings);

        $this->assertEquals('bulk_value1', $this->manager->get('bulk_key1'));
        $this->assertEquals('bulk_value2', $this->manager->get('bulk_key2'));
        $this->assertEquals(123, $this->manager->get('bulk_key3'));
    }

    #[Test]
    public function it_can_set_setting_with_description()
    {
        $this->manager->set('documented_key', 'value', 'This is a description');

        $setting = Setting::where('key', 'documented_key')->first();
        $this->assertEquals('value', $setting->value);
        $this->assertEquals('This is a description', $setting->description);
    }

    #[Test]
    public function it_updates_existing_setting()
    {
        $this->manager->set('updatable_key', 'original_value');
        $this->assertEquals('original_value', $this->manager->get('updatable_key'));

        $this->manager->set('updatable_key', 'updated_value', 'Updated description');
        $this->assertEquals('updated_value', $this->manager->get('updatable_key'));

        // Should only have one record in database
        $this->assertEquals(1, Setting::where('key', 'updatable_key')->count());

        // Check description was updated
        $setting = Setting::where('key', 'updatable_key')->first();
        $this->assertEquals('Updated description', $setting->description);
    }

    #[Test]
    public function it_preserves_data_types()
    {
        $this->manager->set('string_key', 'string_value');
        $this->manager->set('int_key', 42);
        $this->manager->set('float_key', 3.14);
        $this->manager->set('bool_key', true);
        $this->manager->set('array_key', ['item1', 'item2']);

        $this->assertIsString($this->manager->get('string_key'));
        $this->assertIsInt($this->manager->get('int_key'));
        $this->assertIsFloat($this->manager->get('float_key'));
        $this->assertIsBool($this->manager->get('bool_key'));
        $this->assertIsArray($this->manager->get('array_key'));

        $this->assertEquals('string_value', $this->manager->get('string_key'));
        $this->assertEquals(42, $this->manager->get('int_key'));
        $this->assertEquals(3.14, $this->manager->get('float_key'));
        $this->assertTrue($this->manager->get('bool_key'));
        $this->assertEquals(['item1', 'item2'], $this->manager->get('array_key'));
    }

    #[Test]
    public function it_handles_empty_keys_array_in_get_many()
    {
        $result = $this->manager->getMany([]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    #[Test]
    public function it_handles_empty_settings_array_in_set_many()
    {
        $this->manager->setMany([]);

        // Should not throw any errors and database should remain empty
        $this->assertEmpty($this->manager->all());
    }
}
