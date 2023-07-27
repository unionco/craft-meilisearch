<?php

namespace unionco\meilisearch\config;

use Closure;
use craft\base\Element;
use craft\elements\db\ElementQuery;
use unionco\meilisearch\services\LogService;
use yii\base\InvalidConfigException;

class Index
{
    public $uid;

    protected $_facets;
    protected $_stopWords;
    protected $_uid;
    protected $_transform;
    protected $_elementQuery;
    protected $_rebuild;
    // https://docs.meilisearch.com/guides/advanced_guides/settings.html#synonyms
    protected $_settings = [
        'synonyms' => null,
        'stopWords' => null,
        'attributesForFaceting' => null,
        'rankingRules' => null,
        'distinctAttribute' => null,
        'searchableAttributes' => null,
        'displayedAttributes' => null,
        'sortableAttributes' => null,
    ];

    public function __construct($params = [])
    {
        // Set protected values based on protected methods (potentially overriden by child class)
        $this->_settings = $this->settings();
        $this->_elementQuery = $this->elementQuery();
        $this->_transform = $this->transform();
        $this->_rebuild = $this->rebuild();
        $this->_uid = $this->uid();

        // override any values explicitly listed in the config params
        foreach ($params as $key => $value) {
            $method = "set" . ucFirst($key);
            $this->{$method}($value);
        }
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * https://docs.meilisearch.com/guides/advanced_guides/faceted_search.html#setting-up-facets
     */
    public function setAttributesForFaceting(array $facets): self
    {
        $this->_settings['attributesForFaceting'] = $facets;
        return $this;
    }


    public function setSynonyms(array $synonyms): self
    {
        $this->_settings['synonyms'] = $synonyms;
        return $this;
    }

    public function setStopWords(array $stopWords): self
    {
        $this->_settings['stopWords'] = $stopWords;
        return $this;
    }

    public function setSearchableAttributes(array $searchableAttributes): self
    {
        $this->_settings['searchableAttributes'] = $searchableAttributes;
        return $this;
    }

    public function setDisplayedAttributes(array $displayedAttributes): self
    {
        $this->_settings['displayedAttributes'] = $displayedAttributes;
        return $this;
    }

    public function setSortableAttributes(array $sortableAttributes): self
    {
        $this->_settings['sortableAttributes'] = $sortableAttributes;
        return $this;
    }

    public function getSettings(): array
    {
        return $this->_settings;
    }

    public function setTransform(Closure $transform): self
    {
        $this->_transform = $transform;
        return $this;
    }

    public function getTransform(): Closure
    {
        if ($this->_transform) {
            return $this->_transform;
        }
        return function (Element $e): array {
            return ['id' => (string) $e->id];
        };
    }

    public function setUid(string $uid): self
    {
        $this->_uid = $uid;
        return $this;
    }

    /**
     * @param ElementQuery|Closure $query
     */
    public function setElementQuery($query): self
    {
        if ($query instanceof Closure) {
            $query = $query();
        }
        if (!($query instanceof ElementQuery)) {
            throw new InvalidConfigException('elementQuery must be an instance of \craft\element\db\ElementQuery');
        }
        $this->_elementQuery = $query;
        return $this;
    }

    /**
     * @return \craft\base\Element[]
     */
    public function getElements(): array
    {
        $this->_elementQuery = $this->elementQuery();
        // LogService::debug(__METHOD__, $this->_elementQuery);
        return $this->_elementQuery->all();
    }

    public function getElementCount(): int
    {
        $this->_elementQuery = $this->elementQuery();
        return $this->_elementQuery->count();
    }

    public function getElementQuery(): ElementQuery
    {
        return $this->elementQuery();
    }

    public function setRebuild(array $rebuild): self
    {
        $this->_rebuild = $rebuild;
        return $this;
    }

    public function getRebuild(): array
    {
        return $this->_rebuild;
    }

    /**
     * Helper method for fluent interface
     */
    public function clone(): Index
    {
        $clone = clone $this;

        return $clone;
    }

    // Protected methods for setting values in child classes

    protected function transform(): Closure
    {
        if ($transform = $this->_transform) {
            return $transform;
        }
        return function (Element $e): array {
            return [
                'id' => (string) $e->id,
            ];
        };
    }

    protected function elementQuery(): ?ElementQuery
    {
        return null;
    }

    protected function settings(): array
    {
        return [];
    }

    protected function rebuild(): array
    {
        return [];
    }

    protected function uid(): string
    {
        return '';
    }
}
