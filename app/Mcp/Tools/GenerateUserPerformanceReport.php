<?php

namespace App\Mcp\Tools;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Carbon\Carbon;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class GenerateUserPerformanceReport extends Tool
{
    protected string $description = 'Generate individual user performance report with task statistics, productivity metrics, and workload analysis';

    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository
    ) {
    }

    public function handle(Request $request): Response
    {
        $userId = $request->input('user_id');
        $days = $request->input('days', 30);
        $format = $request->input('format', 'json');

        $user = User::find($userId);
        if (!$user) {
            return Response::json(['success' => false, 'error' => "User #{$userId} not found"]);
        }

        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays($days);

        $allTasks = $this->taskRepository->findByAssignee($userId);
        $periodTasks = $allTasks->filter(fn ($t) => $t->created_at >= $startDate);

        $completed = $allTasks->where('status', 'completed')->count();
        $total = $allTasks->count();
        $completionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;
        $velocity = $days > 0 ? round($periodTasks->where('status', 'completed')->count() / $days, 2) : 0;

        $reportData = [
            'report_type' => 'user_performance_report',
            'generated_at' => Carbon::now()->toDateTimeString(),
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
            'period' => ['start' => $startDate->toDateString(), 'end' => $endDate->toDateString(), 'days' => $days],
            'performance' => [
                'total_tasks' => $total,
                'completed_tasks' => $completed,
                'completion_rate' => $completionRate.'%',
                'velocity' => $velocity.' tasks/day',
                'current_workload' => $allTasks->whereIn('status', ['pending', 'in_progress', 'review'])->count(),
            ],
            'workload_balance' => [
                'critical' => $allTasks->where('priority', 'critical')->whereIn('status', ['pending', 'in_progress'])->count(),
                'high' => $allTasks->where('priority', 'high')->whereIn('status', ['pending', 'in_progress'])->count(),
            ],
        ];

        return Response::json([
            'success' => true,
            'format' => $format,
            'report' => $reportData,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'user_id' => $schema->integer()->description('User ID')->minimum(1)->required(),
            'days' => $schema->integer()->description('Number of days to analyze')->minimum(1)->maximum(365)->default(30)->optional(),
            'format' => $schema->string()->enum(['json', 'markdown'])->default('json')->optional(),
        ];
    }
}
