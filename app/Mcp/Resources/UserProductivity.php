<?php

namespace App\Mcp\Resources;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Carbon\Carbon;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class UserProductivity extends Resource
{
    /**
     * The resource's URI.
     */
    protected string $uri = 'user-productivity/{user_id}';

    /**
     * The resource's description.
     */
    protected string $description = <<<'MARKDOWN'
        Get individual user productivity metrics including task completion stats,
        workload balance, average completion time, and recent activity timeline.
    MARKDOWN;

    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository
    ) {
    }

    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $userId = $request->input('user_id');

        if (!$userId) {
            return Response::json([
                'error' => 'user_id is required',
                'usage' => 'Provide user_id in the URI: user-productivity/1',
            ]);
        }

        $user = User::find($userId);

        if (!$user) {
            return Response::json([
                'error' => "User #{$userId} not found",
            ]);
        }

        // Get date range (default last 30 days)
        $days = $request->input('days', 30);
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays($days);

        // Get all tasks assigned to this user
        $allTasks = $this->taskRepository->findByAssignee($userId);
        $recentTasks = $allTasks->filter(function ($task) use ($startDate) {
            return $task->created_at >= $startDate;
        });

        // Status breakdown
        $statusBreakdown = [
            'pending' => $allTasks->where('status', 'pending')->count(),
            'in_progress' => $allTasks->where('status', 'in_progress')->count(),
            'review' => $allTasks->where('status', 'review')->count(),
            'completed' => $allTasks->where('status', 'completed')->count(),
            'blocked' => $allTasks->where('status', 'blocked')->count(),
        ];

        // Priority breakdown (current workload)
        $currentWorkload = $allTasks->whereIn('status', ['pending', 'in_progress', 'review']);
        $priorityWorkload = [
            'low' => $currentWorkload->where('priority', 'low')->count(),
            'medium' => $currentWorkload->where('priority', 'medium')->count(),
            'high' => $currentWorkload->where('priority', 'high')->count(),
            'critical' => $currentWorkload->where('priority', 'critical')->count(),
        ];

        // Calculate completion rate
        $totalTasks = $allTasks->count();
        $completedTasks = $statusBreakdown['completed'];
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        // Calculate average completion time
        $completedTasksList = $allTasks->where('status', 'completed');
        $totalCompletionTime = 0;
        foreach ($completedTasksList as $task) {
            if ($task->updated_at && $task->created_at) {
                $totalCompletionTime += $task->created_at->diffInDays($task->updated_at);
            }
        }
        $averageCompletionTime = $completedTasks > 0 ? round($totalCompletionTime / $completedTasks, 1) : 0;

        // Calculate velocity (tasks completed in period / days)
        $completedInPeriod = $recentTasks->where('status', 'completed')->count();
        $velocity = $days > 0 ? round($completedInPeriod / $days, 2) : 0;

        // Recent activity timeline (last 10 tasks updated)
        $recentActivity = $allTasks->sortByDesc('updated_at')->take(10)->map(function ($task) {
            return [
                'task_id' => $task->id,
                'title' => $task->title,
                'status' => $task->status->value,
                'priority' => $task->priority->value,
                'updated_at' => $task->updated_at->format('Y-m-d H:i'),
                'days_ago' => $task->updated_at->diffForHumans(),
            ];
        })->values();

        // Overdue tasks
        $overdueTasks = $allTasks->filter(function ($task) {
            return $task->due_date && $task->due_date->isPast() && $task->status !== 'completed';
        })->count();

        // Estimated vs actual hours
        $totalEstimatedHours = $allTasks->sum('estimated_hours') ?? 0;
        $totalActualHours = $allTasks->sum('actual_hours') ?? 0;

        // Workload balance score (considers priority distribution)
        $workloadBalance = $this->calculateWorkloadBalance($priorityWorkload);

        return Response::json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
                'days' => $days,
            ],
            'overview' => [
                'total_tasks' => $totalTasks,
                'current_workload' => $currentWorkload->count(),
                'completed_tasks' => $completedTasks,
                'completion_rate' => $completionRate.'%',
                'average_completion_time' => $averageCompletionTime.' days',
                'velocity' => $velocity.' tasks/day',
                'overdue_tasks' => $overdueTasks,
            ],
            'status_breakdown' => $statusBreakdown,
            'priority_workload' => $priorityWorkload,
            'workload_balance' => $workloadBalance,
            'time_tracking' => [
                'total_estimated_hours' => $totalEstimatedHours,
                'total_actual_hours' => $totalActualHours,
                'average_hours_per_task' => $completedTasks > 0 ? round($totalActualHours / $completedTasks, 1) : 0,
            ],
            'recent_activity' => $recentActivity,
            'productivity_score' => $this->calculateProductivityScore($completionRate, $velocity, $overdueTasks),
        ]);
    }

    /**
     * Calculate workload balance score.
     */
    private function calculateWorkloadBalance(array $priorityWorkload): array
    {
        $total = array_sum($priorityWorkload);

        if ($total === 0) {
            return [
                'status' => 'no_workload',
                'message' => 'No active tasks assigned',
            ];
        }

        $criticalCount = $priorityWorkload['critical'];
        $highCount = $priorityWorkload['high'];

        if ($criticalCount > 5 || $highCount > 10) {
            return [
                'status' => 'overloaded',
                'message' => 'High number of critical/high priority tasks',
                'recommendation' => 'Consider redistributing some high priority tasks',
            ];
        } elseif ($total > 20) {
            return [
                'status' => 'busy',
                'message' => 'Large number of active tasks',
                'recommendation' => 'Focus on completing current tasks before accepting new ones',
            ];
        } else {
            return [
                'status' => 'balanced',
                'message' => 'Workload appears well balanced',
            ];
        }
    }

    /**
     * Calculate productivity score (0-100).
     */
    private function calculateProductivityScore(float $completionRate, float $velocity, int $overdueTasks): array
    {
        $score = 0;

        // Completion rate (max 40 points)
        $score += min($completionRate * 0.4, 40);

        // Velocity (max 30 points, assuming 1 task/day is good)
        $score += min($velocity * 30, 30);

        // Penalize for overdue tasks (max -30 points)
        $score -= min($overdueTasks * 10, 30);

        $score = max(0, min(100, $score));

        $rating = 'excellent';
        if ($score < 40) {
            $rating = 'needs_improvement';
        } elseif ($score < 60) {
            $rating = 'fair';
        } elseif ($score < 80) {
            $rating = 'good';
        }

        return [
            'score' => round($score, 1),
            'rating' => $rating,
        ];
    }
}
