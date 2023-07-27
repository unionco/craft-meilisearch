<?php

namespace unionco\meilisearch\console\controllers;

use craft\console\Controller;
use unionco\meilisearch\Meilisearch;
use yii\console\ExitCode;

class MeilisearchController extends Controller
{
    /**
     * Create all indices in Meilisearch
     *
     * @return void
     */
    public function actionCreateAllIndexes()
    {
        try {
            Meilisearch::getInstance()->index->createAllIndexes();
            $this->stdout("Added create-indexes job to queue\n");
        } catch (\Throwable $e) {
            $this->stderr("Error:" . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        return ExitCode::OK;
    }

    /**
     * Replace on element in a given index
     *
     * @param string $uid
     * @param integer $elementId
     * @param integer $siteId
     * @return void
     */
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

    /**
     * Rebuild all elements in a given index
     *
     * @param string $uid
     * @return void
     */
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

    /**
     * Rebuild all indexes
     *
     * @return void
     */
    public function actionRebuildAllIndexes()
    {
        try {
            Meilisearch::getInstance()->index->rebuildAll();
            $this->stdout("Add rebuild jobs to queue\n");
        } catch (\Throwable $e) {
            $this->stderr("Error: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        return ExitCode::OK;
    }

    /**
     * Remove all elements from a given index
     *
     * @param string $uid
     * @return void
     */
    public function actionDeleteAllDocuments(string $uid)
    {
        try {
            Meilisearch::getInstance()->index->deleteAllDocuments($uid);
            $this->stdout("Added delete-all job to queue\n");
        } catch (\Throwable $e) {
            $this->stderr("Error:" . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        return ExitCode::OK;
    }

    /**
     * Delete an index entirely
     *
     * @param string $uid
     * @return void
     */
    public function actionDeleteIndex(string $uid)
    {
        try {
            Meilisearch::getInstance()->index->deleteIndex($uid);
            $this->stdout("Added delete-index job to queue\n");
        } catch (\Throwable $e) {
            $this->stderr("Error:" . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        return ExitCode::OK;
    }
}
