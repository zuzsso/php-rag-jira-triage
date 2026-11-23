<?php

declare(strict_types=1);

namespace JiRag\Model\LLM\Type;

use TypedCollection\AbstractStringAssociativeCollection;

class AtlassianTicketDtoCollection extends AbstractStringAssociativeCollection
{
    public function addNewTicket(AtlassianTicketDto $ticketDto): self
    {
        $this->addUnindexedUntypedElement($ticketDto);

        return $this;
    }

    public function getByStringKey(string $key): AtlassianTicketDto
    {
        return $this->getByStringKeyUntyped($key);
    }

    public function getByNumericOffset(int $offset): AtlassianTicketDto
    {
        return $this->getByNumericOffsetUntyped($offset);
    }

    public function current(): AtlassianTicketDto
    {
        return $this->currentUntyped();
    }
}