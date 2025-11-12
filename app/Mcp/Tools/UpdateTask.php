<?php

namespace App\Mcp\Tools;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Domain\TaskManagement\Services\TaskStatusManager;
use App\Domain\TaskManagement\ValueObjects\Priority;
use App\Domain\TaskManagement\ValueObjects\Status;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class UpdateTask extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Update an existing task. You can update title, description, status, priority, assignee, due date, and hours.
        Status transitions are validated to ensure proper workflow.
    MARKDOWN;

    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly TaskStatusManager $statusManager
    ) {
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $taskId = $request->input('task_id');
        $task = $this->taskRepository->findById($taskId);

        if (!$task) {
            return Response::json([
                'success' => false,
                'error' => "Task #{$taskId} not found",
            ]);
        }

        $data = [];

        // Update basic fields if provided
        if ($request->has('title')) {
            $data['title'] = $request->input('title');
        }

        if ($request->has('description')) {
            $data['description'] = $request->input('description');
        }

        if ($request->has('priority')) {
            $data['priority'] = Priority::from($request->input('priority'));
        }

        if ($request->has('assigned_to')) {
            $data['assigned_to'] = $request->input('assigned_to');
        }

        if ($request->has('due_date')) {
            $data['due_date'] = $request->input('due_date');
        }

        if ($request->has('estimated_hours')) {
            $data['estimated_hours'] = $request->input('estimated_hours');
        }

        if ($request->has('actual_hours')) {
            $data['actual_hours'] = $request->input('actual_hours');
        }

        // Handle status change separately to validate transitions
        if ($request->has('status')) {
            $newStatus = Status::from($request->input('status'));

            if (!$this->statusManager->canTransition($task, $newStatus)) {
                return Response::json([
                    'success' => false,
                    'error' => "Cannot transition from {$task->status->value} to {$newStatus->value}",
                    'available_transitions' => array_map(
                        fn ($status) => $status->value,
                        $this->statusManager->getAvailableTransitions($task)
                    ),
                ]);
            }

            $data['status'] = $newStatus;
        }

        // Update the task
        $updated = $this->taskRepository->update($taskId, $data);

        if (!$updated) {
            return Response::json([
                'success' => false,
                'error' => 'Failed to update task',
            ]);
        }

        // Reload task to get updated data
        $task = $this->taskRepository->findById($taskId);

        return Response::json([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status->value,
                'priority' => $task->priority->value,
                'updated_at' => $task->updated_at->toIso8601String(),
            ],
            'message' => "Task #{$task->id} updated successfully",
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
            'task_id' => $schema->integer()
                ->description('The ID of the task to update')
                ->minimum(1)
                ->required(),
            'title' => $schema->string()
                ->description('New task title')
                ->minLength(3)
                ->maxLength(255)
                ->optional(),
            'description' => $schema->string()
                ->description('New task description')
                ->optional(),
            'status' => $schema->enum(['pending', 'in_progress', 'review', 'completed', 'blocked'])
                ->description('New task status (transitions are validated)')
                ->optional(),
            'priority' => $schema->enum(['low', 'medium', 'high', 'critical'])
                ->description('New task priority')
                ->optional(),
            'assigned_to' => $schema->integer()
                ->description('User ID to assign the task to')
                ->optional(),
            'due_date' => $schema->string()
                ->description('New due date in ISO 8601 format')
                ->optional(),
            'estimated_hours' => $schema->integer()
                ->description('Estimated hours to complete')
                ->minimum(1)
                ->optional(),
            'actual_hours' => $schema->integer()
                ->description('Actual hours spent')
                ->minimum(0)
                ->optional(),
        ];
    }
}
