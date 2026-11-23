<?php

declare(strict_types=1);

namespace JiRag\Model\Triage\Service;

use JiRag\Model\Database\Type\EmbeddedTicketChunkDtoCollection;
use JiRag\Model\Database\UseCase\FindVectors;
use JiRag\Model\LLM\UseCase\LLMProviderInterface;
use JiRag\Model\Triage\Type\TriageResult;
use JiRag\Model\Triage\UseCase\TriageServiceInterface;
use RuntimeException;
use Throwable;

final readonly class TriageService implements TriageServiceInterface
{
    public function __construct(
        private LLMProviderInterface $llm,
        private FindVectors $vectorRepository
    ) {
    }

    /**
     * @inheritDoc
     */
    public function triageIncomingIssue(string $issueDescription): TriageResult
    {
        $cleanQuery = mb_substr(trim($issueDescription), 0, 2000);

        // 1: Convert incoming bug description into a 768-dim vector via LLMProviderInterface
        $queryVector = $this->llm->createEmbedding($cleanQuery);

        // 2: Query MariaDB's HNSW index for the closest historical Jira tickets
        $matches = $this->vectorRepository->findSimilarChunks($queryVector);

        // 3: Deterministic guardrail: short-circuit if no close historical precedent exists
        if (count($matches) === 0) {
            return new TriageResult(
                'No sufficiently similar historical Jira tickets were found in the knowledge base.',
                'Unknown / New Investigation Required',
                'Assign to engineering triage queue for fresh root-cause analysis.',
                0.0,
                false,
                (new EmbeddedTicketChunkDtoCollection())
            );
        }

        $contextBlock = $matches->serializeAsPromptContextBlock();

        $guardrailedSystemPrompt = <<<PROMPT
        You are a Senior Software Triage Engineer.
        Analyze the incoming bug report strictly using ONLY the historical Jira ticket records inside <context>.
        Do not invent ticket keys or guess fixes not supported by <context>.

        <context>
        {$contextBlock}
        </context>
        PROMPT;

        // 5: Enforce deterministic JSON Schema output from the LLM
        $jsonSchema = [
            'type' => 'object',
            'properties' => [
                'summary_analysis' => ['type' => 'string'],
                'likely_component' => ['type' => 'string'],
                'recommended_action' => ['type' => 'string'],
                'confidence_score' => ['type' => 'number'],
            ],
            'required' => ['summary_analysis', 'likely_component', 'recommended_action', 'confidence_score'],
        ];

        $rawJson = $this
            ->llm
            ->chatCompletion([
                ['role' => 'system', 'content' => $guardrailedSystemPrompt],
                ['role' => 'user', 'content' => "<bug_report>\n$cleanQuery\n</bug_report>"],
            ], $jsonSchema);

        try {
            $parsed = json_decode($rawJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            throw new RuntimeException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return new TriageResult(
            (string)($parsed['summary_analysis'] ?? 'Analysis unavailable.'),
            (string)($parsed['likely_component'] ?? 'General'),
            (string)($parsed['recommended_action'] ?? 'Review cited tickets.'),
            (float)($parsed['confidence_score'] ?? 0.5),
            true,
            $matches
        );
    }
}