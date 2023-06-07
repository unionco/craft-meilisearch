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

use unionco\meilisearch\Meilisearch;

use Craft;
use craft\base\Component;

/**
 * @author    Abry Rath
 * @package   Meilisearch
 * @since     0.1.0
 */
class MeilisearchService extends Component
{
    public function init(): void
    {
        parent::init();
        $this->settings = Meilisearch::$plugin->getSettings();
    }

    public function host(): string
    {
        return $this->settings->host;
    }

    public function backeEndHost(): string
    {
        return $this->settings->backEndHost;
    }

    public function key(): string
    {
        return $this->settings->key;
    }

    public function runOnSave(): string
    {
        return $this->settings->runOnSave;
    }
}
