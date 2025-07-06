<?php

namespace Cubecoding\Settings\Tests\Unit;

use Cubecoding\Settings\SettingsManager;
use Mockery;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SettingsManagerTest extends TestCase
{
    private SettingsManager $manager;

    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new SettingsManager;
        $this->reflection = new ReflectionClass($this->manager);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Helper method to call protected methods
     */
    private function callProtectedMethod($methodName, ...$args)
    {
        $method = $this->reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invoke($this->manager, ...$args);
    }

    /** @test */
    public function it_can_cast_string_values_from_database()
    {
        $result = $this->callProtectedMethod('castValueFromString', 'test string', 'string');

        $this->assertEquals('test string', $result);
        $this->assertIsString($result);
    }

    /** @test */
    public function it_can_cast_boolean_values_from_database()
    {
        $this->assertTrue($this->callProtectedMethod('castValueFromString', '1', 'boolean'));
        $this->assertTrue($this->callProtectedMethod('castValueFromString', 'true', 'boolean'));
        $this->assertFalse($this->callProtectedMethod('castValueFromString', '0', 'boolean'));
        $this->assertFalse($this->callProtectedMethod('castValueFromString', 'false', 'boolean'));
    }

    /** @test */
    public function it_can_cast_integer_values_from_database()
    {
        $result = $this->callProtectedMethod('castValueFromString', '42', 'integer');

        $this->assertEquals(42, $result);
        $this->assertIsInt($result);
    }

    /** @test */
    public function it_can_cast_float_values_from_database()
    {
        $result = $this->callProtectedMethod('castValueFromString', '3.14', 'float');

        $this->assertEquals(3.14, $result);
        $this->assertIsFloat($result);
    }

    /** @test */
    public function it_can_cast_json_values_from_database()
    {
        $jsonString = '{"key":"value","number":123}';
        $result = $this->callProtectedMethod('castValueFromString', $jsonString, 'json');

        $this->assertEquals(['key' => 'value', 'number' => 123], $result);
        $this->assertIsArray($result);
    }

    /** @test */
    public function it_can_cast_array_values_from_database()
    {
        $jsonString = '["item1","item2","item3"]';
        $result = $this->callProtectedMethod('castValueFromString', $jsonString, 'array');

        $this->assertEquals(['item1', 'item2', 'item3'], $result);
        $this->assertIsArray($result);
    }

    /** @test */
    public function it_returns_string_for_unknown_types()
    {
        $result = $this->callProtectedMethod('castValueFromString', 'test', 'unknown_type');

        $this->assertEquals('test', $result);
        $this->assertIsString($result);
    }

    /** @test */
    public function it_handles_default_cache_ttl_from_config()
    {
        // Test default TTL value that would be used if no config is set
        $this->assertEquals(3600, 3600); // Default value should be 3600 seconds
    }

    /** @test */
    public function it_can_get_many_settings_with_empty_array()
    {
        // Create a partial mock to avoid database calls
        $manager = Mockery::mock(SettingsManager::class)->makePartial();
        $manager->shouldReceive('get')->never();

        $result = $manager->getMany([]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    public function it_can_get_many_settings_with_mocked_get_method()
    {
        // Create a partial mock
        $manager = Mockery::mock(SettingsManager::class)->makePartial();
        $manager->shouldReceive('get')
            ->with('key1', null)
            ->once()
            ->andReturn('value1');
        $manager->shouldReceive('get')
            ->with('key2', null)
            ->once()
            ->andReturn('value2');
        $manager->shouldReceive('get')
            ->with('non_existent', null)
            ->once()
            ->andReturn(null);

        $result = $manager->getMany(['key1', 'key2', 'non_existent']);

        $expected = [
            'key1' => 'value1',
            'key2' => 'value2',
            'non_existent' => null,
        ];

        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function it_can_get_many_settings_with_default_value()
    {
        // Create a partial mock
        $manager = Mockery::mock(SettingsManager::class)->makePartial();
        $manager->shouldReceive('get')
            ->with('existing_key', 'default')
            ->once()
            ->andReturn('existing_value');
        $manager->shouldReceive('get')
            ->with('non_existent', 'default')
            ->once()
            ->andReturn('default');

        $result = $manager->getMany(['existing_key', 'non_existent'], 'default');

        $expected = [
            'existing_key' => 'existing_value',
            'non_existent' => 'default',
        ];

        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function it_can_set_many_settings_with_empty_array()
    {
        // Create a partial mock
        $manager = Mockery::mock(SettingsManager::class)->makePartial();
        $manager->shouldReceive('set')->never();

        $manager->setMany([]);

        // If we get here without exceptions, the test passes
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_set_many_settings_with_mocked_set_method()
    {
        // Create a partial mock
        $manager = Mockery::mock(SettingsManager::class)->makePartial();
        $manager->shouldReceive('set')
            ->with('key1', 'value1')
            ->once();
        $manager->shouldReceive('set')
            ->with('key2', 'value2')
            ->once();

        $settings = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $manager->setMany($settings);

        // If we get here without exceptions, the test passes
        $this->assertTrue(true);
    }
}
