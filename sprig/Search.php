<?php

namespace sprig\components\unionco;

use putyourlightson\sprig\base\Component;
use unionco\meilisearch\Meilisearch;

class Search extends Component
{
    public $results = [];
    public $query = '';

    protected $_template = 'meilisearch/_components/sprig/search';

    /** @inheritdoc */
    public function send()
    {
        // var_dump($this->query); die;
        // $this->results = Meilisearch::getInstance()->getClient()->getIndex('Property')->search($this->query);
        $this->results = [1, 2, 4];
        // $this->results = 1;
    }
}
