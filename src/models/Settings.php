<?php
/**
 * Meilisearch plugin for Craft CMS 3.x
 *
 * Meilisearch integration for Craft
 *
 * @link      https://union.co
 * @copyright Copyright (c) 2020 Abry Rath
 */

namespace unionco\meilisearch\models;

use Craft;

use craft\base\Model;
use unionco\meilisearch\config\Index;
use unionco\meilisearch\Meilisearch;

/**
 * @author    Abry Rath
 * @package   Meilisearch
 * @since     0.1.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string the Meilisearch API URL used on the front end
     */
    public string $host = 'http://127.0.0.1:7700';

    /**
     *
     * @var string the Meilisearch API URL used on the back end. May be different than the front end URL.
     */
    public string $backEndHost = 'http://127.0.0.1:7700';

    /**
     * @var string
     */
    public string $key = '';

    public $runOnSave = '';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            ['host', 'string'],
            ['host', 'default', 'value' => 'http://127.0.0.1:7700'],
            ['backEndHost', 'string'],
            ['backEndHost', 'default', 'value' => 'http://127.0.0.1:7700'],
            ['key', 'string'],
            ['key', 'default', 'value' => ''],
        ];
    }

    public function getConfigFilePath(): string
    {
        return Craft::$app->getPath()->getConfigPath() . '/meili.php';
    }

    /**
     * @return array{string,Index}
     */
    public function getIndexes()
    {
        // if (!$this->indexes) {
        $this->_initializeConfigFile();
        /** @todo Set an event to change config file path **/
        $configFilePath = $this->getConfigFilePath();
        $config = require($configFilePath);
        // var_dump($config); die;
        // $this->indexes = $config;
        // }
        return $config;
    }

    public function getRunOnSave(): bool
    {
        return (bool) Meilisearch::parseEnv($this->runOnSave);
    }


    private function _initializeConfigFile()
    {
        $configFilePath = $this->getConfigFilePath();
        if (file_exists($configFilePath)) {
            return;
        }
        $exampleFilePath = Meilisearch::$plugin->getBasePath() . '/../resources/config/meili.php.example';
        \copy($exampleFilePath, $configFilePath);
    }
}
