<?php

declare(strict_types=1);

namespace JiRag\Model\Database\Service;

use JiRag\Model\Database\UseCase\ProvideDbConnex;
use PDO;
use PDOException;

class DbConnexProvider implements ProvideDbConnex
{
    private ?PDO $connex = null;
    private string $dsn;

    public function __construct(
        private readonly string $dbHost,
        private readonly string $dbName,
        private readonly string $dbUser,
        private readonly string $dbPass
    ) {
        $this->dsn = "mysql:host=$this->dbHost;dbname=$this->dbName;charset=utf8mb4";
    }

    public function connect(): PDO
    {
        if ($this->connex !== null) {
            try {
                $this->connex->query('SELECT 1');
                return $this->connex;
            } catch (PDOException $e) {
                $driverCode = (int)($e->errorInfo[1] ?? 0);

                if (!in_array($driverCode, [2006, 2013], true)) {
                    throw $e;
                }
            }
        }

        return $this->connex = new PDO(
            $this->dsn,
            $this->dbUser,
            $this->dbPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
        );
    }
}
