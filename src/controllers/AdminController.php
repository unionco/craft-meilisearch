<?php
/**
 * Meilisearch plugin for Craft CMS 3.x
 *
 * Meilisearch integration for Craft
 *
 * @link      https://union.co
 * @copyright Copyright (c) 2020 Abry Rath
 */

namespace unionco\meilisearch\controllers;

use Craft;

use craft\elements\Entry;
use craft\models\Section;
use craft\web\Controller;
use craft\helpers\ArrayHelper;
use unionco\meilisearch\Meilisearch;

/**
 * @author    Abry Rath
 * @package   Meilisearch
 * @since     0.1.0
 */
class AdminController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['index', 'do-something'];

    // Public Methods
    // =========================================================================

    /**
     * @return mixed
     */
    public function actionIndex()
    {
        $result = 'Welcome to the AdminControllerController actionIndex() method';
        $settings = Meilisearch::getInstance()->getSettings();

        return $this->renderTemplate(
            'meilisearch/_rebuild',
            [
                // 'sections' => $settings->getSections(),
                // 'categories' => $settings->getCategories(),
                'settings' => $settings,
            ]
        );
        return $result;
    }

    /**
     * @return mixed
     */
    public function actionIndexes()
    {
        $resp = Meilisearch::getInstance()->getClient()->getAllIndexes();

        $result = array_map(function($resp) {
            return $resp->stats();
        }, $resp);

        return $this->renderTemplate('meilisearch/_indexes', ['indexes' => $result]);
    }

    public function actionDebug()
    {
        $req = \Craft::$app->getRequest();
        $section = $req->getQueryParam('section');

        $entries = Entry::find()
                ->sectionId($section)
                ->all();
        $elements = Meilisearch::getInstance()
            ->transforms
            ->transformElements($entries);

        return $this->asJson($elements);
    }
}
