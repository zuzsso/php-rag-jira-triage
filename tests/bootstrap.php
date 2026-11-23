<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use DI\ContainerBuilder;
use JiRag\JiRagDIManifest;

require_once __DIR__ . '/../vendor/autoload.php';

$builder = new ContainerBuilder();

$builder->useAutowiring(true);

$builder->addDefinitions(JiRagDIManifest::getDependencies());

$GLOBALS['diContainer'] = $builder->build();
