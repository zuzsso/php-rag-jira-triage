<?php

declare(strict_types=1);

namespace JiRag\Model\Database\Service;

use JiRag\Model\Database\Type\EmbeddedTicketChunkDto;
use JiRag\Model\Database\Type\EmbeddedTicketChunkDtoCollection;
use JiRag\Model\Database\UseCase\FindVectors;
use JiRag\Model\Database\UseCase\ProvideDbConnex;
use JsonValidator\UseCase\CheckKeyFloat;
use JsonValidator\UseCase\CheckKeyInteger;
use JsonValidator\UseCase\CheckKeyString;
use Math\Numbers\UseCase\EqualFloats;
use PDO;
use RuntimeException;
use Throwable;

final readonly class VectorRepository implements FindVectors
{
    public function __construct(
        private ProvideDbConnex $provideDbConnex,
        private CheckKeyFloat $checkKeyFloat,
        private CheckKeyString $checkKeyString,
        private CheckKeyInteger $checkKeyInteger,
        private EqualFloats $equalFloats
    ) {
    }

    /**
     * @inheritDoc
     */
    public function findSimilarChunks(
        array $queryVector,
        int $limit = 4,
        float $maxDistance = 0.55
    ): EmbeddedTicketChunkDtoCollection {
        try {
            $vectorJson = json_encode($queryVector, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        $sql = '
            SELECT 
                id,
                ticket_key,
                title,
                resolution,
                content,
                VEC_DISTANCE_COSINE(embedding, VEC_FromText(:query_vector)) AS distance
            FROM jira_knowledge_chunks
            ORDER BY VEC_DISTANCE_COSINE(embedding, VEC_FromText(:query_vector_order)) ASC
            LIMIT :limit
        ';

        $pdo = $this->provideDbConnex->connect();

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':query_vector', $vectorJson);
        $stmt->bindValue(':query_vector_order', $vectorJson);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $closeEnough = array_values(
            array_filter(
                $results,
                static fn (array $row): bool => (float)$row['distance'] <= $maxDistance
            )
        );

        $result = new EmbeddedTicketChunkDtoCollection();

        try {
            foreach ($closeEnough as $r) {
                $result->addEmbeddedTicketChunkDto(
                    EmbeddedTicketChunkDto::constractFromDbRecord(
                        $this->checkKeyFloat,
                        $this->checkKeyString,
                        $this->checkKeyInteger,
                        $this->equalFloats,
                        $r
                    )
                );
            }
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return $result;
    }
}
