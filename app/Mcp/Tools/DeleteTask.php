<?php

namespace App\Mcp\Tools;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class DeleteTask extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Delete a task permanently. This action cannot be undone.
        The task will be soft-deleted, allowing for potential recovery.
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

        $taskTitle = $task->title;
        $deleted = $this->taskRepository->delete($taskId);

        if (!$deleted) {
            return Response::json([
                'success' => false,
                'error' => 'Failed to delete task',
            ]);
        }

        return Response::json([
            'success' => true,
            'message' => "Task #{$taskId} '{$taskTitle}' deleted successfully",
            'task_id' => $taskId,
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
                ->description('The ID of the task to delete')
                ->minimum(1)
                ->required(),
        ];
    }
}
