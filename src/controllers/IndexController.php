<?php

namespace unionco\meilisearch\controllers;

use Craft;
use craft\web\Controller;
use unionco\meilisearch\Meilisearch;
use yii\web\Response;

class IndexController extends Controller
{
    protected array|int|bool $allowAnonymous = [];

    public function actionDashboard(): Response
    {
        $this->requireAdmin();
        $indexes = Meilisearch::getInstance()->getSettings()->getIndexes();
        Craft::info('Indexes being passed to template: ' . print_r($indexes, true), __METHOD__);
        return $this->renderTemplate('meilisearch/dashboard', [
            'indexes' => $indexes,
        ]);
    }

    public function actionUpdateSettings(): Response
    {
        $this->requireAdmin();
        $this->requirePostRequest();
        $uid = $this->request->getRequiredBodyParam('uid');
        $settings = $this->request->getBodyParam('settings', []);
        
        $client = Meilisearch::getInstance()->getClient();
        $index = $client->getIndex($uid);
        
        foreach ($settings as $key => $value) {
            if ($value !== null) {
                $method = 'update' . ucfirst($key);
                try {
                    $index->$method($value);
                } catch (\Exception $e) {
                    Craft::error("Failed to update $key for index $uid: " . $e->getMessage(), __METHOD__);
                }
            }
        }
        
        Craft::$app->getSession()->setNotice(Craft::t('meilisearch', 'Index settings updated.'));
        return $this->redirectToPostedUrl();
    }

    public function actionRebuild(): Response
    {
        $this->requireAdmin();
        $uid = $this->request->getRequiredParam('uid');
        Meilisearch::getInstance()->index->rebuild($uid);
        Craft::$app->getSession()->setNotice(Craft::t('meilisearch', 'Index rebuild queued.'));
        return $this->redirectToPostedUrl();
    }
}
