<?php

namespace unionco\meilisearch\controllers;

use craft\helpers\UrlHelper;
use craft\web\Controller;
use unionco\meilisearch\Meilisearch;

class IndexController extends Controller
{
    protected $allowAnonymous = ['show', 'test'];

    public function actionShow()
    {
        return $this->asJson(['todo']);
    }

    public function actionRebuild()
    {
        $req = \Craft::$app->getRequest();

        $sections = $req->getBodyParam('sections');
        if ($sections) {
            foreach ($sections as $section) {
                Meilisearch::getInstance()->index->rebuildSection((int) $section);
            }
        }

        $categories = $req->getBodyParam('categories');
        if ($categories) {
            foreach ($categories as $group) {
                Meilisearch::getInstance()->index->rebuildCategory((int) $group);
            }
        }

        \Craft::$app->getSession()-> setFlash('cp-notice', 'Meilisearch index rebuild started');

        return $this->redirect(UrlHelper::cpUrl('meilisearch'));
    }

    public function actionTest()
    {
        Meilisearch::getInstance()->index->executeRebuildJob('properties_by_market_or_submarket');
    }
}
