<?php

declare(strict_types=1);

namespace JiRag\Model\Database\Type;

class TicketSummaryItem
{
    private string $ticketKey;
    private string $ticketTitle;
    private string $ticketResolution;
    private ChunkSummaryItemCollection $chunkSummaryItemCollection;

    public function __construct(string $ticketKey, string $ticketTitle, string $ticketResolution)
    {
        $this->ticketKey = $ticketKey;
        $this->ticketTitle = $ticketTitle;
        $this->ticketResolution = $ticketResolution;
        $this->chunkSummaryItemCollection = new ChunkSummaryItemCollection();
    }

    public function addChunkSummaryItem(ChunkSummaryItem $chunkSummaryItem): self
    {
        $this->chunkSummaryItemCollection->addNewChunkedItem($chunkSummaryItem);
        return $this;
    }

    public function getTicketResolution(): string
    {
        return $this->ticketResolution;
    }

    public function getTicketTitle(): string
    {
        return $this->ticketTitle;
    }

    public function getTicketKey(): string
    {
        return $this->ticketKey;
    }

    public function getChunkSummaryItemCollection(): ChunkSummaryItemCollection
    {
        return $this->chunkSummaryItemCollection;
    }
}
