<?php

namespace App\Mcp\Tools;

use App\Domain\TaskManagement\Contracts\Repositories\ProjectRepositoryInterface;
use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use Carbon\Carbon;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class GenerateProjectStatusReport extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Generate comprehensive project status report with health overview, task completion, budget tracking, and risk assessment';

    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly TaskRepositoryInterface $taskRepository
    ) {
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $projectId = $request->input('project_id');
        $format = $request->input('format', 'json');

        $project = $this->projectRepository->findById($projectId);

        if (!$project) {
            return Response::json([
                'success' => false,
                'error' => "Project #{$projectId} not found",
            ]);
        }

        $tasks = $this->taskRepository->findByProject($projectId);
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        // Calculate health score
        $overdueTasks = $tasks->filter(fn ($t) => $t->isOverdue())->count();
        $blockedTasks = $tasks->where('status', 'blocked')->count();
        $healthScore = max(0, 100 - ($overdueTasks * 5) - ($blockedTasks * 10) - (100 - $completionRate) * 0.5);

        // Time tracking
        $totalEstimated = $tasks->sum('estimated_hours') ?? 0;
        $totalActual = $tasks->sum('actual_hours') ?? 0;

        $reportData = [
            'report_type' => 'project_status_report',
            'generated_at' => Carbon::now()->toDateTimeString(),
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'status' => $project->status,
                'owner' => $project->owner->name ?? 'Unassigned',
            ],
            'health' => [
                'score' => round($healthScore, 1),
                'status' => $healthScore >= 80 ? 'healthy' : ($healthScore >= 60 ? 'at_risk' : 'critical'),
            ],
            'completion' => [
                'total_tasks' => $totalTasks,
                'completed' => $completedTasks,
                'rate' => $completionRate.'%',
            ],
            'time_tracking' => [
                'estimated_hours' => $totalEstimated,
                'actual_hours' => $totalActual,
                'variance' => $totalActual - $totalEstimated,
            ],
            'risks' => [
                'overdue_tasks' => $overdueTasks,
                'blocked_tasks' => $blockedTasks,
                'high_priority_pending' => $tasks->where('priority', 'high')->whereIn('status', ['pending', 'in_progress'])->count(),
            ],
        ];

        $output = $format === 'markdown' ? $this->formatAsMarkdown($reportData) : json_encode($reportData, JSON_PRETTY_PRINT);

        return Response::json([
            'success' => true,
            'format' => $format,
            'report' => $format === 'json' ? $reportData : null,
            'content' => $format !== 'json' ? $output : null,
        ]);
    }

    private function formatAsMarkdown(array $data): string
    {
        $md = "# Project Status Report: {$data['project']['name']}\n\n";
        $md .= "**Generated:** {$data['generated_at']}\n\n";
        $md .= "## Health Score: {$data['health']['score']} ({$data['health']['status']})\n\n";
        $md .= "## Completion\n\n";
        $md .= "- Total Tasks: {$data['completion']['total_tasks']}\n";
        $md .= "- Completed: {$data['completion']['completed']}\n";
        $md .= "- Rate: {$data['completion']['rate']}\n\n";
        $md .= "## Risks\n\n";
        $md .= "- Overdue: {$data['risks']['overdue_tasks']}\n";
        $md .= "- Blocked: {$data['risks']['blocked_tasks']}\n";

        return $md;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'project_id' => $schema->integer()->description('Project ID')->minimum(1)->required(),
            'format' => $schema->string()->enum(['json', 'markdown', 'html'])->default('json')->optional(),
        ];
    }
}
