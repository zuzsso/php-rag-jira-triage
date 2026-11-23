<?php

declare(strict_types=1);

namespace JiRag\Model\Database\Type;

use JsonValidator\Exception\EntryEmptyException;
use JsonValidator\Exception\EntryMissingException;
use JsonValidator\Exception\IncorrectParametrizationException;
use JsonValidator\Exception\InvalidIntegerValueException;
use JsonValidator\Exception\ValueNotAFloatException;
use JsonValidator\Exception\ValueNotAStringException;
use JsonValidator\Exception\ValueTooBigException;
use JsonValidator\Exception\ValueTooSmallException;
use JsonValidator\Types\Range\FloatRange;
use JsonValidator\UseCase\CheckKeyFloat;
use JsonValidator\UseCase\CheckKeyInteger;
use JsonValidator\UseCase\CheckKeyString;
use Math\Numbers\UseCase\EqualFloats;
use JiRag\Model\Database\Helper\DtoEntryDictionary as DTOD;

readonly class EmbeddedTicketChunkDto
{
    private function __construct(
        private int $id,
        private string $ticketKey,
        private string $title,
        private string $resolution,
        private string $content,
        private float $distance
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTicketKey(): string
    {
        return $this->ticketKey;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getResolution(): string
    {
        return $this->resolution;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getDistance(): float
    {
        return $this->distance;
    }

    /**
     * @throws EntryEmptyException
     * @throws EntryMissingException
     * @throws IncorrectParametrizationException
     * @throws ValueNotAFloatException
     * @throws ValueNotAStringException
     * @throws ValueTooBigException
     * @throws ValueTooSmallException
     * @throws InvalidIntegerValueException
     */
    public static function constractFromDbRecord(
        CheckKeyFloat $checkKeyFloat,
        CheckKeyString $checkKeyString,
        CheckKeyInteger $checkKeyInteger,
        EqualFloats $equalFloats,
        array $record
    ): self {
        $checkKeyString
            ->required(DTOD::TICKET_KEY, $record)
            ->required(DTOD::TITLE_KEY, $record)
            ->required(DTOD::RESOLUTION_KEY, $record)
            ->required(DTOD::CONTENT_KEY, $record);

        $checkKeyFloat
            ->required(DTOD::DISTANCE_KEY, $record)
            ->withinRange(DTOD::DISTANCE_KEY, $record, new FloatRange($equalFloats, 0, 2));

        $checkKeyInteger->required(DTOD::ID_KEY, $record);

        return new self(
            (int)$record[DTOD::ID_KEY],
            trim($record[DTOD::TICKET_KEY]),
            trim($record[DTOD::TITLE_KEY]),
            trim($record[DTOD::RESOLUTION_KEY]),
            trim($record[DTOD::CONTENT_KEY]),
            (float)$record[DTOD::DISTANCE_KEY]
        );
    }

    public function serialize(): array
    {
        return [
            DTOD::ID_KEY => $this->id,
            DTOD::TICKET_KEY => $this->ticketKey,
            DTOD::TITLE_KEY => $this->title,
            DTOD::RESOLUTION_KEY => $this->resolution,
            DTOD::CONTENT_KEY => $this->content,
            DTOD::DISTANCE_KEY => $this->distance
        ];
    }
}
