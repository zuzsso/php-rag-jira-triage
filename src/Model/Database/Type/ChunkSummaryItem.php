<?php

declare(strict_types=1);

namespace JiRag\Model\Database\Type;

class ChunkSummaryItem
{
    private float $cosineDistance;
    private string $chunkedText;

    public function __construct(float $cosineDistance, string $chunkedText)
    {
        $this->cosineDistance = $cosineDistance;
        $this->chunkedText = $chunkedText;
    }

    public function getCosineDistance(): float
    {
        return $this->cosineDistance;
    }

    public function getChunkedText(): string
    {
        return $this->chunkedText;
    }
}