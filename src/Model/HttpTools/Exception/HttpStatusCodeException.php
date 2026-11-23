<?php

declare(strict_types=1);

namespace JiRag\Model\HttpTools\Exception;

use Exception;
use RuntimeException;

class HttpStatusCodeException extends Exception
{
    public static function constructForCodeAndMessage(int $httpStatusCode, string $message): self
    {
        if (!($httpStatusCode >= 100 && $httpStatusCode < 600)) {
            throw new RuntimeException("Not a valid http status code: $httpStatusCode");
        }

        if ($httpStatusCode >= 200 && $httpStatusCode < 300) {
            throw new RuntimeException("Not a HTTP status code: $httpStatusCode");
        }

        return new self("The HTTP request returned and error: Code: $httpStatusCode. Message: $message");
    }
}