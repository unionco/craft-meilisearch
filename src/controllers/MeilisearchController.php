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
use craft\web\Controller;
use unionco\meilisearch\jobs\CreateIndex;
use unionco\meilisearch\jobs\UpdateIndex;
use unionco\meilisearch\Meilisearch;

/**
 * @author    Abry Rath
 * @package   Meilisearch
 * @since     0.1.0
 */
class MeilisearchController extends Controller
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
        /**
         * @var \craft\queue\Queue
         */
        $queue = \Craft::$app->getQueue();
        $create = new CreateIndex(['uid' => 'properties']);
        $update = new UpdateIndex(['uid' => 'properties', 'section' => 'property']);
        $queue->push($create);
        $queue->push($update);
        return $this->asJson(['cool']);
    }

    /**
     * @return mixed
     */
    public function actionDoSomething()
    {
        $result = 'Welcome to the MeilisearchControllerController actionDoSomething() method';

        return $result;
    }
}
