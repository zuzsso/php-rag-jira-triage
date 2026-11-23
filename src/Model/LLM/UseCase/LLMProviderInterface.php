<?php

declare(strict_types=1);

namespace JiRag\Model\LLM\UseCase;

use JiRag\Model\HttpTools\Exception\HttpStatusCodeException;

interface LLMProviderInterface
{
    /**
     * Converts a text string into a float vector embedding.
     *
     * @throws HttpStatusCodeException
     * @return float[]
     */
    public function createEmbedding(string $text): array;

    /**
     * Embeds multiple text chunks in a single HTTP request to Ollama.
     *
     * @throws HttpStatusCodeException
     *
     * @param string[] $texts
     *
     * @return array<int, float[]>
     */
    public function createEmbeddingsBatch(array $texts): array;

    /**
     * Sends chat messages to the LLM and returns the assistant's response.
     *
     * @throws HttpStatusCodeException
     *
     * @param array<int, array{role: string, content: string}> $messages
     * @param array<string, mixed>|null                        $jsonSchema Optional structured output schema
     */
    public function chatCompletion(array $messages, ?array $jsonSchema = null): string;
}
