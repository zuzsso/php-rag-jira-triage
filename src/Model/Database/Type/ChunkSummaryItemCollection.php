<?php

declare(strict_types=1);

namespace JiRag\Model\Database\Type;

use TypedCollection\AbstractStringAssociativeCollection;

class ChunkSummaryItemCollection extends AbstractStringAssociativeCollection
{
    public function addNewChunkedItem(ChunkSummaryItem $item): self
    {
        $this->addUnindexedUntypedElement($item);
        return $this;
    }

    public function getByStringKey(string $key): ChunkSummaryItem
    {
        return $this->getByStringKeyUntyped($key);
    }

    public function getByNumericOffset(int $offset): ChunkSummaryItem
    {
        return $this->getByNumericOffsetUntyped($offset);
    }

    public function current(): ChunkSummaryItem
    {
        return $this->currentUntyped();
    }
}