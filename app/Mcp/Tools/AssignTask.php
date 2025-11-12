<?php

namespace App\Mcp\Tools;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Models\User;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class AssignTask extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Assign or reassign a task to a user. Provide the task ID and user ID.
        Leave user_id empty to unassign the task.
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
        $userId = $request->input('user_id');

        $task = $this->taskRepository->findById($taskId);

        if (!$task) {
            return Response::json([
                'success' => false,
                'error' => "Task #{$taskId} not found",
            ]);
        }

        // If user_id is provided, verify user exists
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                return Response::json([
                    'success' => false,
                    'error' => "User #{$userId} not found",
                ]);
            }
        }

        $assigned = $this->taskRepository->assign($taskId, $userId);

        if (!$assigned) {
            return Response::json([
                'success' => false,
                'error' => 'Failed to assign task',
            ]);
        }

        // Reload task to get updated data
        $task = $this->taskRepository->findById($taskId);

        if ($userId) {
            $assignee = $task->assignedTo;
            $message = "Task #{$taskId} assigned to {$assignee->name} ({$assignee->email})";
        } else {
            $message = "Task #{$taskId} unassigned";
        }

        return Response::json([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'assigned_to' => $task->assigned_to,
                'assignee' => $task->assignedTo ? [
                    'id' => $task->assignedTo->id,
                    'name' => $task->assignedTo->name,
                    'email' => $task->assignedTo->email,
                ] : null,
            ],
            'message' => $message,
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
                ->description('The ID of the task to assign')
                ->minimum(1)
                ->required(),
            'user_id' => $schema->integer()
                ->description('The ID of the user to assign the task to (null to unassign)')
                ->minimum(1)
                ->optional(),
        ];
    }
}
