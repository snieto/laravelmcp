<?php

namespace App\Mcp\Tools;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Domain\TaskManagement\ValueObjects\Priority;
use App\Domain\TaskManagement\ValueObjects\Status;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class CreateTask extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Create a new task in a project. Specify the project ID, title, description, priority, and other details.
        The task will be created with 'pending' status by default.
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
        $data = [
            'project_id' => $request->input('project_id'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'priority' => Priority::from($request->input('priority', 'medium')),
            'status' => Status::PENDING,
            'created_by' => $request->input('created_by'),
            'assigned_to' => $request->input('assigned_to'),
            'due_date' => $request->input('due_date'),
            'estimated_hours' => $request->input('estimated_hours'),
        ];

        $task = $this->taskRepository->create($data);

        return Response::json([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status->value,
                'priority' => $task->priority->value,
                'created_at' => $task->created_at->toIso8601String(),
            ],
            'message' => "Task #{$task->id} created successfully: {$task->title}",
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
                ->description('The ID of the project this task belongs to')
                ->required(),
            'title' => $schema->string()
                ->description('The task title')
                ->minLength(3)
                ->maxLength(255)
                ->required(),
            'description' => $schema->string()
                ->description('Detailed description of the task')
                ->optional(),
            'priority' => $schema->enum(['low', 'medium', 'high', 'critical'])
                ->description('Task priority level')
                ->default('medium'),
            'created_by' => $schema->integer()
                ->description('User ID of the task creator')
                ->required(),
            'assigned_to' => $schema->integer()
                ->description('User ID of the assignee')
                ->optional(),
            'due_date' => $schema->string()
                ->description('Due date in ISO 8601 format (e.g., 2025-11-20)')
                ->optional(),
            'estimated_hours' => $schema->integer()
                ->description('Estimated hours to complete the task')
                ->minimum(1)
                ->optional(),
        ];
    }
}
