<?php

declare(strict_types=1);

namespace JiRag\Model\LLM\Service;

use JiRag\Model\LLM\UseCase\ChunkDocument;

final readonly class DocumentChunker implements ChunkDocument
{
    public function __construct(
        private int $chunkSize,
        private int $overlap
    ) {
    }

    /**
     * @inheritDoc
     */
    public function chunk(string $text): array
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');
        $length = mb_strlen($text);

        if ($length <= $this->chunkSize) {
            return [$text];
        }

        $chunks = [];
        $start = 0;

        while ($start < $length) {
            $slice = mb_substr($text, $start, $this->chunkSize);

            if ($start + $this->chunkSize < $length) {
                $lastSpace = mb_strrpos($slice, ' ');
                if ($lastSpace !== false && $lastSpace > ($this->chunkSize * 0.5)) {
                    $slice = mb_substr($slice, 0, $lastSpace);
                }
            }

            $chunks[] = trim($slice);
            $advance = mb_strlen($slice) - $this->overlap;
            $start += max(1, $advance);
        }

        return $chunks;
    }
}