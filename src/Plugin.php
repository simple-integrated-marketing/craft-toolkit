<?php

namespace Simpleteam\Craft;

use Craft;
use craft\base\Plugin as BasePlugin;
use Simpleteam\Craft\Database\Options;

/**
 * Simple Craft Toolkit Plugin
 *
 * A toolkit for Craft CMS
 */
class Plugin extends BasePlugin
{
    /**
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public bool $hasCpSettings = false;

    /**
     * @var bool
     */
    public bool $hasCpSection = false;

    /**
     * Initialize the plugin
     */
    public function init(): void
    {
        parent::init();

        // Register the Options component globally
        $this->_registerComponents();

        Craft::info(
            Craft::t('app', '{name} plugin loaded', ['name' => $this->name]),
            __METHOD__
        );
    }

    /**
     * Register the Options component
     */
    private function _registerComponents(): void
    {
        // Register the options component if it doesn't already exist
        if (!Craft::$app->has('options')) {
            Craft::$app->set('options', [
                'class' => Options::class,
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public static function config(): array
    {
        return [
            'components' => [
                // Register the options component
                'options' => Options::class,
            ],
        ];
    }
}