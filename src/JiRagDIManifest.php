<?php

declare(strict_types=1);

namespace JiRag;

use DiManifest\AbstractDependencyInjection;
use JiRag\Model\Database\DatabaseDiDefinitions;
use JiRag\Model\HttpTools\HttpToolsDiManifest;
use JiRag\Model\LLM\LlmDiManifest;
use JiRag\Model\TextTools\TextToolsDiDefinitions;
use JiRag\Model\Triage\TriageDiDefinitions;
use JsonValidator\JsonValidatorDiManifest;
use Math\MathDiManifest;

class JiRagDIManifest extends AbstractDependencyInjection
{
    public static function getDependencies(): array
    {
        return array_merge(
            LlmDiManifest::getDependencies(),
            HttpToolsDiManifest::getDependencies(),
            JsonValidatorDiManifest::getDependencies(),
            TriageDiDefinitions::getDependencies(),
            DatabaseDiDefinitions::getDependencies(),
            MathDiManifest::getDependencies(),
            TextToolsDiDefinitions::getDependencies()
        );
    }
}
