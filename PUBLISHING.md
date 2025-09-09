# Publishing Simple Craft Toolkit to Packagist

This guide explains how to publish the Simple Craft Toolkit package to Packagist and how the automatic configuration works.

## Prerequisites

1. **GitHub Repository**: Create a public GitHub repository for your package
2. **Packagist Account**: Sign up at [packagist.org](https://packagist.org)
3. **Git Tags**: Use semantic versioning for releases

## Publishing Steps

### 1. Prepare Your Repository

```bash
# Pull the repository 
git clone https://github.com/simple-integrated-marketing/craft-toolkit.git
cd craft-toolkit
```

### 2. Publish New Version

```bash
# Commit your changes
git add .
git commit -m "Add new feature"

# Create new version tag
git tag 1.1.0
git push origin main
git push --tags
```

Packagist will automatically update if you've set up webhooks.


## How Automatic Configuration Works

When someone installs this package via `composer require simpleteam/craft-toolkit`, the following happens automatically:

### 1. Composer Installation
```bash
composer require simpleteam/craft-toolkit
```

### 2. Craft Plugin Discovery
Craft CMS automatically discovers the plugin through:
- The `type: "craft-plugin"` in `composer.json`
- The `extra.class` configuration pointing to `Simpleteam\Craft\Plugin`

### 3. Plugin Initialization
The `Plugin` class automatically:
- Registers the `Options` component as `Craft::$app->options`
- Creates the database table if it doesn't exist
- Makes the component available globally

### 4. No Manual Configuration Required
Users can immediately use:
```php
Craft::$app->options->set('key', 'value');
$value = Craft::$app->options->get('key');
```

## Key Configuration Files

### composer.json
- `type: "craft-plugin"` - Tells Craft this is a plugin
- `extra.class` - Points to the main Plugin class
- `extra.handle` - Unique plugin identifier
- `extra.components` - Defines the components to register

### Plugin.php
- Extends `craft\base\Plugin`
- Registers the Options component in `init()` method
- Uses `config()` method to define component configuration