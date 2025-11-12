<?php

namespace App\Mcp\Tools;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Domain\TaskManagement\Services\TaskPriorityCalculator;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class SuggestPriority extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Calculate and suggest an optimal priority for a task based on multiple factors:
        - Due date urgency
        - Current priority
        - Task complexity (estimated hours)
        - Activity level (number of comments)

        Returns suggested priority with explanation.
    MARKDOWN;

    public function __construct(
        private readonly TaskPriorityCalculator $priorityCalculator,
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

        $suggestedPriority = $this->priorityCalculator->calculateSuggestedPriority($task);
        $currentPriority = $task->priority;

        // Build explanation
        $factors = [];

        if ($task->due_date) {
            $daysUntil = now()->diffInDays($task->due_date, false);
            if ($daysUntil < 0) {
                $factors[] = "Task is overdue by ".abs($daysUntil).' days';
            } elseif ($daysUntil <= 1) {
                $factors[] = 'Task is due very soon (within 24 hours)';
            } elseif ($daysUntil <= 3) {
                $factors[] = 'Task is due soon (within 3 days)';
            }
        }

        if ($task->estimated_hours && $task->estimated_hours >= 20) {
            $factors[] = 'Large task ('.$task->estimated_hours.' estimated hours)';
        }

        if ($task->comments()->count() > 5) {
            $factors[] = 'High activity ('.$task->comments()->count().' comments)';
        }

        $shouldEscalate = $suggestedPriority->score() > $currentPriority->score();

        return Response::json([
            'success' => true,
            'task_id' => $task->id,
            'task_title' => $task->title,
            'current_priority' => $currentPriority->value,
            'suggested_priority' => $suggestedPriority->value,
            'should_escalate' => $shouldEscalate,
            'priority_change' => $shouldEscalate ? 'increase' : ($suggestedPriority->score() < $currentPriority->score() ? 'decrease' : 'no change'),
            'factors' => $factors,
            'explanation' => $shouldEscalate
                ? "Consider escalating this task to {$suggestedPriority->value} priority based on the factors above."
                : "Current priority level seems appropriate for this task.",
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
                ->description('The ID of the task to analyze')
                ->minimum(1)
                ->required(),
        ];
    }
}
