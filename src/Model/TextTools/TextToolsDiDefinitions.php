<?php

declare(strict_types=1);

namespace JiRag\Model\TextTools;

use DiManifest\AbstractDependencyInjection;
use JiRag\Model\TextTools\Service\PlainTextFormatter;
use JiRag\Model\TextTools\UseCase\FormatPlainText;

use function DI\autowire;

class TextToolsDiDefinitions extends AbstractDependencyInjection
{
    public static function getDependencies(): array
    {
        return [
            FormatPlainText::class => autowire(PlainTextFormatter::class)
        ];
    }
}
