# Laravel Settings Package

A minimal Laravel package for storing project settings in the database.

## Installation

1. Install the package via Composer:

```bash
composer require cubecoding/laravel-settings
```

2. The package will be automatically registered via Laravel's Package Discovery.

3. Run the migrations:

```bash
php artisan migrate
```

Alternatively, you can publish the migrations and then run them:

```bash
php artisan vendor:publish --tag=cubecoding-settings-migrations
php artisan migrate
```

## Configuration

The package comes with a configuration file that you can publish and customize:

```bash
# Publish config file
php artisan vendor:publish --tag=cubecoding-settings-config

# Publish migrations (optional)
php artisan vendor:publish --tag=cubecoding-settings-migrations

# Publish everything
php artisan vendor:publish --provider="Cubecoding\Settings\SettingsServiceProvider"
```

This will create a `config/settings.php` file where you can customize:

- **Models**: Use custom models extending the base Setting model
- **Table names**: Change the database table name if needed
- **Cache settings**: Configure caching behavior, TTL, store, and cache keys

### Environment Variables

You can also configure the package using environment variables:

```bash
SETTINGS_CACHE_ENABLED=true
SETTINGS_CACHE_STORE=redis
SETTINGS_CACHE_KEY=my-app-settings
SETTINGS_CACHE_TTL=7200
```

## Usage

### Basic Usage

```php
use Cubecoding\Settings\Facades\Settings;

// Set a setting
Settings::set('app_name', 'My Application');
Settings::set('max_users', 100);
Settings::set('features', ['feature1', 'feature2']);
Settings::set('is_maintenance', true);

// Get a setting
$appName = Settings::get('app_name');
$maxUsers = Settings::get('max_users', 50); // with default value
$features = Settings::get('features');
$isMaintenance = Settings::get('is_maintenance');

// Check if setting exists
if (Settings::has('app_name')) {
    // Setting exists
}

// Delete a setting
Settings::forget('old_setting');

// Get all settings
$allSettings = Settings::all();
```

### Advanced Usage

```php
// Set multiple settings at once
Settings::setMany([
    'app_name' => 'New App',
    'version' => '1.0.0',
    'debug' => false
]);

// Get multiple settings
$settings = Settings::getMany(['app_name', 'version', 'debug']);

// Set with description
Settings::set('api_key', 'secret-key', 'API key for external services');
```

### Helper Function

The package provides a convenient global `settings()` helper function that mimics Laravel's `config()` helper:

```php
// Get a setting
$appName = settings('app.name');
$debug = settings('debug', false); // with default value

// Get all settings
$allSettings = settings();

// Set multiple settings
settings([
    'app.name' => 'My Application',
    'app.version' => '2.0.0',
    'debug' => true
]);

// Set a single setting (using second parameter)
settings('app.theme', 'dark');
```

### Dot Notation Support

The package supports dot notation for nested settings:

```php
// Set nested settings
Settings::set('app.name', 'My Application');
Settings::set('app.version', '1.0.0');
Settings::set('database.host', 'localhost');
Settings::set('database.port', 3306);

// Get nested settings
$appName = Settings::get('app.name');
$dbHost = Settings::get('database.host');

// Get entire sections as arrays
$appSettings = Settings::get('app'); // ['name' => 'My Application', 'version' => '1.0.0']
$dbSettings = Settings::get('database'); // ['host' => 'localhost', 'port' => 3306]

// Set arrays (automatically flattened)
Settings::set('config', [
    'cache' => ['driver' => 'redis', 'ttl' => 3600],
    'session' => ['driver' => 'database', 'lifetime' => 120]
]);

// Access deeply nested values
$cacheDriver = Settings::get('config.cache.driver'); // 'redis'
$sessionLifetime = Settings::get('config.session.lifetime'); // 120

// Check if nested settings exist
Settings::has('app.name'); // true
Settings::has('app.nonexistent'); // false

// Delete nested settings
Settings::forget('app.version'); // Only deletes app.version
Settings::forget('app'); // Deletes all app.* settings
```

### Caching and Performance

The package uses automatic caching for better performance:

```php
// Manually clear cache (e.g., after bulk updates)
Settings::flushCache();

// Settings are automatically loaded on first access
// and cached for 1 hour (configurable)

// Get all settings without specific key
$allSettings = Settings::get(); // Returns the complete nested structure
```

### Dependency Injection

You can also inject the SettingsManager directly:

```php
use Cubecoding\Settings\SettingsManager;

class MyController extends Controller
{
    public function index(SettingsManager $settings)
    {
        $appName = $settings->get('app_name');

        // Dot notation works here too
        $dbHost = $settings->get('database.host');

        // Clear cache if needed
        $settings->flushCache();

        // ...
    }
}
```

## Data Types

The package supports automatic type conversion for:

- **String**: Default type
- **Integer**: Whole numbers
- **Float**: Decimal numbers
- **Boolean**: true/false values
- **Array**: Arrays are automatically flattened using dot notation

```php
Settings::set('count', 42);           // stored as integer
Settings::set('price', 19.99);        // stored as float
Settings::set('enabled', true);       // stored as boolean
Settings::set('config', ['a' => 1]);  // flattened to 'config.a' = 1 (integer)
```

