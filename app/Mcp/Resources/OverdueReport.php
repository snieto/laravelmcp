<?php

namespace App\Mcp\Resources;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use Carbon\Carbon;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class OverdueReport extends Resource
{
    /**
     * The resource's URI.
     */
    protected string $uri = 'overdue-report';

    /**
     * The resource's description.
     */
    protected string $description = <<<'MARKDOWN'
        Get comprehensive report of all overdue tasks with risk assessment,
        grouped by project and assignee, including impact analysis and recommendations.
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
        // Get all tasks
        $allTasks = $this->taskRepository->all();

        // Filter overdue tasks (due_date in past and not completed)
        $overdueTasks = $allTasks->filter(function ($task) {
            return $task->due_date
                && $task->due_date->isPast()
                && $task->status !== 'completed';
        });

        if ($overdueTasks->isEmpty()) {
            return Response::json([
                'summary' => [
                    'total_overdue' => 0,
                    'message' => 'No overdue tasks found',
                ],
                'tasks' => [],
                'grouped_by_project' => [],
                'grouped_by_assignee' => [],
            ]);
        }

        // Summary statistics
        $totalOverdue = $overdueTasks->count();
        $criticalOverdue = $overdueTasks->where('priority', 'critical')->count();
        $highOverdue = $overdueTasks->where('priority', 'high')->count();
        $blockedOverdue = $overdueTasks->where('status', 'blocked')->count();

        // Calculate average days overdue
        $totalDaysOverdue = 0;
        foreach ($overdueTasks as $task) {
            $totalDaysOverdue += abs($task->due_date->diffInDays(Carbon::now(), false));
        }
        $avgDaysOverdue = round($totalDaysOverdue / $totalOverdue, 1);

        // Map overdue tasks with details
        $tasksList = $overdueTasks->map(function ($task) {
            $daysOverdue = abs($task->due_date->diffInDays(Carbon::now(), false));
            $risk = $this->assessRisk($task, $daysOverdue);

            return [
                'task_id' => $task->id,
                'title' => $task->title,
                'project' => $task->project->name ?? 'No Project',
                'project_id' => $task->project_id,
                'assignee' => $task->assignedTo->name ?? 'Unassigned',
                'assignee_id' => $task->assigned_to,
                'priority' => $task->priority->value,
                'status' => $task->status->value,
                'due_date' => $task->due_date->toDateString(),
                'days_overdue' => $daysOverdue,
                'risk_level' => $risk['level'],
                'risk_score' => $risk['score'],
                'estimated_hours' => $task->estimated_hours ?? 0,
            ];
        })->sortByDesc('risk_score')->values();

        // Group by project
        $groupedByProject = $overdueTasks->groupBy('project_id')->map(function ($tasks, $projectId) {
            $project = $tasks->first()->project;
            return [
                'project_id' => $projectId,
                'project_name' => $project->name ?? 'No Project',
                'overdue_count' => $tasks->count(),
                'critical_count' => $tasks->where('priority', 'critical')->count(),
                'high_count' => $tasks->where('priority', 'high')->count(),
                'total_estimated_hours' => $tasks->sum('estimated_hours') ?? 0,
            ];
        })->sortByDesc('overdue_count')->values();

        // Group by assignee
        $groupedByAssignee = $overdueTasks->groupBy('assigned_to')->map(function ($tasks, $assigneeId) {
            $assignee = $assigneeId ? $tasks->first()->assignedTo : null;
            return [
                'assignee_id' => $assigneeId,
                'assignee_name' => $assignee->name ?? 'Unassigned',
                'overdue_count' => $tasks->count(),
                'critical_count' => $tasks->where('priority', 'critical')->count(),
                'high_count' => $tasks->where('priority', 'high')->count(),
                'blocked_count' => $tasks->where('status', 'blocked')->count(),
            ];
        })->sortByDesc('overdue_count')->values();

        // Generate recommendations
        $recommendations = $this->generateRecommendations([
            'total' => $totalOverdue,
            'critical' => $criticalOverdue,
            'blocked' => $blockedOverdue,
            'avg_days' => $avgDaysOverdue,
        ]);

        // Calculate overall impact
        $impact = $this->calculateImpact($overdueTasks);

