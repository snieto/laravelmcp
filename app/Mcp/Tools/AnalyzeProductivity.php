<?php

namespace App\Mcp\Tools;

use App\Domain\AIIntegration\Services\ProductivityAnalyzer;
use App\Domain\Analytics\Services\MetricsCollector;
use Carbon\Carbon;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class AnalyzeProductivity extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Analyze team productivity metrics using AI.
        Provides insights, identifies bottlenecks, and suggests improvements.
        Optionally specify a time period (default: last 7 days).
    MARKDOWN;

    public function __construct(
        private readonly ProductivityAnalyzer $productivityAnalyzer,
        private readonly MetricsCollector $metricsCollector
    ) {
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $days = $request->input('days', 7);
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays($days);

        try {
            // Collect metrics
            $metrics = $this->metricsCollector->collectMetrics($startDate, $startDate);

            // Analyze with AI
            $analysis = $this->productivityAnalyzer->analyze($metrics);

            // Identify bottlenecks
            $tasksArray = $metrics['tasks'] ?? [];
            $bottlenecks = $this->productivityAnalyzer->identifyBottlenecks([$tasksArray]);

            return Response::json([
                'success' => true,
                'period' => [
                    'days' => $days,
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'metrics' => $metrics,
                'ai_analysis' => $analysis,
                'bottlenecks' => $bottlenecks,
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => 'Failed to analyze productivity: '.$e->getMessage(),
                'hint' => 'Make sure OPENAI_API_KEY is configured in .env',
            ]);
        }
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'days' => $schema->integer()
                ->description('Number of days to analyze (default: 7)')
                ->minimum(1)
                ->maximum(90)
                ->default(7)
                ->optional(),
        ];
    }
}
