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
    public $schemaVersion = '0.1.0';

    /**
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * @var bool
     */
    public $hasCpSection = true;

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
            Craft::$app->getView()->registerTwigExtension(new MeilisearchTwigExtension());
        }

        $this->setComponents([
            'events' => \unionco\meilisearch\services\EventService::class,
            'index' => \unionco\meilisearch\services\IndexService::class,
            'search' => \unionco\meilisearch\services\SearchService::class,
            // 'log' => \unionco\meilisearch\services\LogService::class,
            // 'transforms' => \unionco\meilisearch\services\TransformService::class,
        ]);

        $this->events->attachEventListeners();
    }

    /** @inheritdoc */
    public function getCpNavItem()
    {
        return [
            'url' => 'meilisearch',
            'label' => 'Meilisearch',
            'subnav' => [
                'indexes' => [
                    'label' => 'Rebuild Indexes',
                    'url' => 'meilisearch/indexes',
                ],
                'search' => [
                    'label' => 'Search',
                    'url' => 'meilisearch/search',
                ],
            ],
        ];
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
        $host = App::parseEnv($settings->host);

        if (!$host) {
            $host = $settings->host;
        }
        $key = App::parseEnv($settings->key);
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
}