## Database Structure

Settings are stored in a `settings` table:

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| key | string | Unique setting key |
| value | text | Setting value |
| type | string | Data type (string, integer, float, boolean) |
| description | text | Optional description |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update date |

## API Reference

### Settings Facade / SettingsManager

| Method | Description |
|--------|-------------|
| `get($key = null, $default = null)` | Get setting (with dot notation). Without key: all settings |
| `set($key, $value = null, $description = null)` | Set setting (with dot notation and array support) |
| `has($key)` | Check if setting exists (with dot notation) |
| `forget($key)` | Delete setting (with dot notation, also deletes children) |
| `all()` | All settings as nested array structure |
| `getMany(array $keys, $default = null)` | Get multiple settings (with dot notation) |
| `setMany(array $settings)` | Set multiple settings |
| `flushCache()` | Manually clear cache |
| `boot()` | Load settings from database/cache (automatically called) |

### Advanced Features

- **Dot Notation**: All methods support dot notation for nested access
- **Automatic Caching**: Settings are cached for better performance (configurable TTL)
- **Array Flattening**: Arrays are automatically split into flat dot-notation keys
- **Type Preservation**: Data types are automatically detected and preserved
- **Bulk Operations**: Efficient processing of multiple settings
- **Schema Check**: Automatic check if settings table exists
- **Configurable**: Models, table names, and cache settings can be customized
- **Multiple Cache Stores**: Support for different cache stores (Redis, Memcached, etc.)

## Requirements

- PHP ^8.0
- Laravel ^9.0|^10.0|^11.0

## Testing

The package uses Orchestra Testbench for development and testing. Orchestra Testbench is a testing framework specifically designed for Laravel packages that provides a minimal Laravel environment for tests.

### Why Orchestra Testbench?

**Yes, it makes absolute sense to use Orchestra Testbench for development!**

**Benefits:**
- **Real Laravel Environment**: Testbench provides a complete but minimal Laravel application
- **Service Provider Testing**: Enables testing of Service Providers, Facades and Laravel-specific features
- **Database Testing**: Supports migrations and database tests with SQLite in-memory
- **Package Discovery**: Tests automatic package registration
- **Isolation**: Each test runs in a clean environment

### Running Tests

```bash
# Run all tests
./vendor/bin/phpunit
# or with Composer
composer test

# Tests with coverage
./vendor/bin/phpunit --coverage-html coverage
# or with Composer
composer test-coverage

# Only Unit Tests (fast, no database)
./vendor/bin/phpunit tests/Unit
# or with Composer
composer test-unit

# Only Feature Tests (end-to-end and integration)
./vendor/bin/phpunit tests/Feature
# or with Composer
composer test-feature

# Run individual test
./vendor/bin/phpunit tests/Unit/SettingModelTest.php
./vendor/bin/phpunit tests/Feature/SettingsTest.php
./vendor/bin/phpunit tests/Feature/SettingModelFeatureTest.php
./vendor/bin/phpunit tests/Feature/SettingsManagerFeatureTest.php
```

### Test Structure

```
tests/
├── TestCase.php                           # Base TestCase with Orchestra Testbench
├── Feature/
│   ├── SettingsTest.php                   # End-to-End Tests for Settings Facade
│   ├── SettingModelFeatureTest.php        # Feature Tests for Setting Model (with DB)
│   └── SettingsManagerFeatureTest.php     # Feature Tests for SettingsManager (with DB)
└── Unit/
    ├── SettingModelTest.php               # True Unit Tests for Setting Model (no DB)
    └── SettingsManagerTest.php            # True Unit Tests for SettingsManager (no DB)
```

### Test Types Explained

**Unit Tests** (`tests/Unit/`):
- Test individual components in isolation
- Use **no** database
- Use mocks and stubs for dependencies
- Fast and focused on individual methods/classes

**Feature Tests** (`tests/Feature/`):
- Test complete user workflows and component integration
- End-to-end tests across the entire application
- Use the **real** database (with RefreshDatabase)
- Use facades and test the public API
- Test both individual components and their interaction
- Simulate real application scenarios
- Cover both integration and end-to-end functionality

### Writing Your Own Tests

```php
<?php

namespace YourApp\Tests;

use Cubecoding\Settings\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MySettingsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_store_my_custom_setting()
    {
        \Settings::set('my_setting', 'my_value');

        $this->assertEquals('my_value', \Settings::get('my_setting'));
    }
}
```

### Continuous Integration

For CI/CD pipelines (GitHub Actions, GitLab CI, etc.), Orchestra Testbench is ideal because it:
- Starts quickly (no full Laravel installation needed)
- Can test different Laravel versions
- Is compatible with different PHP versions

Example GitHub Actions workflow:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        php: [8.0, 8.1, 8.2]
        laravel: [9.*, 10.*, 11.*]

    steps:
    - uses: actions/checkout@v3

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: ${{ matrix.php }}

    - name: Install dependencies
      run: composer install

    - name: Run tests
      run: ./vendor/bin/phpunit
```

## License

MIT License
