<?php

declare(strict_types=1);

namespace JiRag\Model\Database\UseCase;

use JiRag\Model\Database\Type\EmbeddedTicketChunkDtoCollection;

interface FindVectors
{
    /**
     * Performs an Approximate Nearest Neighbor (ANN) search using MariaDB's native HNSW VECTOR INDEX.
     *
     * @param float[] $queryVector
     */
    public function findSimilarChunks(
        array $queryVector,
        int $limit = 4,
        float $maxDistance = 0.55
    ): EmbeddedTicketChunkDtoCollection;
}