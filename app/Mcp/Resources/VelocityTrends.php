<?php

namespace App\Mcp\Resources;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use Carbon\Carbon;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class VelocityTrends extends Resource
{
    /**
     * The resource's URI.
     */
    protected string $uri = 'velocity-trends';

    /**
     * The resource's description.
     */
    protected string $description = <<<'MARKDOWN'
        Get task velocity and trend analysis including tasks completed per period,
        velocity trends over time, burndown data, and forecasted completion dates.
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
        // Get parameters
        $days = $request->input('days', 30);
        $projectId = $request->input('project_id', null);
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays($days);

        // Get tasks (filtered by project if specified)
        $allTasks = $projectId
            ? $this->taskRepository->findByProject($projectId)
            : $this->taskRepository->all();

        // Filter tasks by date range
        $periodTasks = $allTasks->filter(function ($task) use ($startDate, $endDate) {
            return $task->created_at >= $startDate && $task->created_at <= $endDate;
        });

        // Calculate daily velocity
        $dailyVelocity = $this->calculateDailyVelocity($periodTasks, $startDate, $endDate);

        // Calculate weekly velocity
        $weeklyVelocity = $this->calculateWeeklyVelocity($periodTasks, $startDate, $endDate);

        // Calculate average velocity
        $completedTasks = $periodTasks->where('status', 'completed')->count();
        $averageVelocity = $days > 0 ? round($completedTasks / $days, 2) : 0;

        // Calculate trend (comparing first half vs second half of period)
        $trend = $this->calculateTrend($dailyVelocity);

        // Get current active tasks count
        $activeTasks = $allTasks->whereIn('status', ['pending', 'in_progress', 'review'])->count();

        // Forecast completion date based on current velocity
        $forecastDays = $averageVelocity > 0 ? ceil($activeTasks / $averageVelocity) : null;
        $forecastDate = $forecastDays ? Carbon::now()->addDays($forecastDays)->toDateString() : 'Unable to forecast';

        // Burndown data (remaining tasks over time)
        $burndownData = $this->calculateBurndown($allTasks, $startDate, $endDate, $averageVelocity);

        // Calculate velocity consistency (standard deviation)
        $consistency = $this->calculateConsistency($dailyVelocity);

