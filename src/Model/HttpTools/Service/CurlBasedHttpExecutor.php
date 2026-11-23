<?php

declare(strict_types=1);

namespace JiRag\Model\HttpTools\Service;

use JiRag\Model\HttpTools\Exception\HttpStatusCodeException;
use JiRag\Model\HttpTools\UseCase\ExecuteHttp;
use RuntimeException;
use Throwable;

class CurlBasedHttpExecutor implements ExecuteHttp
{
    /**
     * @inheritDoc
     */
    public function post(string $url, array $payload, array $httpHeaders): array
    {
        $ch = curl_init($url);

        try {
            $fields = json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => $httpHeaders,
            CURLOPT_POSTFIELDS => $fields,
        ]);

        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            throw new RuntimeException("POST failed: $curlError");
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw HttpStatusCodeException::constructForCodeAndMessage($httpCode, $raw);
        }

        try {
            return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }


    /**
     * @inheritDoc
     */
    public function get(string $url, array $httpHeaders): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $httpHeaders,
        ]);

        $rawResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || $rawResponse === false) {
            throw HttpStatusCodeException::constructForCodeAndMessage($httpCode, $rawResponse);
        }

        try {
            return json_decode($rawResponse, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
