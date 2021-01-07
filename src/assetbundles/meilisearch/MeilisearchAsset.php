<?php
/**
 * Meilisearch plugin for Craft CMS 3.x
 *
 * Meilisearch integration for Craft
 *
 * @link      https://union.co
 * @copyright Copyright (c) 2020 Abry Rath
 */

namespace unionco\meilisearch\assetbundles\meilisearch;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Abry Rath
 * @package   Meilisearch
 * @since     0.1.0
 */
class MeilisearchAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@unionco/meilisearch/assetbundles/meilisearch/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Meilisearch.js',
        ];

        $this->css = [
            'css/Meilisearch.css',
        ];

        parent::init();
    }
}
