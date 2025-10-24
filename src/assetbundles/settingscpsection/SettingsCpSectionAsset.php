<?php
/**
 * Meilisearch plugin for Craft CMS 3.x
 *
 * Meilisearch integration for Craft
 *
 * @link      https://union.co
 * @copyright Copyright (c) 2020 Abry Rath
 */

namespace unionco\meilisearch\assetbundles\settingscpsection;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Abry Rath
 * @package   Meilisearch
 * @since     0.1.0
 */
class SettingsCPSectionAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@unionco/meilisearch/assetbundles/settingscpsection/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Settings.js',
        ];

        $this->css = [
            'css/Settings.css',
        ];

        parent::init();
    }
}