        return Response::json([
            'generated_at' => Carbon::now()->toDateTimeString(),
            'summary' => [
                'total_overdue' => $totalOverdue,
                'critical_priority' => $criticalOverdue,
                'high_priority' => $highOverdue,
                'blocked_tasks' => $blockedOverdue,
                'average_days_overdue' => $avgDaysOverdue,
                'total_estimated_hours_at_risk' => $overdueTasks->sum('estimated_hours') ?? 0,
            ],
            'impact_assessment' => $impact,
            'tasks' => $tasksList,
            'grouped_by_project' => $groupedByProject,
            'grouped_by_assignee' => $groupedByAssignee,
            'recommendations' => $recommendations,
            'urgency_level' => $this->determineUrgencyLevel($criticalOverdue, $highOverdue, $totalOverdue),
        ]);
    }

    /**
     * Assess risk for a single task.
     */
    private function assessRisk($task, int $daysOverdue): array
    {
        $score = 0;

        // Days overdue factor (max 40 points)
        if ($daysOverdue > 30) {
            $score += 40;
        } elseif ($daysOverdue > 14) {
            $score += 30;
        } elseif ($daysOverdue > 7) {
            $score += 20;
        } else {
            $score += 10;
        }

        // Priority factor (max 30 points)
        $score += match ($task->priority->value) {
            'critical' => 30,
            'high' => 20,
            'medium' => 10,
            'low' => 5,
        };

        // Status factor (max 30 points)
        if ($task->status === 'blocked') {
            $score += 30;
        } elseif ($task->status === 'pending') {
            $score += 20;
        } else {
            $score += 10;
        }

        $level = 'low';
        if ($score >= 70) {
            $level = 'critical';
        } elseif ($score >= 50) {
            $level = 'high';
        } elseif ($score >= 30) {
            $level = 'medium';
        }

        return [
            'score' => $score,
            'level' => $level,
        ];
    }

    /**
     * Calculate overall impact.
     */
    private function calculateImpact($overdueTasks): array
    {
        $totalTasks = $this->taskRepository->all()->count();
        $percentageOverdue = $totalTasks > 0 ? round(($overdueTasks->count() / $totalTasks) * 100, 1) : 0;

        $impactLevel = 'low';
        if ($percentageOverdue > 30) {
            $impactLevel = 'critical';
        } elseif ($percentageOverdue > 20) {
            $impactLevel = 'high';
        } elseif ($percentageOverdue > 10) {
            $impactLevel = 'medium';
        }

        return [
            'level' => $impactLevel,
            'percentage_of_total_tasks' => $percentageOverdue.'%',
            'affected_projects' => $overdueTasks->groupBy('project_id')->count(),
            'affected_team_members' => $overdueTasks->groupBy('assigned_to')->count(),
            'description' => $this->getImpactDescription($impactLevel),
        ];
    }

    /**
     * Get impact description.
     */
    private function getImpactDescription(string $level): string
    {
        return match ($level) {
            'critical' => 'Severe impact on project timelines. Immediate action required across multiple teams.',
            'high' => 'Significant impact on deliverables. Prioritize resolution of overdue tasks.',
            'medium' => 'Moderate impact. Review and address overdue tasks in upcoming sprint.',
            'low' => 'Minimal impact. Monitor and address during regular task management.',
            default => 'Impact level unknown.',
        };
    }

    /**
     * Generate recommendations.
     */
    private function generateRecommendations(array $stats): array
    {
        $recommendations = [];

        if ($stats['critical'] > 0) {
            $recommendations[] = [
                'priority' => 'urgent',
                'action' => 'Immediately address '.$stats['critical'].' critical overdue tasks',
                'reason' => 'Critical tasks pose highest risk to project success',
            ];
        }

        if ($stats['blocked'] > 0) {
            $recommendations[] = [
                'priority' => 'high',
                'action' => 'Unblock '.$stats['blocked'].' blocked tasks',
                'reason' => 'Blocked tasks prevent progress on dependent work',
            ];
        }

        if ($stats['avg_days'] > 14) {
            $recommendations[] = [
                'priority' => 'high',
                'action' => 'Review task assignment and capacity planning',
                'reason' => 'Average '.$stats['avg_days'].' days overdue indicates systemic issues',
            ];
        }

        if ($stats['total'] > 10) {
            $recommendations[] = [
                'priority' => 'medium',
                'action' => 'Conduct retrospective to identify root causes',
                'reason' => 'Large number of overdue tasks suggests process improvements needed',
            ];
        }

        if (empty($recommendations)) {
            $recommendations[] = [
                'priority' => 'low',
                'action' => 'Continue monitoring task progress',
                'reason' => 'Limited overdue tasks, maintain current practices',
            ];
        }

        return $recommendations;
    }

    /**
     * Determine urgency level.
     */
    private function determineUrgencyLevel(int $critical, int $high, int $total): string
    {
        if ($critical > 3 || $high > 10) {
            return 'immediate_action_required';
        } elseif ($critical > 0 || $high > 5 || $total > 15) {
            return 'attention_needed';
        } elseif ($total > 5) {
            return 'monitor_closely';
        } else {
            return 'routine_management';
        }
    }
}
