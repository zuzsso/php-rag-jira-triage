<?php

declare(strict_types=1);

namespace JiRag\Model\HttpTools;

use DiManifest\AbstractDependencyInjection;
use JiRag\Model\HttpTools\Service\CurlBasedHttpExecutor;
use JiRag\Model\HttpTools\UseCase\ExecuteHttp;

use function DI\autowire;

class HttpToolsDiManifest extends AbstractDependencyInjection
{
    public static function getDependencies(): array
    {
        return [
            ExecuteHttp::class => autowire(CurlBasedHttpExecutor::class)
        ];
    }
}