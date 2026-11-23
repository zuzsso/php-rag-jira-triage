<?php

declare(strict_types=1);

namespace JiRag\Model\LLM\Service;

use JiRag\Model\HttpTools\UseCase\ExecuteHttp;
use JiRag\Model\LLM\UseCase\LLMProviderInterface;
use RuntimeException;

final readonly class OllamaLocalServerLlm implements LLMProviderInterface
{
    public function __construct(
        private ExecuteHttp $executeHttp,
        private string $baseUrl,
        private string $embeddingModel,
        private string $chatModel
    ) {
    }

    /**
     * @inheritDoc
     */
    public function createEmbedding(string $text): array
    {
        $payload = [
            'model' => $this->embeddingModel,
            'input' => $text,
        ];

        $httpHeaders = ['Content-Type: application/json'];
        $url = rtrim($this->baseUrl, '/') . '/api/embed';

        $response = $this->executeHttp->post($url, $payload, $httpHeaders);

        if (!isset($response['embeddings'][0]) || !is_array($response['embeddings'][0])) {
            throw new RuntimeException('Invalid embedding response from local Ollama server.');
        }

        return $response['embeddings'][0];
    }

    /**
     * @inheritDoc
     */
    public function createEmbeddingsBatch(array $texts): array
    {
        if ($texts === []) {
            return [];
        }

        $payload = [
            'model' => $this->embeddingModel,
            'input' => array_values($texts),
        ];

        $httpHeaders = ['Content-Type: application/json'];
        $url = rtrim($this->baseUrl, '/') . '/api/embed';

        $response = $this->executeHttp->post($url, $payload, $httpHeaders);

        if (!isset($response['embeddings']) || !is_array($response['embeddings'])) {
            throw new RuntimeException('Invalid batch embedding response from local Ollama server.');
        }

        return $response['embeddings'];
    }

    /**
     * @inheritDoc
     */
    public function chatCompletion(array $messages, ?array $jsonSchema = null): string
    {
        $payload = [
            'model' => $this->chatModel,
            'messages' => $messages,
            'stream' => false,
            'options' => [
                // This configuration gives more deterministic confidence scores
                // 'temperature' => 0.2,
                'temperature' => 0.0,
                'seed' => 42
            ],
        ];

        if ($jsonSchema !== null) {
            $payload['format'] = $jsonSchema;
        }

        $httpHeaders = ['Content-Type: application/json'];
        $url = rtrim($this->baseUrl, '/') . '/api/chat';

        $response = $this->executeHttp->post($url, $payload, $httpHeaders);

        if (!isset($response['message']['content'])) {
            throw new RuntimeException('Invalid chat completion response from local Ollama server.');
        }

        return (string)$response['message']['content'];
    }
}