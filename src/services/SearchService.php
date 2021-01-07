<?php

namespace unionco\meilisearch\services;

use craft\base\Component;
use unionco\meilisearch\Meilisearch;

class SearchService extends Component
{
    private $_client = null;

    public function search(string $index, string $query, $opts = [])
    {
        if (!$this->_client) {
            $this->_client = Meilisearch::getInstance()->getClient();
        }
        return $this->_client->getIndex($index)->search($query);
    }
}
