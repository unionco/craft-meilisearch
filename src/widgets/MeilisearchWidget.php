<?php

namespace unionco\meilisearch\widgets;

use Craft;
use craft\web\View;
use craft\base\Widget;
use unionco\meilisearch\Meilisearch;

class MeilisearchWidget extends Widget
{
    protected static function allowMultipleInstances(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return "Rebuild Search Indexes";
    }

    public function getBodyHtml()
    {
        /** @var View */
        $view = Craft::$app->getView();

        $indexes = Meilisearch::getInstance()->getSettings()->getIndexes();

        $options = [
            [
                'value' => '',
                'label' => 'Select an index',
            ],
        ];
        foreach ($indexes as $uid => $value) {
            $options[] = [
                'value' => $uid,
                'label' => $uid,
            ];
        }

        $template = $view->renderTemplate('meilisearch/_widget', compact('options'));
        return $template;
    }
}
