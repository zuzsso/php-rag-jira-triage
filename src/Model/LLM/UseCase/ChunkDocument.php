<?php

declare(strict_types=1);

namespace JiRag\Model\LLM\UseCase;

interface ChunkDocument
{
    /**
     * @return string[]
     */
    public function chunk(string $text): array;
}