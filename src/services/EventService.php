<?php

namespace unionco\meilisearch\services;

use Throwable;
use craft\web\View;
use yii\base\Event;
use craft\helpers\App;
use craft\base\Component;
use craft\elements\Entry;
use craft\elements\Category;
use craft\events\ModelEvent;
use craft\services\Dashboard;
use craft\helpers\ArrayHelper;
use craft\helpers\ElementHelper;
use unionco\meilisearch\Meilisearch;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterComponentTypesEvent;
use unionco\meilisearch\widgets\MeilisearchWidget;
use unionco\meilisearch\services\MeilisearchService;

class EventService extends Component
{
    public function attachEventListeners()
    {
        // Register templates + Widgets
        // Widgets
        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = MeilisearchWidget::class;
            }
        );

        // Update the CraftVariable
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $e) {
                /** @var CraftVariable $variable */
                $variable = $e->sender;
        
                // Attach a service:
                $variable->set('meilisearch', MeilisearchService::class);
            }
        );


        // Search-specific events

        $settings = Meilisearch::getInstance()->getSettings();
        if (!$settings->getRunOnSave()) {
            return;
        }
        /**
         * Compile a mapping of all of the triggers to rebuild a given index UID
         * For example, if the `properties_index` index should be rebuilt whenever an Entry
         * in the section called `propertiesSection` is saved, then the array would include this:
         * ```php
         * [
         *  'sections' => [
         *      'propertiesSection' => [
         *          'properties_index'
         *      ],
         *  ],
         * ]
         * ```
         * @var array{sections:array{string,string[]}}
         * */
        $rebuildMap = [
            'sections' => [],
            'categories' => [],
            // ...
        ];

        // Get all of the defined indexes from the config file
        try {
            $indexes = $settings->getIndexes();
            // var_dump($indexes); die;
            foreach ($indexes as $uid => $index) {
                $rebuildTriggers = $index->getRebuild();
                if ($rebuildTriggers['sections'] ?? false) {
                    foreach ($rebuildTriggers['sections'] as $sectionHandle) {
                        $rebuildMap['sections'][$sectionHandle][] = $uid;
                    }
                }
                if ($rebuildTriggers['categories'] ?? false) {
                    foreach ($rebuildTriggers['categories'] as $categoryGroupHandle) {
                        $rebuildMap['categories'][$categoryGroupHandle][] = $uid;
                    }
                }
                /** @todo */
                // and so on...
            }
        } catch (Throwable $e) {
            $indexes = [];
            $rebuildMap = [
                'sections' => [],
                'categories' => [],
            ];
        }

        // Attach Entry section listeners, if set
        if ($rebuildMap['sections'] ?? false) {

            $entryRebuildCallback = function (ModelEvent $event) use ($rebuildMap) {
                /** @var Entry */
                $entry = $event->sender;

                if (ElementHelper::isDraftOrRevision($entry)) {
                    return;
                }
                /** @var string */
                $sectionHandle = $entry->section->handle;
                /** @var string[] */
                $enabledSections = array_keys($rebuildMap['sections']);
                if (ArrayHelper::isIn($sectionHandle, $enabledSections)) {
                    /** @var string[] */
                    $uids = $rebuildMap['sections'][$sectionHandle];
                    foreach ($uids as $uid) {
                        // Create a 'replace' job for the matching element
                        Meilisearch::getInstance()->index->replace($uid, $entry->id, $entry->siteId);
                    }
                }
            };
            Event::on(
                Entry::class,
                Entry::EVENT_AFTER_SAVE,
                $entryRebuildCallback
            );
        }
        // Attach Caegory group listeners, if set
        if ($settings->getRunOnSave() && $rebuildMap['categories'] ?? false) {
            Event::on(
                Category::class,
                Category::EVENT_AFTER_SAVE,
                function (ModelEvent $event) use ($rebuildMap) {
                    /** @var Category */
                    $category = $event->sender;
                    /** @var string */
                    $groupHandle = $category->group->handle;
                    /** @var string[] */
                    $enabledGroups = array_keys($rebuildMap['categories']);
                    if (ArrayHelper::isIn($groupHandle, $enabledGroups)) {
                        /** @var string[] */
                        $uids = $rebuildMap['categories'][$groupHandle];
                        foreach ($uids as $uid) {
                            // Meilisearch::getInstance()->index->rebuild($uid);
                            Meilisearch::getInstance()->index->replace($uid, $category->id, $category->siteId);
                        }
                    }
                }
            );
        }

        /** @todo */
        // and so on...

    }
}
