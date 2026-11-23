<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use JiRag\Model\Database\UseCase\ProvideDbConnex;
use JiRag\Model\LLM\Type\AtlassianTicketDto;
use JiRag\Model\LLM\UseCase\ChunkDocument;
use JiRag\Model\LLM\UseCase\LLMProviderInterface;
use JsonValidator\UseCase\CheckKeyString;

require __DIR__ . '/../../../../public/index.php';

global $diContainer;

/** @var LLMProviderInterface $llmProviderInterface */
$llmProviderInterface = $diContainer->get(LLMProviderInterface::class);

/** @var ChunkDocument $chunkDocument */
$chunkDocument = $diContainer->get(ChunkDocument::class);

/** @var CheckKeyString $checkKeyString */
$checkKeyString = $diContainer->get(CheckKeyString::class);

/** @var ProvideDbConnex $provideDbConnex */
$provideDbConnex = $diContainer->get(ProvideDbConnex::class);

$force = in_array('--force', $argv, true);

$fixturePath = __DIR__ . '/../../../../resources/fixtures/atlassian_jira_tickets.json';

if (!file_exists($fixturePath)) {
    fwrite(STDERR, "Fixture file not found at $fixturePath");
    exit(1);
}

$pdo = $provideDbConnex->connect();

$existingCount = (int)$pdo->query('SELECT COUNT(*) FROM jira_knowledge_chunks')->fetchColumn();

if ($existingCount > 0 && !$force) {
    echo "Database already contains $existingCount chunks. Use --force to rebuild." . PHP_EOL;
    exit(0);
}

if ($force) {
    $pdo->exec('TRUNCATE TABLE jira_knowledge_chunks');
}

$tickets = json_decode(file_get_contents($fixturePath), true, 512, JSON_THROW_ON_ERROR);

$stmt = $pdo->prepare(
    "
        INSERT INTO jira_knowledge_chunks (
            ticket_key, 
            title, 
            resolution, 
            chunk_index, 
            content, 
            embedding
        ) VALUES (
            :key, 
            :title, 
            :resolution, 
            :idx, 
            :content, 
            VEC_FromText(:vector)
        )
    "
);

echo "Seeding MariaDB from fixtures/atlassian_tickets.json (" . count($tickets) . " tickets)..." . PHP_EOL;

$totalChunks = 0;

$totaltickets = count($tickets);

$processedTickets = 0;

echo PHP_EOL;


foreach ($tickets as $rawTicket) {
    $ticket = AtlassianTicketDto::createFromAssociativeArray($checkKeyString, $rawTicket);

    $chunks = $chunkDocument->chunk($ticket->getFullText());

    $vectors = $llmProviderInterface->createEmbeddingsBatch($chunks);

    foreach ($chunks as $index => $chunkText) {
        $stmt->execute([
            ':key' => $ticket->getKey(),
            ':title' => $ticket->getTitle(),
            ':resolution' => $ticket->getResolution(),
            ':idx' => $index,
            ':content' => $chunkText,
            ':vector' => json_encode($vectors[$index], JSON_THROW_ON_ERROR),
        ]);

        $totalChunks++;
    }
    $processedTickets++;
    echo "\r$processedTickets of $totaltickets               ";
}

echo PHP_EOL . "Done! Inserted $totalChunks vector chunks into jira_knowledge_chunks." . PHP_EOL;
