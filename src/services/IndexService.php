<?php

namespace unionco\meilisearch\services;

use craft\queue\Queue;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use unionco\meilisearch\Meilisearch;
use yii\base\InvalidConfigException;
use unionco\meilisearch\jobs\UpdateIndex;
use unionco\meilisearch\services\LogService;

class IndexService extends Component
{
    /**
     * Add a rebuild job to the queue
     */
    public function rebuild(string $uid)
    {
        $job = new UpdateIndex([
            'uid' => $uid,
        ]);
        /** @var Queue */
        $queue = \Craft::$app->getQueue();
        $this->removeDuplicateJobs($queue, $uid);

        // remove duplicates
        $queue->push($job);

        return true;
    }

    public function rebuildAll()
    {
        $indexes = Meilisearch::getInstance()->getSettings()->getIndexes();
        foreach ($indexes as $handle => $index) {
            $this->rebuild($handle);
        }
    }

    private function removeDuplicateJobs(Queue &$queue, string $uid): void
    {
        $jobInfo = $queue->getJobInfo();
        $duplicates = array_filter(
            $jobInfo,
            function ($job) use ($uid) {
                $description = $job['description'];
                $strMatch = strpos($description, 'Updating Meilisearch Index - ' . $uid) !== false;

                $status = $job['status'];
                $validStatus = $status == Queue::STATUS_WAITING || Queue::STATUS_RESERVED;

                return $strMatch && $validStatus;
            }
        );
        $duplicateIds = array_map(
            function ($job): string {
                return (string) $job['id'];
            },
            $duplicates
        );
        foreach ($duplicateIds as $id) {
            $queue->release($id);
        }
    }

    /**
     * Execute the actual logic for the queue job
     */
    public function executeRebuildJob($uid, ?\craft\queue\Queue $queue = null)
    {
        $settings = Meilisearch::getInstance()->getSettings();

        $indexConfig = $settings->getIndexes()[$uid];

        $elementCount = $indexConfig->getElementCount();
        $elementQuery = $indexConfig->getElementQuery();

        LogService::debug(__METHOD__, $uid);
        if (!$elementCount) {
            LogService::error(__METHOD__, 'No elements matched index elementQuery - ' .  $uid);
            return;
        }

        $transform = $indexConfig->getTransform();

        /** @var array[] */
        $transformed = [];
        foreach ($elementQuery->each() as $i => $element) {
            // Scale by 90% - the last 10% will be the meilisearch API call
            $progress = ceil(($i / $elementCount) * 90);
            $transformed[] = $transform($element);
            if ($queue) {
                $queue->setProgress($progress, 'Querying and transforming elements');
            }
        }

        $transformed = array_filter($transformed);
        if (!$transformed) {
            LogService::error(__METHOD__, 'No elements remain after transformation - ' .  $uid);
            return;
        }

        $flattened = [];
        foreach ($transformed as $group) {
            if (key_exists('id', $group)) {
                $flattened[] = $group;
            } else {
                $firstLevel = ArrayHelper::firstValue($group);
                // LogService::debug('firstLevel', $firstLevel);
                if (key_exists('id', $firstLevel)) {
                    $flattened[] = $firstLevel;
                } else {
                    $secondLevel = ArrayHelper::firstValue($firstLevel);
                    // LogService::debug('secondLevel', $secondLevel);
                    $flattened[] = $secondLevel;
                }
            }
        }

        if ($queue) {
            $queue->setProgress(90, 'Preparing Meilisearch API call');
        }
        // Meilisearch calls are async, so there is no way to show progress
        $client = Meilisearch::getInstance()->getClient();
        /** @todo read from config */
        // $this->delete($uid);
        $index = $client->getIndex($uid);
        $indexSettings = $indexConfig->getSettings();
        foreach ($indexSettings as $attr => $value) {
            if (!$value) {
                continue;
            }
            $name = "update" . ucFirst($attr);
            $index->{$name}($value);
        }
        // delete all documents in the index before rebuilding
        $index->deleteAll();
        LogService::debug(__METHOD__ . ' - Before Add Documents (count)', count($transformed));
        try {
            $result = $index->addDocuments($flattened);
            LogService::info(__METHOD__, $result);
            // LogService::error(__METHOD__ . "[INFO]", $flattened);
        } catch (\Throwable $e) {
            LogService::error(__METHOD__ . "[ERROR]" . __METHOD__, $e->getMessage());
            // LogService::error(__METHOD__, $flattened);
            throw $e;
        }
        if ($queue) {
            $queue->setProgress(100, 'Complete');
        }
    }

    public function delete(string $uid)
    {
        $client = Meilisearch::getInstance()->getClient();
        try {
            $client->deleteIndex($uid);
        } catch (\MeiliSearch\Exceptions\HTTPRequestException $e) {
        }
    }

    public function deleteAll()
    {
        $client = Meilisearch::getInstance()->getClient();
        $client->deleteAllIndexes();
    }
}
