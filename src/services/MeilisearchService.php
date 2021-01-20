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
    // Public Methods
    // =========================================================================

    /*
     * @return mixed
     */
    public function exampleService()
    {
        $result = 'something';
        // Check our Plugin's settings for `someAttribute`
        if (Meilisearch::$plugin->getSettings()->someAttribute) {
        }

        return $result;
    }

    /**
      * Search for documents matching a specific query in the given index.
      * @param queryText Query originating the response
      * @param meilisearchIndex Meilisearch index UID
      * @param offset Number of documents skipped
      * @param limit Number of documents to take
      * @return object Results of the query
      */
    public function search($queryText, $indexName, $offset = 0, $limit = 100) {
        $client = Meilisearch::getInstance()->getClient();
        $index = $client->getIndex($indexName);
        $result = $index->search($queryText, ['offset' => $offset, 'limit' => $limit]);
        return $result;
    }
}
