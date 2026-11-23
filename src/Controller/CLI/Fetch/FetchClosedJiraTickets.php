<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use JiRag\Model\HttpTools\UseCase\ExecuteHttp;
use JiRag\Model\LLM\Type\AtlassianTicketDto;
use JiRag\Model\LLM\Type\AtlassianTicketDtoCollection;
use JsonValidator\UseCase\CheckKeyString;

include __DIR__ . '/../../../../public/index.php';

$outputDir = __DIR__ . '/../../../../resources/fixtures';

global $diContainer;

/** @var ExecuteHttp $executeHttp */
$executeHttp = $diContainer->get(ExecuteHttp::class);

/** @var CheckKeyString $checkKeyString */
$checkKeyString = $diContainer->get(CheckKeyString::class);


if (!is_dir($outputDir) && !mkdir($outputDir, 0755, true) && !is_dir($outputDir)) {
    throw new RuntimeException(sprintf('Directory "%s" was not created', $outputDir));
}

$outputFile = $outputDir . '/atlassian_jira_tickets.json';

$baseUrl = 'https://jira.atlassian.com/rest/api/2/search';

$jql = '
    project IN (JRACLOUD, JRASERVER)
    AND issuetype = Bug
    AND resolution = Fixed
    ORDER BY resolutiondate DESC, key ASC
';

$fields = 'summary,description,resolution,comment';

$targetCount = 250;
$cleanedTickets = new AtlassianTicketDtoCollection();
$startAt = 0;
$maxResults = 100;
$page = 1;

echo "Fetching resolved bugs from Atlassian Jira Data Center API v2..." . PHP_EOL;

while (count($cleanedTickets) < $targetCount) {
    $params = [
        'jql' => $jql,
        'fields' => $fields,
        'startAt' => $startAt,
        'maxResults' => $maxResults,
    ];

    $url = $baseUrl . '?' . http_build_query($params);

    $httpHeaders = [
        'Accept: application/json',
        'User-Agent: JiRag-Portfolio-Extractor/1.0',
    ];

    $data = $executeHttp->get($url, $httpHeaders);
    $issues = $data['issues'] ?? [];

    if ($issues === []) {
        break;
    }

    echo "Page $page: retrieved " . count($issues) . " raw issues..." . PHP_EOL;

    foreach ($issues as $issue) {
        $key = $issue['key'];
        $summary = trim($issue['fields']['summary'] ?? '');
        $description = stripJiraMarkup((string)($issue['fields']['description'] ?? ''));
        $resolution = $issue['fields']['resolution']['name'] ?? 'Resolved';

        $comments = $issue['fields']['comment']['comments'] ?? [];
        $recentComments = array_slice($comments, -2);
        $commentText = '';

        foreach ($recentComments as $comment) {
            $parsedComment = stripJiraMarkup((string)($comment['body'] ?? ''));
            if ($parsedComment !== '') {
                $commentText .= PHP_EOL . "Engineer Note: " . $parsedComment;
            }
        }

        if (mb_strlen($description) < 50) {
            continue;
        }

        $cleanedTickets->addNewTicket(
            AtlassianTicketDto::createFromIndividualComponents(
                $checkKeyString,
                $key,
                $summary,
                $resolution,
                $description,
                $commentText
            )
        );

        if (count($cleanedTickets) >= $targetCount) {
            break 2;
        }
    }

    $startAt += $maxResults;
    $page++;
    usleep(300_000);
}

$extractedRecords = [];

foreach ($cleanedTickets as $ticket) {
    $extractedRecords[] = $ticket->fixtureSerialize();
}

file_put_contents($outputFile, json_encode($extractedRecords, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

echo "Done! Saved " . count($extractedRecords) . " cleaned tickets to " . realpath($outputFile) . PHP_EOL;

function stripJiraMarkup(string $textWithMarkup): string
{
    $text = preg_replace('/\{[^}]+\}/', ' ', $textWithMarkup) ?? $textWithMarkup;
    $text = preg_replace('/\[~[^\]]+\]/', '@engineer', $text) ?? $text;
    return trim(preg_replace('/\s+/', ' ', $text) ?? '');
}
