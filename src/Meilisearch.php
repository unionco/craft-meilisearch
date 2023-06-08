<?php

/**
 * Meilisearch plugin for Craft CMS 3.x
 *
 * Meilisearch integration for Craft
 *
 * @link      https://union.co
 * @copyright Copyright (c) 2020 Abry Rath
 */

namespace unionco\meilisearch;

use Craft;
use craft\base\Plugin;
use craft\helpers\App;
use MeiliSearch\Client;
use unionco\meilisearch\models\Settings;
use craft\console\Application as ConsoleApplication;
use unionco\meilisearch\twigextensions\MeilisearchTwigExtension;
use unionco\meilisearch\services\MeilisearchService as MeilisearchServiceService;

/**
 * Class Meilisearch
 *
 * @author    Abry Rath
 * @package   Meilisearch
 * @since     0.1.0
 *
 * @property  MeilisearchServiceService $meilisearchService
 */
class Meilisearch extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Meilisearch
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '0.1.1';

    /**
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * @var bool
     */
    public $hasCpSection = false;

    private $client;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->initializeClient();

        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'unionco\meilisearch\console\controllers';
        } else {
            $this->controllerNamespace = 'unionco\meilisearch\controllers';
        }

        $this->setComponents([
            'events' => \unionco\meilisearch\services\EventService::class,
            'index' => \unionco\meilisearch\services\IndexService::class,
            'search' => \unionco\meilisearch\services\SearchService::class,
        ]);

        $this->events->attachEventListeners();
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    protected function initializeClient()
    {
        $settings = $this->getSettings();
        $host = self::parseEnv($settings->backEndHost);

        if (!$host) {
            $host = $settings->host;
        }
        $key = self::parseEnv($settings->key);
        if (!$key) {
            $key = $settings->key;
        }
        $this->client = new Client($host, $key);
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'meilisearch/settings',
            [
                'settings' => $this->getSettings(),
            ]
        );
    }

    public static function parseEnv(string $handle)
    {
        try {
            if (method_exists(App::class, 'parseEnv')) {
                return App::parseEnv($handle);
            }
            return Craft::parseEnv($handle);
        } catch (\Throwable $e) {
            return $handle;
        }
    }
}
