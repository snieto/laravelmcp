<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Services;

use App\Domain\AIIntegration\Services\OpenAIService;
use App\Domain\AIIntegration\Services\PromptBuilder;
use Carbon\Carbon;

class ReportGenerator
{
    public function __construct(
        private readonly MetricsCollector $metricsCollector,
        private readonly OpenAIService $openAIService,
        private readonly PromptBuilder $promptBuilder
    ) {
    }

    /**
     * Generate a weekly productivity report.
     */
    public function generateWeeklyReport(): string
    {
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(7);

        $metrics = $this->metricsCollector->collectMetrics($startDate, $endDate);

        if ($this->openAIService->isAvailable()) {
            $prompt = $this->promptBuilder->buildReportPrompt('weekly productivity', $metrics);

            return $this->openAIService->complete($prompt, [
                'temperature' => 0.6,
                'max_tokens' => 2000,
            ]);
        }

        return $this->generateBasicReport('Weekly Report', $metrics);
    }

    /**
     * Generate a monthly summary report.
     */
    public function generateMonthlyReport(): string
    {
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(30);

        $metrics = $this->metricsCollector->collectMetrics($startDate, $endDate);

        if ($this->openAIService->isAvailable()) {
            $prompt = $this->promptBuilder->buildReportPrompt('monthly summary', $metrics);

            return $this->openAIService->complete($prompt, [
                'temperature' => 0.6,
                'max_tokens' => 2500,
            ]);
        }

        return $this->generateBasicReport('Monthly Report', $metrics);
    }

    /**
     * Generate a project-specific report.
     */
    public function generateProjectReport(int $projectId): string
    {
        // This would need project-specific metrics collection
        // For now, return a placeholder
        return "## Project Report\n\nProject-specific reporting coming soon.";
    }

    /**
     * Generate a user productivity report.
     */
    public function generateUserReport(int $userId): string
    {
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(30);

        $metrics = $this->metricsCollector->getUserProductivity($userId, $startDate, $endDate);

        return $this->generateBasicReport('User Productivity Report', $metrics);
    }

    /**
     * Generate a basic markdown report without AI.
     */
    private function generateBasicReport(string $title, array $metrics): string
    {
        $report = "# {$title}\n\n";
        $report .= "**Generated:** ".Carbon::now()->toDateTimeString()."\n\n";

        $report .= "## Metrics\n\n";
        $report .= $this->formatMetrics($metrics);

        return $report;
    }

    /**
     * Format metrics as markdown.
     */
    private function formatMetrics(array $metrics, int $level = 3): string
    {
        $markdown = '';
        $heading = str_repeat('#', $level);

        foreach ($metrics as $key => $value) {
            $label = ucwords(str_replace('_', ' ', $key));

            if (is_array($value)) {
                $markdown .= "{$heading} {$label}\n\n";
                $markdown .= $this->formatMetrics($value, $level + 1);
            } else {
                $markdown .= "- **{$label}:** {$value}\n";
            }
        }

        return $markdown."\n";
    }
}
