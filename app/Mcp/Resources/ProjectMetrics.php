<?php

namespace App\Mcp\Resources;

use App\Domain\TaskManagement\Contracts\Repositories\ProjectRepositoryInterface;
use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use Carbon\Carbon;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class ProjectMetrics extends Resource
{
    /**
     * The resource's URI.
     */
    protected string $uri = 'project-metrics/{project_id}';

    /**
     * The resource's description.
     */
    protected string $description = <<<'MARKDOWN'
        Get detailed metrics for a specific project including completion percentage,
        task breakdown, average completion time, and team member contributions.
    MARKDOWN;

    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly TaskRepositoryInterface $taskRepository
    ) {
    }

    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $projectId = $request->input('project_id');

        if (!$projectId) {
            return Response::json([
                'error' => 'project_id is required',
                'usage' => 'Provide project_id in the URI: project-metrics/1',
            ]);
        }

        $project = $this->projectRepository->findById($projectId);

        if (!$project) {
            return Response::json([
                'error' => "Project #{$projectId} not found",
            ]);
        }

        // Get all tasks for this project
        $tasks = $this->taskRepository->findByProject($projectId);
        $totalTasks = $tasks->count();

        // Status breakdown
        $statusBreakdown = [
            'pending' => $tasks->where('status', 'pending')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'review' => $tasks->where('status', 'review')->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'blocked' => $tasks->where('status', 'blocked')->count(),
        ];

        // Priority breakdown
        $priorityBreakdown = [
            'low' => $tasks->where('priority', 'low')->count(),
            'medium' => $tasks->where('priority', 'medium')->count(),
            'high' => $tasks->where('priority', 'high')->count(),
            'critical' => $tasks->where('priority', 'critical')->count(),
        ];

        // Calculate completion percentage
        $completedTasks = $statusBreakdown['completed'];
        $completionPercentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        // Calculate average completion time for completed tasks
        $completedTasksList = $tasks->where('status', 'completed');
        $totalCompletionTime = 0;
        foreach ($completedTasksList as $task) {
            if ($task->updated_at && $task->created_at) {
                $totalCompletionTime += $task->created_at->diffInDays($task->updated_at);
            }
        }
        $averageCompletionTime = $completedTasks > 0 ? round($totalCompletionTime / $completedTasks, 1) : 0;

        // Team member contributions
        $contributorStats = [];
        $tasksGroupedByAssignee = $tasks->groupBy('assigned_to');
        foreach ($tasksGroupedByAssignee as $userId => $userTasks) {
            if ($userId) {
                $user = $userTasks->first()->assignedTo;
                $contributorStats[] = [
                    'user_id' => $userId,
                    'user_name' => $user->name ?? 'Unknown',
                    'total_tasks' => $userTasks->count(),
                    'completed' => $userTasks->where('status', 'completed')->count(),
                    'in_progress' => $userTasks->where('status', 'in_progress')->count(),
                    'pending' => $userTasks->where('status', 'pending')->count(),
                ];
            }
        }

        // Calculate estimated vs actual hours
        $totalEstimatedHours = $tasks->sum('estimated_hours') ?? 0;
        $totalActualHours = $tasks->sum('actual_hours') ?? 0;
        $hoursDelta = $totalActualHours - $totalEstimatedHours;

        // Overdue tasks
        $overdueTasks = $tasks->filter(function ($task) {
            return $task->due_date && $task->due_date->isPast() && $task->status !== 'completed';
        })->count();

        return Response::json([
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'status' => $project->status,
                'owner' => $project->owner->name ?? 'Unassigned',
            ],
            'summary' => [
                'total_tasks' => $totalTasks,
                'completion_percentage' => $completionPercentage.'%',
                'average_completion_time' => $averageCompletionTime.' days',
                'overdue_tasks' => $overdueTasks,
            ],
            'status_breakdown' => $statusBreakdown,
            'priority_breakdown' => $priorityBreakdown,
            'time_tracking' => [
                'total_estimated_hours' => $totalEstimatedHours,
                'total_actual_hours' => $totalActualHours,
                'hours_delta' => $hoursDelta,
                'variance_percentage' => $totalEstimatedHours > 0 ? round(($hoursDelta / $totalEstimatedHours) * 100, 1).'%' : 'N/A',
            ],
            'team_contributions' => $contributorStats,
            'health_score' => $this->calculateProjectHealth($completionPercentage, $overdueTasks, $statusBreakdown['blocked']),
        ]);
    }

    /**
     * Calculate project health score (0-100).
     */
    private function calculateProjectHealth(float $completionPercentage, int $overdueTasks, int $blockedTasks): array
    {
        $score = 100;

        // Deduct points for low completion
        if ($completionPercentage < 30) {
            $score -= 20;
        } elseif ($completionPercentage < 50) {
            $score -= 10;
        }

        // Deduct points for overdue tasks
        $score -= min($overdueTasks * 5, 30);

        // Deduct points for blocked tasks
        $score -= min($blockedTasks * 10, 30);

        $score = max(0, $score);

        $status = 'excellent';
        if ($score < 40) {
            $status = 'critical';
        } elseif ($score < 60) {
            $status = 'poor';
        } elseif ($score < 80) {
            $status = 'fair';
        } elseif ($score < 90) {
            $status = 'good';
        }

        return [
            'score' => $score,
            'status' => $status,
        ];
    }
}
