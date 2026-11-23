<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use JiRag\Model\TextTools\UseCase\FormatPlainText;
use JiRag\Model\Triage\UseCase\TriageServiceInterface;

require __DIR__ . '/../../../../public/index.php';

$query = trim(implode(' ', array_slice($argv, 1)));

if ($query === '') {
    fwrite(STDERR, "Usage: php bin/triage.php \"<incident description or bug report>\"" . PHP_EOL);
    exit(1);
}

global $diContainer;

/** @var TriageServiceInterface $triageServiceInterface */
$triageServiceInterface = $diContainer->get(TriageServiceInterface::class);

/** @var FormatPlainText $formatPlainText */
$formatPlainText = $diContainer->get(FormatPlainText::class);

$start = microtime(true);
$result = $triageServiceInterface->triageIncomingIssue($query);
$elapsedMs = round((microtime(true) - $start) * 1000, 1);

echo PHP_EOL;

echo $result->formatForCli($formatPlainText, $elapsedMs) . PHP_EOL;
