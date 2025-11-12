<?php

namespace App\Mcp\Tools;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class GetTask extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Get detailed information about a specific task by its ID.
        Returns task details including project, assignee, tags, and comments.
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
        $taskId = $request->input('task_id');
        $task = $this->taskRepository->findById($taskId);

        if (!$task) {
            return Response::json([
                'success' => false,
                'error' => "Task #{$taskId} not found",
            ]);
        }

        return Response::json([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status->value,
                'priority' => $task->priority->value,
                'project' => [
                    'id' => $task->project->id,
                    'name' => $task->project->name,
                ],
                'assigned_to' => $task->assigned_to,
                'assignee' => $task->assignedTo ? [
                    'id' => $task->assignedTo->id,
                    'name' => $task->assignedTo->name,
                    'email' => $task->assignedTo->email,
                ] : null,
                'created_by' => $task->created_by,
                'creator' => [
                    'id' => $task->createdBy->id,
                    'name' => $task->createdBy->name,
                    'email' => $task->createdBy->email,
                ],
                'tags' => $task->tags->map(fn ($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'color' => $tag->color,
                ])->toArray(),
                'comments' => $task->comments->map(fn ($comment) => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user' => $comment->user->name,
                    'created_at' => $comment->created_at->toIso8601String(),
                ])->toArray(),
                'due_date' => $task->due_date?->toDateString(),
                'estimated_hours' => $task->estimated_hours,
                'actual_hours' => $task->actual_hours,
                'created_at' => $task->created_at->toIso8601String(),
                'updated_at' => $task->updated_at->toIso8601String(),
            ],
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
                ->description('The ID of the task to retrieve')
                ->minimum(1)
                ->required(),
        ];
    }
}
