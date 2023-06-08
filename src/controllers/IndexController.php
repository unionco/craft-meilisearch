<?php

namespace unionco\meilisearch\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use unionco\meilisearch\services\IndexService;
use unionco\meilisearch\Meilisearch;

class IndexController extends Controller
{
    public function actionWidgetRebuild()
    {
        $this->requirePostRequest();
        $req = Craft::$app->getRequest();
        $uid = $req->getRequiredBodyParam('uid');
        if (!$uid) {
            Craft::$app->getSession()
                ->setFlash('cp-error', 'Please select an index');
            return $this->redirectToPostedUrl();
        }
        /** @var IndexService */
        $indexService = Meilisearch::$plugin->index;
        $indexService->rebuild($uid);
        // Set a flash notice
        Craft::$app->getSession()
            ->setFlash('cp-notice', 'Index rebuild started');
        return $this->redirectToPostedUrl();
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
}
