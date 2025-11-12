<?php

namespace App\Mcp\Resources;

use App\Domain\Analytics\Services\MetricsCollector;
use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use Carbon\Carbon;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class TeamMetrics extends Resource
{
    /**
     * The resource's URI.
     */
    protected string $uri = 'team-metrics';

    /**
     * The resource's description.
     */
    protected string $description = <<<'MARKDOWN'
        Get overall team performance metrics including task completion rates,
        status distribution, priority breakdown, and velocity trends.
    MARKDOWN;

    public function __construct(
        private readonly MetricsCollector $metricsCollector,
        private readonly TaskRepositoryInterface $taskRepository
    ) {
    }

    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        // Get date range from parameters or use defaults (last 30 days)
        $days = $request->input('days', 30);
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays($days);

        // Collect comprehensive metrics
        $metrics = $this->metricsCollector->collectMetrics($startDate, $endDate);

        // Calculate velocity (tasks completed per day)
        $tasksCompleted = $metrics['tasks']['completed'] ?? 0;
        $velocity = $days > 0 ? round($tasksCompleted / $days, 2) : 0;

        // Calculate completion rate
        $totalTasks = $metrics['tasks']['total'] ?? 1;
        $completionRate = round(($tasksCompleted / $totalTasks) * 100, 1);

        // Get status distribution
        $statusDistribution = [
            'pending' => $metrics['tasks']['pending'] ?? 0,
            'in_progress' => $metrics['tasks']['in_progress'] ?? 0,
            'review' => $metrics['tasks']['review'] ?? 0,
            'completed' => $metrics['tasks']['completed'] ?? 0,
            'blocked' => $metrics['tasks']['blocked'] ?? 0,
        ];

        // Get priority breakdown
        $priorityBreakdown = [
            'low' => $metrics['tasks']['priority']['low'] ?? 0,
            'medium' => $metrics['tasks']['priority']['medium'] ?? 0,
            'high' => $metrics['tasks']['priority']['high'] ?? 0,
            'critical' => $metrics['tasks']['priority']['critical'] ?? 0,
        ];

        // Calculate average task age
        $allTasks = $this->taskRepository->all();
        $totalAge = 0;
        foreach ($allTasks as $task) {
            $totalAge += $task->created_at->diffInDays(Carbon::now());
        }
        $averageAge = $allTasks->count() > 0 ? round($totalAge / $allTasks->count(), 1) : 0;

        return Response::json([
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
                'days' => $days,
            ],
            'overview' => [
                'total_tasks' => $totalTasks,
                'tasks_completed' => $tasksCompleted,
                'completion_rate' => $completionRate.'%',
                'velocity' => $velocity.' tasks/day',
                'average_task_age' => $averageAge.' days',
            ],
            'status_distribution' => $statusDistribution,
            'priority_breakdown' => $priorityBreakdown,
            'velocity_data' => [
                'daily_average' => $velocity,
                'weekly_projection' => round($velocity * 7, 0),
                'monthly_projection' => round($velocity * 30, 0),
            ],
            'health_indicators' => [
                'blocked_tasks' => $statusDistribution['blocked'],
                'overdue_tasks' => $metrics['tasks']['overdue'] ?? 0,
                'high_priority_pending' => $this->taskRepository->findByPriority('high')->where('status', 'pending')->count(),
            ],
        ]);
    }
}
