<?php

namespace App\Mcp\Tools;

use App\Domain\Analytics\Services\MetricsCollector;
use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use Carbon\Carbon;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class GenerateSprintReport extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Generate a comprehensive sprint report with completion metrics, velocity analysis,
        team performance breakdown, and identified blockers.
    MARKDOWN;

    public function __construct(
        private readonly MetricsCollector $metricsCollector,
        private readonly TaskRepositoryInterface $taskRepository
    ) {
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));
        $projectId = $request->input('project_id', null);
        $format = $request->input('format', 'json');

        // Collect metrics for the sprint period
        $metrics = $this->metricsCollector->collectMetrics($startDate, $endDate);

        // Get tasks for this sprint
        $sprintTasks = $projectId
            ? $this->taskRepository->findByProject($projectId)
            : $this->taskRepository->all();

        $sprintTasks = $sprintTasks->filter(function ($task) use ($startDate, $endDate) {
            return $task->created_at >= $startDate && $task->created_at <= $endDate;
        });

        // Calculate sprint metrics
        $totalTasks = $sprintTasks->count();
        $completedTasks = $sprintTasks->where('status', 'completed')->count();
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        // Sprint velocity
        $days = $startDate->diffInDays($endDate);
        $velocity = $days > 0 ? round($completedTasks / $days, 2) : 0;

        // Status breakdown
        $statusBreakdown = [
            'completed' => $completedTasks,
            'in_progress' => $sprintTasks->where('status', 'in_progress')->count(),
            'review' => $sprintTasks->where('status', 'review')->count(),
            'pending' => $sprintTasks->where('status', 'pending')->count(),
            'blocked' => $sprintTasks->where('status', 'blocked')->count(),
        ];

        // Identify blockers
        $blockedTasks = $sprintTasks->filter(function ($task) {
            return $task->status === 'blocked';
        })->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'priority' => $task->priority->value,
                'assignee' => $task->assignedTo->name ?? 'Unassigned',
            ];
        })->values();

        // Team performance
        $teamPerformance = $sprintTasks->groupBy('assigned_to')->map(function ($tasks, $userId) {
            $user = $userId ? $tasks->first()->assignedTo : null;
            return [
                'name' => $user->name ?? 'Unassigned',
                'total_tasks' => $tasks->count(),
                'completed' => $tasks->where('status', 'completed')->count(),
                'in_progress' => $tasks->where('status', 'in_progress')->count(),
                'completion_rate' => $tasks->count() > 0
                    ? round(($tasks->where('status', 'completed')->count() / $tasks->count()) * 100, 1).'%'
                    : '0%',
            ];
        })->values();

        // Build report data
        $reportData = [
            'report_type' => 'sprint_report',
            'generated_at' => Carbon::now()->toDateTimeString(),
            'sprint_period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'duration_days' => $days,
            ],
            'project_filter' => $projectId ? "Project #{$projectId}" : 'All projects',
            'summary' => [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'completion_rate' => $completionRate.'%',
                'velocity' => $velocity.' tasks/day',
            ],
            'status_breakdown' => $statusBreakdown,
            'team_performance' => $teamPerformance,
            'blockers' => [
                'count' => $blockedTasks->count(),
                'tasks' => $blockedTasks,
            ],
            'key_metrics' => [
                'average_task_age' => $this->calculateAverageTaskAge($sprintTasks).' days',
                'on_time_completion' => $this->calculateOnTimeCompletion($sprintTasks).'%',
            ],
        ];

        // Format output
        $output = match ($format) {
            'markdown' => $this->formatAsMarkdown($reportData),
            'html' => $this->formatAsHtml($reportData),
            default => json_encode($reportData, JSON_PRETTY_PRINT),
        };

        return Response::json([
            'success' => true,
            'format' => $format,
            'report' => $format === 'json' ? $reportData : null,
            'content' => $format !== 'json' ? $output : null,
        ]);
    }

    /**
     * Calculate average task age.
     */
    private function calculateAverageTaskAge($tasks): float
    {
        if ($tasks->isEmpty()) {
            return 0;
        }

        $totalAge = 0;
        foreach ($tasks as $task) {
            $totalAge += $task->created_at->diffInDays(Carbon::now());
        }

        return round($totalAge / $tasks->count(), 1);
    }

    /**
     * Calculate on-time completion rate.
     */
    private function calculateOnTimeCompletion($tasks): float
    {
        $completedWithDueDate = $tasks->filter(function ($task) {
            return $task->status === 'completed' && $task->due_date;
        });

        if ($completedWithDueDate->isEmpty()) {
            return 0;
        }

        $onTime = $completedWithDueDate->filter(function ($task) {
            return $task->updated_at <= $task->due_date;
        })->count();

        return round(($onTime / $completedWithDueDate->count()) * 100, 1);
    }

    /**
     * Format report as markdown.
     */
    private function formatAsMarkdown(array $data): string
    {
        $md = "# Sprint Report\n\n";
        $md .= "**Generated:** {$data['generated_at']}\n\n";
        $md .= "## Sprint Period\n\n";
        $md .= "- Start: {$data['sprint_period']['start_date']}\n";
        $md .= "- End: {$data['sprint_period']['end_date']}\n";
        $md .= "- Duration: {$data['sprint_period']['duration_days']} days\n\n";
        $md .= "## Summary\n\n";
        $md .= "- Total Tasks: {$data['summary']['total_tasks']}\n";
        $md .= "- Completed: {$data['summary']['completed_tasks']}\n";
        $md .= "- Completion Rate: {$data['summary']['completion_rate']}\n";
        $md .= "- Velocity: {$data['summary']['velocity']}\n\n";
        $md .= "## Status Breakdown\n\n";
        foreach ($data['status_breakdown'] as $status => $count) {
            $md .= "- ".ucfirst($status).": {$count}\n";
        }
        $md .= "\n## Team Performance\n\n";
        foreach ($data['team_performance'] as $member) {
            $md .= "### {$member['name']}\n";
            $md .= "- Total Tasks: {$member['total_tasks']}\n";
            $md .= "- Completed: {$member['completed']}\n";
            $md .= "- Completion Rate: {$member['completion_rate']}\n\n";
        }
        if ($data['blockers']['count'] > 0) {
            $md .= "## Blockers ({$data['blockers']['count']})\n\n";
            foreach ($data['blockers']['tasks'] as $blocker) {
                $md .= "- [{$blocker['priority']}] {$blocker['title']} (#{$blocker['id']}) - {$blocker['assignee']}\n";
            }
        }

        return $md;
    }

    /**
     * Format report as HTML.
     */
    private function formatAsHtml(array $data): string
    {
        $html = "<html><head><title>Sprint Report</title></head><body>";
        $html .= "<h1>Sprint Report</h1>";
        $html .= "<p><strong>Generated:</strong> {$data['generated_at']}</p>";
        $html .= "<h2>Sprint Period</h2>";
        $html .= "<ul>";
        $html .= "<li>Start: {$data['sprint_period']['start_date']}</li>";
        $html .= "<li>End: {$data['sprint_period']['end_date']}</li>";
        $html .= "<li>Duration: {$data['sprint_period']['duration_days']} days</li>";
        $html .= "</ul>";
        $html .= "<h2>Summary</h2><ul>";
        $html .= "<li>Total Tasks: {$data['summary']['total_tasks']}</li>";
        $html .= "<li>Completed: {$data['summary']['completed_tasks']}</li>";
        $html .= "<li>Completion Rate: {$data['summary']['completion_rate']}</li>";
        $html .= "<li>Velocity: {$data['summary']['velocity']}</li>";
        $html .= "</ul>";
        $html .= "</body></html>";

        return $html;
    }

    /**
     * Get the tool's input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'start_date' => $schema->string()
                ->description('Sprint start date (YYYY-MM-DD)')
                ->pattern('^\d{4}-\d{2}-\d{2}$')
                ->required(),
            'end_date' => $schema->string()
                ->description('Sprint end date (YYYY-MM-DD)')
                ->pattern('^\d{4}-\d{2}-\d{2}$')
                ->required(),
            'project_id' => $schema->integer()
                ->description('Optional project ID to filter tasks')
                ->minimum(1)
                ->optional(),
            'format' => $schema->string()
                ->description('Output format: json, markdown, or html')
                ->enum(['json', 'markdown', 'html'])
                ->default('json')
                ->optional(),
        ];
    }
}
