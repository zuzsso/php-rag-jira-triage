<?php

declare(strict_types=1);

namespace JiRag\Model\Triage;

use DiManifest\AbstractDependencyInjection;
use JiRag\Model\Triage\Service\TriageService;
use JiRag\Model\Triage\UseCase\TriageServiceInterface;

use function DI\autowire;

class TriageDiDefinitions extends AbstractDependencyInjection
{
    public static function getDependencies(): array
    {
        return [
            TriageServiceInterface::class => autowire(TriageService::class),
        ];
    }
}
