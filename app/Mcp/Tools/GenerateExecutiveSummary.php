<?php

namespace App\Mcp\Tools;

use App\Domain\Analytics\Services\MetricsCollector;
use App\Domain\TaskManagement\Contracts\Repositories\ProjectRepositoryInterface;
use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use Carbon\Carbon;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class GenerateExecutiveSummary extends Tool
{
    protected string $description = 'Generate executive summary with high-level organizational metrics, cross-project analysis, and strategic recommendations';

    public function __construct(
        private readonly MetricsCollector $metricsCollector,
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly TaskRepositoryInterface $taskRepository
    ) {
    }

    public function handle(Request $request): Response
    {
        $days = $request->input('days', 30);
        $format = $request->input('format', 'json');

        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays($days);

        // Collect organization-wide metrics
        $metrics = $this->metricsCollector->collectMetrics($startDate, $endDate);
        $allProjects = $this->projectRepository->getActive();
        $allTasks = $this->taskRepository->all();

        $totalTasks = $allTasks->count();
        $completed = $allTasks->where('status', 'completed')->count();
        $overdue = $allTasks->filter(fn ($t) => $t->isOverdue())->count();
        $blocked = $allTasks->where('status', 'blocked')->count();

        // Key achievements
        $achievements = [];
        if ($completed > 50) {
            $achievements[] = "Completed {$completed} tasks in the last {$days} days";
        }
        if ($overdue === 0) {
            $achievements[] = 'Zero overdue tasks across all projects';
        }

        // Key blockers
        $blockers = [];
        if ($blocked > 5) {
            $blockers[] = "{$blocked} tasks currently blocked";
        }
        if ($overdue > 10) {
            $blockers[] = "{$overdue} tasks overdue";
        }

        $reportData = [
            'report_type' => 'executive_summary',
            'generated_at' => Carbon::now()->toDateTimeString(),
            'period' => ['start' => $startDate->toDateString(), 'end' => $endDate->toDateString(), 'days' => $days],
            'organization_metrics' => [
                'active_projects' => $allProjects->count(),
                'total_tasks' => $totalTasks,
                'completion_rate' => $totalTasks > 0 ? round(($completed / $totalTasks) * 100, 1).'%' : '0%',
                'team_velocity' => $days > 0 ? round($completed / $days, 2).' tasks/day' : '0',
            ],
            'health_indicators' => [
                'overdue_tasks' => $overdue,
                'blocked_tasks' => $blocked,
                'at_risk_projects' => $allProjects->filter(function ($p) {
                    $tasks = $p->tasks;
                    return $tasks->filter(fn ($t) => $t->isOverdue())->count() > 3;
                })->count(),
            ],
            'key_achievements' => $achievements,
            'key_blockers' => $blockers,
            'recommendations' => $this->generateRecommendations($overdue, $blocked),
        ];

        return Response::json([
            'success' => true,
            'format' => $format,
            'report' => $reportData,
        ]);
    }

    private function generateRecommendations(int $overdue, int $blocked): array
    {
        $recommendations = [];

        if ($overdue > 10) {
            $recommendations[] = 'Review and reprioritize overdue tasks across projects';
        }
        if ($blocked > 5) {
            $recommendations[] = 'Address blockers to unblock team progress';
        }
        if (empty($recommendations)) {
            $recommendations[] = 'Continue maintaining current performance levels';
        }

        return $recommendations;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'days' => $schema->integer()->description('Number of days to analyze')->minimum(1)->maximum(365)->default(30)->optional(),
            'format' => $schema->string()->enum(['json', 'markdown'])->default('json')->optional(),
        ];
    }
}