        return Response::json([
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
                'days' => $days,
            ],
            'project_filter' => $projectId ? "Project #{$projectId}" : 'All projects',
            'velocity_metrics' => [
                'average_daily' => $averageVelocity.' tasks/day',
                'average_weekly' => round($averageVelocity * 7, 1).' tasks/week',
                'total_completed' => $completedTasks,
                'trend' => $trend,
                'consistency' => $consistency,
            ],
            'daily_velocity' => $dailyVelocity,
            'weekly_velocity' => $weeklyVelocity,
            'forecast' => [
                'active_tasks' => $activeTasks,
                'estimated_days_to_complete' => $forecastDays ?? 'N/A',
                'forecast_completion_date' => $forecastDate,
                'confidence' => $this->calculateForecastConfidence($consistency),
            ],
            'burndown' => $burndownData,
            'insights' => $this->generateInsights($trend, $consistency, $averageVelocity),
        ]);
    }

    /**
     * Calculate daily velocity.
     */
    private function calculateDailyVelocity($tasks, Carbon $startDate, Carbon $endDate): array
    {
        $dailyData = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->toDateString();
            $completedCount = $tasks->filter(function ($task) use ($currentDate) {
                return $task->status === 'completed'
                    && $task->updated_at
                    && $task->updated_at->toDateString() === $currentDate->toDateString();
            })->count();

            $dailyData[] = [
                'date' => $dateStr,
                'completed' => $completedCount,
            ];

            $currentDate->addDay();
        }

        return $dailyData;
    }

    /**
     * Calculate weekly velocity.
     */
    private function calculateWeeklyVelocity($tasks, Carbon $startDate, Carbon $endDate): array
    {
        $weeklyData = [];
        $currentDate = $startDate->copy()->startOfWeek();

        while ($currentDate <= $endDate) {
            $weekEnd = $currentDate->copy()->endOfWeek();
            $completedCount = $tasks->filter(function ($task) use ($currentDate, $weekEnd) {
                return $task->status === 'completed'
                    && $task->updated_at
                    && $task->updated_at >= $currentDate
                    && $task->updated_at <= $weekEnd;
            })->count();

            $weeklyData[] = [
                'week_start' => $currentDate->toDateString(),
                'week_end' => $weekEnd->toDateString(),
                'completed' => $completedCount,
            ];

            $currentDate->addWeek();
        }

        return $weeklyData;
    }

    /**
     * Calculate trend.
     */
    private function calculateTrend(array $dailyVelocity): array
    {
        if (count($dailyVelocity) < 2) {
            return ['direction' => 'insufficient_data', 'percentage' => 0];
        }

        $midpoint = (int) floor(count($dailyVelocity) / 2);
        $firstHalf = array_slice($dailyVelocity, 0, $midpoint);
        $secondHalf = array_slice($dailyVelocity, $midpoint);

        $firstHalfAvg = array_sum(array_column($firstHalf, 'completed')) / count($firstHalf);
        $secondHalfAvg = array_sum(array_column($secondHalf, 'completed')) / count($secondHalf);

        $change = $secondHalfAvg - $firstHalfAvg;
        $percentageChange = $firstHalfAvg > 0 ? round(($change / $firstHalfAvg) * 100, 1) : 0;

        $direction = 'stable';
        if ($percentageChange > 10) {
            $direction = 'increasing';
        } elseif ($percentageChange < -10) {
            $direction = 'decreasing';
        }

        return [
            'direction' => $direction,
            'percentage_change' => $percentageChange.'%',
            'first_half_avg' => round($firstHalfAvg, 2),
            'second_half_avg' => round($secondHalfAvg, 2),
        ];
    }

    /**
     * Calculate velocity consistency.
     */
    private function calculateConsistency(array $dailyVelocity): array
    {
        $values = array_column($dailyVelocity, 'completed');
        $average = array_sum($values) / count($values);

        $variance = 0;
        foreach ($values as $value) {
            $variance += pow($value - $average, 2);
        }
        $variance /= count($values);
        $stdDev = sqrt($variance);

        $status = 'consistent';
        if ($stdDev > $average) {
            $status = 'inconsistent';
        } elseif ($stdDev > $average * 0.5) {
            $status = 'moderate';
        }

        return [
            'status' => $status,
            'standard_deviation' => round($stdDev, 2),
            'coefficient_of_variation' => $average > 0 ? round(($stdDev / $average) * 100, 1).'%' : 'N/A',
        ];
    }

    /**
     * Calculate burndown data.
     */
    private function calculateBurndown($allTasks, Carbon $startDate, Carbon $endDate, float $averageVelocity): array
    {
        $totalTasks = $allTasks->count();
        $activeTasks = $allTasks->whereIn('status', ['pending', 'in_progress', 'review'])->count();

        return [
            'total_tasks' => $totalTasks,
            'remaining_tasks' => $activeTasks,
            'completed_tasks' => $allTasks->where('status', 'completed')->count(),
            'burndown_rate' => $averageVelocity.' tasks/day',
        ];
    }

    /**
     * Calculate forecast confidence.
     */
    private function calculateForecastConfidence(array $consistency): string
    {
        return match ($consistency['status']) {
            'consistent' => 'high',
            'moderate' => 'medium',
            'inconsistent' => 'low',
            default => 'unknown',
        };
    }

    /**
     * Generate insights.
     */
    private function generateInsights(array $trend, array $consistency, float $averageVelocity): array
    {
        $insights = [];

        // Trend insights
        if ($trend['direction'] === 'increasing') {
            $insights[] = "Velocity is trending upward by {$trend['percentage_change']}, indicating improved productivity";
        } elseif ($trend['direction'] === 'decreasing') {
            $insights[] = "Velocity is trending downward by {$trend['percentage_change']}, may indicate bottlenecks";
        } else {
            $insights[] = 'Velocity is stable, team is maintaining consistent pace';
        }

        // Consistency insights
        if ($consistency['status'] === 'inconsistent') {
            $insights[] = 'High velocity variance detected, consider investigating workload distribution';
        } elseif ($consistency['status'] === 'consistent') {
            $insights[] = 'Team maintains consistent velocity, indicating stable workflow';
        }

        // Velocity insights
        if ($averageVelocity < 0.5) {
            $insights[] = 'Low velocity detected, consider reviewing team capacity and blockers';
        } elseif ($averageVelocity > 2) {
            $insights[] = 'High velocity maintained, team is performing well';
        }

        return $insights;
    }
}
