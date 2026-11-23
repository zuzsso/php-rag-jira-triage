<?php

declare(strict_types=1);

namespace JiRag\Model\TextTools\UseCase;

interface  FormatPlainText
{
    public function formatConsoleText(string $text, int $width = 80, int $indent = 3): string;
}