<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Services;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Domain\TaskManagement\ValueObjects\Status;
use Carbon\Carbon;

class MetricsCollector
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository
    ) {
    }

    /**
     * Collect all metrics for a given period.
     */
    public function collectMetrics(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
                'days' => $startDate->diffInDays($endDate),
            ],
            'tasks' => $this->getTaskMetrics(),
            'completion' => $this->getCompletionMetrics($startDate, $endDate),
            'velocity' => $this->getVelocityMetrics($startDate, $endDate),
            'distribution' => $this->getDistributionMetrics(),
        ];
    }

    /**
     * Get overall task metrics.
     */
    public function getTaskMetrics(): array
    {
        $allTasks = $this->taskRepository->all();

        return [
            'total' => $allTasks->count(),
            'pending' => $this->taskRepository->getPending()->count(),
            'in_progress' => $this->taskRepository->getInProgress()->count(),
            'completed' => $this->taskRepository->getCompleted()->count(),
            'overdue' => $this->taskRepository->getOverdue()->count(),
            'high_priority' => $this->taskRepository->getHighPriority()->count(),
        ];
    }

    /**
     * Get completion metrics for a period.
     */
    public function getCompletionMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $completed = $this->taskRepository->getCompleted()
            ->whereBetween('updated_at', [$startDate, $endDate]);

        $totalEstimated = $completed->sum('estimated_hours');
        $totalActual = $completed->sum('actual_hours');

        return [
            'tasks_completed' => $completed->count(),
            'total_estimated_hours' => $totalEstimated,
            'total_actual_hours' => $totalActual,
            'estimation_accuracy' => $totalEstimated > 0
                ? round(($totalActual / $totalEstimated) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get velocity metrics.
     */
    public function getVelocityMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $days = $startDate->diffInDays($endDate) ?: 1;
        $completed = $this->taskRepository->getCompleted()
            ->whereBetween('updated_at', [$startDate, $endDate]);

        return [
            'tasks_per_day' => round($completed->count() / $days, 2),
            'hours_per_day' => round($completed->sum('actual_hours') / $days, 2),
            'average_task_duration' => $completed->count() > 0
                ? round($completed->sum('actual_hours') / $completed->count(), 2)
                : 0,
        ];
    }

    /**
     * Get task distribution metrics.
     */
    public function getDistributionMetrics(): array
    {
        $allTasks = $this->taskRepository->all();

        return [
            'by_status' => $allTasks->groupBy('status')
                ->map->count()
                ->toArray(),
            'by_priority' => $allTasks->groupBy('priority')
                ->map->count()
                ->toArray(),
        ];
    }

    /**
     * Get user productivity metrics.
     */
    public function getUserProductivity(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        $assignedTasks = $this->taskRepository->findByAssignedTo($userId);
        $completed = $assignedTasks
            ->where('status', Status::COMPLETED)
            ->whereBetween('updated_at', [$startDate, $endDate]);

        return [
            'user_id' => $userId,
            'total_assigned' => $assignedTasks->count(),
            'completed_in_period' => $completed->count(),
            'hours_spent' => $completed->sum('actual_hours'),
            'average_completion_time' => $completed->count() > 0
                ? round($completed->sum('actual_hours') / $completed->count(), 2)
                : 0,
        ];
    }
}
