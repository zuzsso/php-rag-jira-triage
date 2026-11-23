<?php

declare(strict_types=1);

namespace JiRag\Model\Database;

use DiManifest\AbstractDependencyInjection;
use JiRag\Model\Database\Service\DbConnexProvider;
use JiRag\Model\Database\Service\VectorRepository;
use JiRag\Model\Database\UseCase\FindVectors;
use JiRag\Model\Database\UseCase\ProvideDbConnex;

use function DI\autowire;

class DatabaseDiDefinitions extends AbstractDependencyInjection
{
    public static function getDependencies(): array
    {
        return [
            ProvideDbConnex::class => autowire(DbConnexProvider::class)->constructor(
                dbHost: getenv('DB_HOST'),
                dbName: getenv('DB_NAME'),
                dbUser: getenv('DB_USER'),
                dbPass: getenv('DB_PASS')
            ),
            FindVectors::class => autowire(VectorRepository::class)
        ];
    }
}