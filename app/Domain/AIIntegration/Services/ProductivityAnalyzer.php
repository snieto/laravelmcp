<?php

declare(strict_types=1);

namespace App\Domain\AIIntegration\Services;

class ProductivityAnalyzer
{
    public function __construct(
        private readonly OpenAIService $openAIService,
        private readonly PromptBuilder $promptBuilder
    ) {
    }

    /**
     * Analyze productivity metrics and generate insights.
     */
    public function analyze(array $metrics): string
    {
        if (!$this->openAIService->isAvailable()) {
            return $this->generateFallbackAnalysis($metrics);
        }

        $prompt = $this->promptBuilder->buildProductivityAnalysisPrompt($metrics);

        return $this->openAIService->complete($prompt, [
            'temperature' => 0.6,
            'max_tokens' => 1500,
        ]);
    }

    /**
     * Generate recommendations based on task data.
     */
    public function generateRecommendations(array $tasks): array
    {
        if (!$this->openAIService->isAvailable()) {
            return $this->generateFallbackRecommendations();
        }

        $prompt = $this->promptBuilder->buildPrioritySuggestionPrompt($tasks);

        $response = $this->openAIService->complete($prompt, [
            'temperature' => 0.7,
            'max_tokens' => 1000,
        ]);

        // Parse the response into structured recommendations
        return $this->parseRecommendations($response);
    }

    /**
     * Calculate team velocity and trends.
     */
    public function calculateVelocity(array $completedTasks, int $days = 7): array
    {
        $totalTasks = count($completedTasks);
        $totalHours = array_sum(array_column($completedTasks, 'actual_hours'));

        return [
            'tasks_per_day' => round($totalTasks / $days, 2),
            'hours_per_day' => round($totalHours / $days, 2),
            'total_tasks_completed' => $totalTasks,
            'total_hours_spent' => $totalHours,
            'period_days' => $days,
        ];
    }

    /**
     * Identify bottlenecks in the workflow.
     */
    public function identifyBottlenecks(array $tasks): array
    {
        $bottlenecks = [];

        // Tasks stuck in specific statuses
        $statusCounts = [];
        foreach ($tasks as $task) {
            $status = $task['status'] ?? 'unknown';
            $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
        }

        // Find statuses with unusually high counts
        if (isset($statusCounts['blocked']) && $statusCounts['blocked'] > 3) {
            $bottlenecks[] = [
                'type' => 'blocked_tasks',
                'count' => $statusCounts['blocked'],
                'severity' => 'high',
                'message' => "High number of blocked tasks ({$statusCounts['blocked']})",
            ];
        }

        if (isset($statusCounts['review']) && $statusCounts['review'] > 5) {
            $bottlenecks[] = [
                'type' => 'review_backlog',
                'count' => $statusCounts['review'],
                'severity' => 'medium',
                'message' => "Review backlog building up ({$statusCounts['review']} tasks)",
            ];
        }

        return $bottlenecks;
    }

    /**
     * Generate fallback analysis when AI is unavailable.
     */
    private function generateFallbackAnalysis(array $metrics): string
    {
        $tasksCompleted = $metrics['tasks_completed'] ?? 0;
        $tasksInProgress = $metrics['tasks_in_progress'] ?? 0;

        return "## Productivity Summary\n\n"
            ."- Tasks Completed: {$tasksCompleted}\n"
            ."- Tasks In Progress: {$tasksInProgress}\n\n"
            .'AI analysis is currently unavailable. Please check your OpenAI API configuration.';
    }

    /**
     * Generate fallback recommendations.
     */
    private function generateFallbackRecommendations(): array
    {
        return [
            ['type' => 'general', 'message' => 'Focus on high-priority tasks first'],
            ['type' => 'general', 'message' => 'Review blocked tasks daily'],
            ['type' => 'general', 'message' => 'Keep tasks updated with progress'],
        ];
    }

    /**
     * Parse AI recommendations into structured format.
     */
    private function parseRecommendations(string $response): array
    {
        // Simple parsing - can be enhanced with more sophisticated logic
        $lines = explode("\n", $response);
        $recommendations = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^[-*]\s*(.+)$/', $line, $matches)) {
                $recommendations[] = [
                    'type' => 'ai_suggestion',
                    'message' => $matches[1],
                ];
            }
        }

        return $recommendations;
    }
}
