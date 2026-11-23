<?php

declare(strict_types=1);

namespace JiRag\Model\Database\Type;

use TypedCollection\AbstractStringAssociativeCollection;
use TypedCollection\Exception\KeyAlreadyExistsException;

class TicketSummaryCollection extends AbstractStringAssociativeCollection
{
    /**
     * @throws KeyAlreadyExistsException
     */
    public function add(TicketSummaryItem $ticketSummaryItem): self
    {
        $this->addStringKeyUntyped($ticketSummaryItem->getTicketKey(), $ticketSummaryItem);
        return $this;
    }

    public function getByStringKey(string $key): TicketSummaryItem
    {
        return $this->getByStringKeyUntyped($key);
    }

    public function getByNumericOffset(int $offset): TicketSummaryItem
    {
        return $this->getByNumericOffsetUntyped($offset);
    }

    public function current(): TicketSummaryItem
    {
        return $this->currentUntyped();
    }
}
