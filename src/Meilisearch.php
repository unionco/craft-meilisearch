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

use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;
use craft\events\RegisterCpNavItemsEvent;
use craft\helpers\App;
use craft\web\twig\variables\Cp;
use MeiliSearch\Client;
use unionco\meilisearch\models\Settings;
use unionco\meilisearch\services\MeilisearchService as MeilisearchServiceService;
use yii\base\Event;
use Craft;

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
    public string $schemaVersion = '0.1.1';

    /**
     * @var bool
     */
    public bool $hasCpSettings = true;

    /**
     * @var bool
     */
    public bool $hasCpSection = false;

    private $client;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        // Only register asset bundles in web context, not console
        if (!(Craft::$app instanceof ConsoleApplication)) {
            // Register asset bundle for CP
            Craft::$app->view->registerAssetBundle(\unionco\meilisearch\assetbundles\settingscpsection\SettingsCpSectionAsset::class);
            Craft::$app->view->registerAssetBundle(\unionco\meilisearch\assetbundles\meilisearch\MeilisearchAsset::class);

            // Register CP navigation items
            Event::on(
                Cp::class,
                Cp::EVENT_REGISTER_CP_NAV_ITEMS,
                function (RegisterCpNavItemsEvent $event) {
                    $event->navItems[] = [
                        'url' => 'actions/meilisearch/index/dashboard',
                        'label' => Craft::t('meilisearch', 'Meilisearch'),
                        'subnav' => [
                            'dashboard' => ['label' => Craft::t('meilisearch', 'Dashboard'), 'url' => 'actions/meilisearch/index/dashboard'],
                            'settings' => ['label' => Craft::t('meilisearch', 'Settings'), 'url' => 'settings/plugins/meilisearch'],
                        ],
                    ];
                }
            );
        }

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

    public function getCpNavItem(): ?array
    {
        $item = parent::getCpNavItem();
        $item['label'] = Craft::t('meilisearch', 'Meilisearch');
        $item['url'] = 'meilisearch/index/dashboard';
        $item['subnav'] = [
            'dashboard' => [
                'label' => Craft::t('meilisearch', 'Dashboard'),
                'url' => 'meilisearch/index/dashboard',
            ],
            'settings' => [
                'label' => Craft::t('meilisearch', 'Settings'),
                'url' => 'settings/plugins/meilisearch',
            ],
        ];
        return $item;
    }

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate(
            'meilisearch/settings',
            [
                'settings' => $this->getSettings(),
                'indexes' => $this->getSettings()->getIndexes(),
            ]
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?\craft\base\Model
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
