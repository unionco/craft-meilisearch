<?php

namespace unionco\meilisearch\twigextensions;

use Twig\Extension\GlobalsInterface;
use unionco\meilisearch\Meilisearch;
use Twig\Extension\AbstractExtension;

class MeilisearchTwigExtension extends AbstractExtension implements GlobalsInterface
{
    public function getName()
    {
        return 'Meilisearch';
    }

    public function getGlobals()
    {
        return [
            'meili' => Meilisearch::getInstance(),
        ];
    }
}
