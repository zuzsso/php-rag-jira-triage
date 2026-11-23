<?php

declare(strict_types=1);

namespace JiRag\Model\Database\UseCase;

use PDO;

interface ProvideDbConnex
{
    public function connect(): PDO;
}
