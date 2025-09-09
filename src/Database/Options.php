<?php

namespace Simpleteam\Craft\Database;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\helpers\Db;
use craft\helpers\Json;
use yii\db\Exception;

/**
 * Options Module
 * 
 * WordPress-like options functionality for storing key-value pairs in craft
 */
class Options extends Component
{
    /**
     * Table name
     */
    const TABLE_NAME = '{{%simple_options}}';

    /**
     * Initialize the service and ensure the table exists
     */
    public function init(): void
    {
        parent::init();
        $this->ensureTableExists();
    }

    /**
     * Store a key-value pair
     *
     * @param string $key The option key
     * @param mixed $value The option value (will be JSON encoded if not string)
     * @param bool $autoload Whether to autoload this option (for future caching)
     * 
     * @return bool Success status
     */
    public function set(string $key, $value, bool $autoload = false): bool
    {
        try {
            $db = Craft::$app->getDb();
            
            // Serialize data types other than string
            $serializedValue = is_string($value) ? $value : Json::encode($value);
            $isJson = !is_string($value);
            
            // Check if option already exists
            $existingOption = (new Query())
                ->select(['id'])
                ->from(self::TABLE_NAME)
                ->where(['key' => $key])
                ->one();

            $now = Db::prepareDateForDb(new \DateTime());

            if ( $existingOption ) {
                // Update existing option
                return $db->createCommand()
                    ->update(self::TABLE_NAME, [
                        'value' => $serializedValue,
                        'isJson' => $isJson,
                        'autoload' => $autoload,
                        'dateUpdated' => $now
                    ], ['key' => $key])
                    ->execute() > 0;
            } else {
                // Insert new option
                return $db->createCommand()
                    ->insert(self::TABLE_NAME, [
                        'key' => $key,
                        'value' => $serializedValue,
                        'isJson' => $isJson,
                        'autoload' => $autoload,
                        'dateCreated' => $now,
                        'dateUpdated' => $now
                    ])
                    ->execute() > 0;
            }
        } catch (Exception $e) {
            Craft::error('Failed to store option: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Retrieve a value by key
     *
     * @param string $key The option key
     * @param mixed $default Default value if key doesn't exist
     * 
     * @return mixed The option value or default
     */
    public function get(string $key, $default = null)
    {
        try {
            $option = (new Query())
                ->select(['value', 'isJson'])
                ->from(self::TABLE_NAME)
                ->where(['key' => $key])
                ->one();

            if (!$option) {
                return $default;
            }

            // Deserialize if it was stored as JSON
            if ( ! empty($option['isJson']) ) {
                return Json::decode( $option['value'] );
            }

            return $option['value'];
        } catch (Exception $e) {
            Craft::error('Failed to retrieve option: ' . $e->getMessage(), __METHOD__);
            return $default;
        }
    }

    /**
     * Delete an option
     *
     * @param string $key The option key
     * @return bool Success status
     */
    public function delete(string $key): bool
    {
        try {
            $db = Craft::$app->getDb();
            return $db->createCommand()
                ->delete(self::TABLE_NAME, ['key' => $key])
                ->execute() > 0;
        } catch (Exception $e) {
            Craft::error('Failed to delete option: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Check if an option exists
     *
     * @param string $key The option key
     * @return bool
     */
    public function exists(string $key): bool
    {
        try {
            $count = (new Query())
                ->from(self::TABLE_NAME)
                ->where(['key' => $key])
                ->count();

            return $count > 0;
        } catch (Exception $e) {
            Craft::error('Failed to check option existence: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Check if an option exists
     *
     * @param string $key The option key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->exists( $key );
    }

    /**
     * Get all options
     *
     * @param bool|null $autoload Filter by autoload status
     * 
     * @return array Associative array of key-value pairs
     */
    public function getAll(?bool $autoload = null): array
    {
        try {
            $query = (new Query())
                ->select(['key', 'value', 'isJson'])
                ->from(self::TABLE_NAME);

            if ($autoload !== null) {
                $query->where(['autoload' => $autoload]);
            }

            $options = $query->all();
            $result = [];

            foreach ($options as $option) {
                $value = $option['isJson'] 
                    ? Json::decode($option['value']) 
                    : $option['value'];
                $result[$option['key']] = $value;
            }

            return $result;
        } catch (Exception $e) {
            Craft::error('Failed to retrieve all options: ' . $e->getMessage(), __METHOD__);
            return [];
        }
    }

    /**
     * Update multiple options at once
     *
     * @param array $options Associative array of key-value pairs
     * @param bool $autoload Whether to set autoload for all options
     * 
     * @return bool Success status
     */
    public function setMultiple(array $options, bool $autoload = false): bool
    {
        $success = true;
        foreach ($options as $key => $value) {
            if (!$this->set($key, $value, $autoload)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Ensure the options table exists
     */
    private function ensureTableExists(): void
    {
        $db = Craft::$app->getDb();
        $tableSchema = $db->getTableSchema(self::TABLE_NAME);
        
        if ($tableSchema === null) {
            $this->createTable();
        }
    }

    /**
     * Create the options table
     */
    private function createTable(): void
    {
        try {
            $db = Craft::$app->getDb();
            
            $db->createCommand()
                ->createTable(self::TABLE_NAME, [
                    'id' => 'pk',
                    'key' => 'varchar(255) NOT NULL',
                    'value' => 'longtext',
                    'isJson' => 'boolean DEFAULT false',
                    'autoload' => 'boolean DEFAULT false',
                    'dateCreated' => 'datetime NOT NULL',
                    'dateUpdated' => 'datetime NOT NULL',
                ])
                ->execute();

            // Create unique index on key
            $db->createCommand()
                ->createIndex('idx_simple_options_key', self::TABLE_NAME, 'key', true)
                ->execute();

            // Create index on autoload for performance
            $db->createCommand()
                ->createIndex('idx_simple_options_autoload', self::TABLE_NAME, 'autoload')
                ->execute();

            Craft::info('Created simple_options table successfully', __METHOD__);
        } catch (Exception $e) {
            Craft::error('Failed to create simple_options table: ' . $e->getMessage(), __METHOD__);
        }
    }
}