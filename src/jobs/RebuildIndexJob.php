<?php

/**
 * Meilisearch plugin for Craft CMS 3.x
 *
 * Meilisearch integration for Craft
 *
 * @link      https://union.co
 * @copyright Copyright (c) 2020 Abry Rath
 */

namespace unionco\meilisearch\jobs;

use Craft;
use craft\queue\BaseJob;
use unionco\meilisearch\Meilisearch;

/**
 * @author    Abry Rath
 * @package   Meilisearch
 * @since     0.1.0
 */
class RebuildIndexJob extends BaseJob
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */

    public $uid = '';
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        Meilisearch::getInstance()->index->executeRebuildJob($this->uid, $queue);
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('meilisearch', 'Rebuilding Meilisearch Index - ' . $this->uid);
    }
}
