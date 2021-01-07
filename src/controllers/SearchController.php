<?php

namespace unionco\meilisearch\controllers;

use craft\web\Controller;

class SearchController extends Controller
{
    public function actionIndex()
    {
        return $this->renderTemplate('meilisearch/_search');
    }
}
