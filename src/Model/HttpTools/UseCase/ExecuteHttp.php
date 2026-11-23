<?php

declare(strict_types=1);

namespace JiRag\Model\HttpTools\UseCase;

use JiRag\Model\HttpTools\Exception\HttpStatusCodeException;

interface ExecuteHttp
{
    /**
     * Executes POST and returns the response body in JSON format
     *
     * @throws HttpStatusCodeException
     */
    public function post(string $url, array $payload, array $httpHeaders): array;

    /**
     * Executes GET and returns the response body in JSON format
     *
     * @throws HttpStatusCodeException
     */
    public function get(string $url, array $httpHeaders): array;
}
