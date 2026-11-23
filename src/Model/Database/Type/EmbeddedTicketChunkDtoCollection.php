<?php

declare(strict_types=1);

namespace JiRag\Model\Database\Type;

use RuntimeException;
use TypedCollection\AbstractStringAssociativeCollection;
use TypedCollection\Exception\KeyAlreadyExistsException;

class EmbeddedTicketChunkDtoCollection extends AbstractStringAssociativeCollection
{
    /**
     * @throws KeyAlreadyExistsException
     */
    public function addEmbeddedTicketChunkDto(EmbeddedTicketChunkDto $embeddedTicketChunkDto): self
    {
        $this->addIntKeyUntyped($embeddedTicketChunkDto->getId(), $embeddedTicketChunkDto);
        return $this;
    }

    public function getByStringKey(string $key): EmbeddedTicketChunkDto
    {
        return $this->getByStringKeyUntyped($key);
    }

    public function getByNumericOffset(int $offset): EmbeddedTicketChunkDto
    {
        return $this->getByNumericOffsetUntyped($offset);
    }

    public function current(): EmbeddedTicketChunkDto
    {
        return $this->currentUntyped();
    }

    public function serializeAsPromptContextBlock(): string
    {
        $result = '';

        foreach ($this as $chunk) {
            $result .= sprintf(
                "[%s | %s | Resolution: %s | Cosine Distance: %.3f]\n%s\n\n",
                $chunk->getTicketKey(),
                $chunk->getTitle(),
                $chunk->getResolution(),
                $chunk->getDistance(),
                $chunk->getContent()
            );
        }

        return $result;
    }

    public function serialize(): array
    {
        $result = [];

        foreach ($this as $chunk) {
            $result [] = $chunk->serialize();
        }

        return $result;
    }

    public function sumarize(): TicketSummaryCollection
    {
        $result = [];

        foreach ($this as $chunk) {
            $ticketKey = $chunk->getTicketKey();

            /** @var TicketSummaryItem | null $ticketKeyObj */
            $ticketKeyObj = $result[$ticketKey] ?? null;

            if ($ticketKeyObj === null) {
                $ticketKeyObj = new TicketSummaryItem($ticketKey, $chunk->getTitle(), $chunk->getResolution());
                $result[$ticketKey] = $ticketKeyObj;
            }

            $ticketKeyObj->addChunkSummaryItem(new ChunkSummaryItem($chunk->getDistance(), $chunk->getContent()));
        }

        $resultFinal = new TicketSummaryCollection();

        try {
            foreach ($result as $resultItem) {
                $resultFinal->add($resultItem);
            }
        } catch (KeyAlreadyExistsException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return $resultFinal;
    }
}
