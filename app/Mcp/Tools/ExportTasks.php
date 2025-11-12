<?php

namespace App\Mcp\Tools;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use Carbon\Carbon;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ExportTasks extends Tool
{
    protected string $description = 'Export filtered tasks to various formats (CSV, JSON, Excel) with comprehensive filtering options';

    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository
    ) {
    }

    public function handle(Request $request): Response
    {
        $format = $request->input('format', 'json');
        $projectId = $request->input('project_id', null);
        $status = $request->input('status', null);
        $priority = $request->input('priority', null);
        $assigneeId = $request->input('assignee_id', null);

        // Get tasks with filters
        $tasks = $this->taskRepository->all();

        if ($projectId) {
            $tasks = $tasks->where('project_id', $projectId);
        }
        if ($status) {
            $tasks = $tasks->where('status', $status);
        }
        if ($priority) {
            $tasks = $tasks->where('priority', $priority);
        }
        if ($assigneeId) {
            $tasks = $tasks->where('assigned_to', $assigneeId);
        }

        // Map tasks to export format
        $exportData = $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status->value,
                'priority' => $task->priority->value,
                'project' => $task->project->name ?? 'No Project',
                'assignee' => $task->assignedTo->name ?? 'Unassigned',
                'creator' => $task->creator->name ?? 'Unknown',
                'due_date' => $task->due_date?->toDateString() ?? 'Not set',
                'estimated_hours' => $task->estimated_hours ?? 0,
                'actual_hours' => $task->actual_hours ?? 0,
                'created_at' => $task->created_at->toDateString(),
                'updated_at' => $task->updated_at->toDateString(),
            ];
        })->values();

        // Format output
        $output = match ($format) {
            'csv' => $this->formatAsCsv($exportData->toArray()),
            'json' => json_encode($exportData, JSON_PRETTY_PRINT),
            default => json_encode($exportData, JSON_PRETTY_PRINT),
        };

        return Response::json([
            'success' => true,
            'format' => $format,
            'count' => $exportData->count(),
            'filters_applied' => array_filter([
                'project_id' => $projectId,
                'status' => $status,
                'priority' => $priority,
                'assignee_id' => $assigneeId,
            ]),
            'data' => $format === 'json' ? $exportData : null,
            'content' => $format !== 'json' ? $output : null,
            'generated_at' => Carbon::now()->toDateTimeString(),
        ]);
    }

    private function formatAsCsv(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        $csv = implode(',', array_keys($data[0]))."\n";

        foreach ($data as $row) {
            $csv .= implode(',', array_map(function ($value) {
                return '"'.str_replace('"', '""', $value).'"';
            }, $row))."\n";
        }

        return $csv;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'format' => $schema->string()->enum(['json', 'csv'])->default('json')->optional(),
            'project_id' => $schema->integer()->description('Filter by project ID')->minimum(1)->optional(),
            'status' => $schema->string()->enum(['pending', 'in_progress', 'review', 'completed', 'blocked'])->optional(),
            'priority' => $schema->string()->enum(['low', 'medium', 'high', 'critical'])->optional(),
            'assignee_id' => $schema->integer()->description('Filter by assignee user ID')->minimum(1)->optional(),
        ];
    }
}
