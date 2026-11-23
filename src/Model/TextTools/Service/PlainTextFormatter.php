<?php

declare(strict_types=1);

namespace JiRag\Model\TextTools\Service;

use InvalidArgumentException;
use JiRag\Model\TextTools\UseCase\FormatPlainText;

class PlainTextFormatter implements FormatPlainText
{
    public function formatConsoleText(string $text, int $width = 80, int $indent = 3): string
    {
        if ($indent < 0 || $width <= $indent) {
            throw new InvalidArgumentException('Width must exceed the non-negative indent.');
        }

        $padding = str_repeat(' ', $indent);
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $wrapped = wordwrap($text, $width - $indent);

        return implode(
            PHP_EOL,
            array_map(
                static fn (string $line): string => $line === '' ? '' : $padding . $line,
                explode("\n", $wrapped)
            )
        );
    }

}