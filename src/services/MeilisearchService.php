<?php
/**
 * Meilisearch plugin for Craft CMS 3.x
 *
 * Meilisearch integration for Craft
 *
 * @link      https://union.co
 * @copyright Copyright (c) 2020 Abry Rath
 */

namespace unionco\meilisearch\services;

use Craft;

use craft\base\Component;
use unionco\meilisearch\Meilisearch;
use unionco\meilisearch\models\Settings;

/**
 * @author    Abry Rath
 * @package   Meilisearch
 * @since     0.1.0
 */
class MeilisearchService extends Component
{
    public ?Settings $settings = null;

    public function init(): void
    {
        parent::init();
        $this->settings = Meilisearch::$plugin->getSettings();
    }

    public function host(): string
    {
        return Meilisearch::parseEnv($this->settings->host);
    }

    public function backeEndHost(): string
    {
        return Meilisearch::parseEnv($this->settings->backEndHost);
    }

    public function key(): string
    {
        return Meilisearch::parseEnv($this->settings->key);
    }

    public function runOnSave(): string
    {
        return $this->settings->getRunOnSave();
    }
}
