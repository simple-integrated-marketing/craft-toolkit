
# Simple Craft Toolkit

A comprehensive toolkit for Craft CMS that provides complementary functionality and utilities. Currently includes an options system for easy key-value storage accessible via `Craft::$app->options`.

## Installation

```bash
composer require simple-team/craft-toolkit
```

The plugin will automatically register the `options` component when installed.

## Usage

Once installed, you can use the options component anywhere in your Craft application:

### Store a value
```php
Craft::$app->options->set('my_key', 'my_value');

// Store complex data (automatically will be JSON encoded)
Craft::$app->options->set('user_preferences', [
    'theme' => 'dark',
    'language' => 'en',
    'notifications' => true
]);
```

### Retrieve a value
```php
$value = Craft::$app->options->get('my_key');

// With default value
$theme = Craft::$app->options->get('my_key', 'some_default_value');
```

### Check if option exists
```php
if (Craft::$app->options->exists('my_key')) {
    // Option exists
}

// Or use the alias
if (Craft::$app->options->has('my_key')) {
    // Option exists
}
```

### Delete an option
```php
Craft::$app->options->delete('my_key');
```

### Get all options
```php
$allOptions = Craft::$app->options->getAll();

// Get only autoload options
$autoloadOptions = Craft::$app->options->getAll(true);
```

### Set multiple options
```php
Craft::$app->options->setMultiple([
    'option1' => 'value1',
    'option2' => 'value2',
    'option3' => ['complex' => 'data']
]);
```

## Features

### Options System
- **Automatic Installation**: No manual configuration required
- **WordPress-like API**: Familiar interface for WordPress developers
- **JSON Support**: Automatically handles complex data types
- **Database Storage**: Uses a dedicated table with proper indexing
- **Autoload Support**: Option to mark frequently used options for caching
- **Clean Syntax**: Available anywhere via `Craft::$app->options`

## Database

The plugin automatically creates a `simple_options` table with the following structure:

- `id` - Primary key
- `key` - Option key (unique index)
- `value` - Option value (longtext)
- `isJson` - Boolean flag for JSON data
- `autoload` - Boolean flag for autoloading
- `dateCreated` - Creation timestamp
- `dateUpdated` - Last update timestamp

## Requirements

- Craft CMS 5.0 or later
- PHP 8.0 or later

## License

MIT License