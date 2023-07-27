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
class ReplaceElementJob extends BaseJob
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */

    public string $uid = '';
    public int $elementId = 0;
    public int $siteId = 0;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        Meilisearch::getInstance()->index->executeReplaceElementJob($this->uid, $this->elementId, $this->siteId, $queue);
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('meilisearch', 'Replacing Element ' . $this->elementId . ' in Meilisearch Index - ' . $this->uid);
    }
}
