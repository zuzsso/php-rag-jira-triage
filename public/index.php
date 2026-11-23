<?php

/** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

use DI\ContainerBuilder;
use JiRag\JiRagDIManifest;

require_once __DIR__ . '/../vendor/autoload.php';

$builder = new ContainerBuilder();
$builder->useAutowiring(true);

$diDefinitions = JiRagDIManifest::getDependencies();

$builder->addDefinitions($diDefinitions);

/** @noinspection PhpUnhandledExceptionInspection */
$diContainer = $builder->build();
$GLOBALS['diContainer'] = $diContainer; // Make sure the diContainer is not modified beyond this point
