<?php

declare(strict_types=1);

namespace JiRag\Model\LLM;

use DiManifest\AbstractDependencyInjection;
use JiRag\Model\LLM\Service\DocumentChunker;
use JiRag\Model\LLM\Service\OllamaLocalServerLlm;
use JiRag\Model\LLM\UseCase\ChunkDocument;
use JiRag\Model\LLM\UseCase\LLMProviderInterface;

use function DI\autowire;

class LlmDiManifest extends AbstractDependencyInjection
{
    public static function getDependencies(): array
    {
        return [
            LLMProviderInterface::class => autowire(OllamaLocalServerLlm::class)
                ->constructor(
                    baseUrl       : 'http://ollama:11434',
                    embeddingModel: 'nomic-embed-text',
                    chatModel     : 'llama3.2:3b',
                ),
            ChunkDocument::class => autowire(DocumentChunker::class)->constructor(
                chunkSize: 1800,
                overlap  : 150
            ),
        ];
    }
}
