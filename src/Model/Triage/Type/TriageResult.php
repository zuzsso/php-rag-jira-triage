<?php

declare(strict_types=1);

namespace JiRag\Model\Triage\Type;

use InvalidArgumentException;
use JiRag\Model\Database\Type\EmbeddedTicketChunkDtoCollection;
use JiRag\Model\TextTools\UseCase\FormatPlainText;

final readonly class TriageResult
{
    private string $summaryAnalysis;
    private string $likelyComponent;
    private string $recommendedAction;
    private float $confidenceScore;
    private bool $grounded;
    private EmbeddedTicketChunkDtoCollection $citations;

    public function __construct(
        string $summaryAnalysis,
        string $likelyComponent,
        string $recommendedAction,
        float $confidenceScore,
        bool $grounded,
        EmbeddedTicketChunkDtoCollection $citations
    ) {
        $this->summaryAnalysis = $summaryAnalysis;
        $this->likelyComponent = $likelyComponent;
        $this->recommendedAction = $recommendedAction;
        $this->confidenceScore = $confidenceScore;
        $this->grounded = $grounded;
        $this->citations = $citations;
    }

    public function getConfidenceScorePercentage(): float
    {
        return $this->confidenceScore * 100;
    }

    public function formatForCli(FormatPlainText $formatPlainText, float $elapsedMs): string
    {
        $result = '';

        if ($elapsedMs < 0) {
            throw new InvalidArgumentException("Implausible duration: $elapsedMs");
        }

        $groundedLiteral = $this->grounded ? 'YES (Verified in Historical Jira Data)' : 'NO (No Close Match Found)';
        $result .= "====================================================================" . PHP_EOL;
        $result .= "   JIRAG SEMANTIC TRIAGE REPORT ($elapsedMs ms)" . PHP_EOL;
        $result .= "====================================================================" . PHP_EOL;
        $result .= "   Grounded   : $groundedLiteral" . PHP_EOL;
        $result .= "   Component  : $this->likelyComponent" . PHP_EOL;
        $result .= "   Confidence : " . round($this->getConfidenceScorePercentage()) . "%" . PHP_EOL;
        $result .= "--------------------------------------------------------------------" . PHP_EOL;
        $result .= "   ANALYSIS          " . PHP_EOL;
        $result .= "   ========          " . PHP_EOL;
        $result .= $formatPlainText->formatConsoleText($this->summaryAnalysis, 66, 6) . PHP_EOL . PHP_EOL;
        $result .= "   RECOMMENDED ACTION" . PHP_EOL;
        $result .= "   ================== " . PHP_EOL;
        $result .= $formatPlainText->formatConsoleText($this->recommendedAction, 66, 6) . PHP_EOL;

        if (count($this->citations) !== 0) {
            $summary = $this->citations->sumarize();

            $result .= "--------------------------------------------------------------------" . PHP_EOL;
            $result .= "   HNSW NEAREST NEIGHBOR CITATIONS:" . PHP_EOL;
            foreach ($summary as $s) {
                $result .= "      * " . $s->getTicketKey() . " [" . $s->getTicketResolution() . "]" . PHP_EOL;
                $result .= PHP_EOL;
                $result .= $formatPlainText->formatConsoleText($s->getTicketTitle(), 100, 8) . PHP_EOL;
                $result .= PHP_EOL;
                $result .= "        Chunked texts:" . PHP_EOL;

                $chunkedItems = $s->getChunkSummaryItemCollection();
                foreach ($chunkedItems as $chunkedItem) {
                    $result .= $formatPlainText->formatConsoleText(
                        " - [Cosine Distance: " . $chunkedItem->getCosineDistance() . "] " . $chunkedItem->getChunkedText(),
                        100,
                        12
                    ) . PHP_EOL . PHP_EOL;
                }
            }
        }

        $result .= "====================================================================" . PHP_EOL;
        return $result;
    }
}
