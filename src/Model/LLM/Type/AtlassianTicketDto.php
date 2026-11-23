<?php

declare(strict_types=1);

namespace JiRag\Model\LLM\Type;

use JsonValidator\Exception\EntryEmptyException;
use JsonValidator\Exception\EntryMissingException;
use JsonValidator\Exception\IncorrectParametrizationException;
use JsonValidator\Exception\OptionalPropertyNotAStringException;
use JsonValidator\Exception\ValueNotAStringException;
use JsonValidator\Exception\ValueTooBigException;
use JsonValidator\Exception\ValueTooSmallException;
use JsonValidator\Types\Range\StringByteLengthRange;
use JsonValidator\UseCase\CheckKeyString;
use JiRag\Model\Database\Helper\DtoEntryDictionary as DTOD;

class AtlassianTicketDto
{
    private string $key;
    private string $title;
    private string $resolution;
    private string $description;
    private ?string $comment;

    private function __construct(
        string $key,
        string $title,
        string $resolution,
        string $description,
        ?string $comment
    ) {
        $this->key = $key;
        $this->title = $title;
        $this->resolution = $resolution;
        $this->description = $description;
        $this->comment = $comment;

        $this->comment = trim($this->comment ?? '');
        if ($this->comment === '') {
            $this->comment = null;
        }
    }

    public function fixtureSerialize(): array
    {
        return [
            DTOD::TICKET_KEY => $this->key,
            DTOD::TITLE_KEY => $this->title,
            DTOD::RESOLUTION_KEY => $this->resolution,
            DTOD::DESCRIPTION_KEY => $this->description,
            DTOD::COMMENT_KEY => $this->comment
        ];
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getResolution(): string
    {
        return $this->resolution;
    }

    public function getFullText(): string
    {
        return trim(
            "Ticket $this->key: $this->title\n\nProblem:\n$this->description\n$this->comment"
        );
    }

    /**
     * @throws EntryEmptyException
     * @throws EntryMissingException
     * @throws IncorrectParametrizationException
     * @throws ValueNotAStringException
     * @throws ValueTooBigException
     * @throws ValueTooSmallException
     * @throws OptionalPropertyNotAStringException
     */
    public static function createFromAssociativeArray(CheckKeyString $checkKeyString, array $payload): self
    {
        $checkKeyString
            ->required(DTOD::TICKET_KEY, $payload)
            ->byteLengthRange(DTOD::TICKET_KEY, $payload, new StringByteLengthRange(5, null))
            ->required(DTOD::TITLE_KEY, $payload)
            ->byteLengthRange(DTOD::TITLE_KEY, $payload, new StringByteLengthRange(10, null))
            ->required(DTOD::RESOLUTION_KEY, $payload)
            ->required(DTOD::DESCRIPTION_KEY, $payload)
            ->byteLengthRange(DTOD::DESCRIPTION_KEY, $payload, new StringByteLengthRange(5, null))
            ->optional(DTOD::COMMENT_KEY, $payload)
            ->byteLengthRange(DTOD::COMMENT_KEY, $payload, new StringByteLengthRange(1, null), false);

        return new self(
            $payload[DTOD::TICKET_KEY],
            $payload[DTOD::TITLE_KEY],
            $payload[DTOD::RESOLUTION_KEY],
            $payload[DTOD::DESCRIPTION_KEY],
            $payload[DTOD::COMMENT_KEY]
        );
    }

    /**
     * @throws EntryEmptyException
     * @throws EntryMissingException
     * @throws IncorrectParametrizationException
     * @throws OptionalPropertyNotAStringException
     * @throws ValueNotAStringException
     * @throws ValueTooBigException
     * @throws ValueTooSmallException
     */
    public static function createFromIndividualComponents(
        CheckKeyString $checkKeyString,
        string $key,
        string $title,
        string $resolution,
        string $description,
        ?string $comment
    ): self {
        $sanitizedComment = trim($comment ?? '');

        if ($sanitizedComment === '') {
            $sanitizedComment = null;
        }

        $associative = [
            DTOD::TICKET_KEY => $key,
            DTOD::TITLE_KEY => $title,
            DTOD::RESOLUTION_KEY => $resolution,
            DTOD::DESCRIPTION_KEY => $description,
            DTOD::COMMENT_KEY => $sanitizedComment
        ];

        return self::createFromAssociativeArray($checkKeyString, $associative);
    }
}
