<?php

namespace unionco\meilisearch\console\controllers;

use craft\console\Controller;
use unionco\meilisearch\Meilisearch;
use yii\console\ExitCode;

class MeilisearchController extends Controller
{
    public function actionReplaceElement(string $uid, int $elementId, int $siteId = 1)
    {
        try {
            Meilisearch::getInstance()->index->replace($uid, $elementId, $siteId);
            $this->stdout("Added replace job to queue\n");
        } catch (\Throwable $e) {
            $this->stderr("Error: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        return ExitCode::OK;
    }

    public function actionRebuildIndex(string $uid)
    {
        try {
            Meilisearch::getInstance()->index->rebuild($uid);
            $this->stdout("Added rebuild job to queue\n");
        } catch (\Throwable $e) {
            $this->stderr("Error: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        return ExitCode::OK;
    }

    public function actionDeleteAllDocuments(string $uid) {
        try {
            Meilisearch::getInstance()->index->deleteAllDocuments($uid);
            $this->stdout("Added delete-all job to queue\n");
        } catch (\Throwable $e) {
            $this->stderr("Error:" . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        return ExitCode::OK;
    }
}