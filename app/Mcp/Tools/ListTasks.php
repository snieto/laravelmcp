<?php

namespace App\Mcp\Tools;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Domain\TaskManagement\ValueObjects\Status;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ListTasks extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        List tasks with optional filters. You can filter by project, status, assigned user, or search by text.
        Returns a paginated list of tasks with their details.
    MARKDOWN;

    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository
    ) {
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $projectId = $request->input('project_id');
        $status = $request->input('status');
        $assignedTo = $request->input('assigned_to');
        $search = $request->input('search');

        // Get tasks based on filters
        if ($projectId) {
            $tasks = $this->taskRepository->findByProjectId($projectId);
        } elseif ($status) {
            $tasks = $this->taskRepository->findByStatus(Status::from($status));
        } elseif ($assignedTo) {
            $tasks = $this->taskRepository->findByAssignedTo($assignedTo);
        } elseif ($search) {
            $tasks = $this->taskRepository->search($search);
        } else {
            $tasks = $this->taskRepository->all();
        }

        $formattedTasks = $tasks->map(fn ($task) => [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status->value,
            'priority' => $task->priority->value,
            'project_id' => $task->project_id,
            'project_name' => $task->project->name ?? null,
            'assigned_to' => $task->assigned_to,
            'assignee_name' => $task->assignedTo->name ?? null,
            'due_date' => $task->due_date?->toDateString(),
            'created_at' => $task->created_at->toIso8601String(),
        ])->values();

        return Response::json([
            'success' => true,
            'count' => $tasks->count(),
            'tasks' => $formattedTasks,
        ]);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'project_id' => $schema->integer()
                ->description('Filter tasks by project ID')
                ->optional(),
            'status' => $schema->enum(['pending', 'in_progress', 'review', 'completed', 'blocked'])
                ->description('Filter tasks by status')
                ->optional(),
            'assigned_to' => $schema->integer()
                ->description('Filter tasks by assignee user ID')
                ->optional(),
            'search' => $schema->string()
                ->description('Search tasks by title or description')
                ->optional(),
        ];
    }
}
