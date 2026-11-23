<?php

declare(strict_types=1);

namespace JiRag\Model\Triage\UseCase;

use JiRag\Model\HttpTools\Exception\HttpStatusCodeException;
use JiRag\Model\Triage\Type\TriageResult;

interface TriageServiceInterface
{
    /**
     * @throws HttpStatusCodeException
     */
    public function triageIncomingIssue(string $issueDescription): TriageResult;
}