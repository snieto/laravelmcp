<?php

declare(strict_types=1);

namespace App\Domain\TaskManagement\Services;

use App\Domain\TaskManagement\ValueObjects\Priority;
use App\Infrastructure\Persistence\Eloquent\Models\Task;
use Carbon\Carbon;

class TaskPriorityCalculator
{
    /**
     * Calculate suggested priority based on task attributes.
     */
    public function calculateSuggestedPriority(Task $task): Priority
    {
        $score = 0;

        // Factor 1: Due date urgency
        if ($task->due_date) {
            $daysUntilDue = Carbon::now()->diffInDays($task->due_date, false);

            if ($daysUntilDue < 0) {
                // Overdue
                $score += 4;
            } elseif ($daysUntilDue <= 1) {
                // Due today or tomorrow
                $score += 3;
            } elseif ($daysUntilDue <= 3) {
                // Due within 3 days
                $score += 2;
            } elseif ($daysUntilDue <= 7) {
                // Due within a week
                $score += 1;
            }
        }

        // Factor 2: Current priority
        $score += $task->priority->score();

        // Factor 3: Task complexity (based on estimated hours)
        if ($task->estimated_hours) {
            if ($task->estimated_hours >= 20) {
                // Large task
                $score += 2;
            } elseif ($task->estimated_hours >= 10) {
                // Medium task
                $score += 1;
            }
        }

        // Factor 4: Has comments (indicates active discussion)
        if ($task->comments()->count() > 5) {
            $score += 1;
        }

        // Convert score to priority
        return $this->scoreToPriority($score);
    }

    /**
     * Convert numerical score to Priority enum.
     */
    private function scoreToPriority(int $score): Priority
    {
        return match (true) {
            $score >= 8 => Priority::CRITICAL,
            $score >= 5 => Priority::HIGH,
            $score >= 3 => Priority::MEDIUM,
            default => Priority::LOW,
        };
    }

    /**
     * Compare two tasks and determine which has higher priority.
     *
     * @return int -1 if task1 < task2, 0 if equal, 1 if task1 > task2
     */
    public function compare(Task $task1, Task $task2): int
    {
        $priority1 = $this->calculateSuggestedPriority($task1);
        $priority2 = $this->calculateSuggestedPriority($task2);

        return $priority1->score() <=> $priority2->score();
    }

    /**
     * Sort a collection of tasks by calculated priority.
     *
     * @param  array<Task>  $tasks
     * @return array<Task>
     */
    public function sortByPriority(array $tasks): array
    {
        usort($tasks, fn ($a, $b) => $this->compare($b, $a)); // Descending order

        return $tasks;
    }

    /**
     * Get tasks that should be escalated to higher priority.
     *
     * @param  array<Task>  $tasks
     * @return array<Task>
     */
    public function getTasksForEscalation(array $tasks): array
    {
        return array_filter($tasks, function (Task $task) {
            $suggestedPriority = $this->calculateSuggestedPriority($task);
            $currentPriority = $task->priority;

            // Suggest escalation if suggested priority is higher than current
            return $suggestedPriority->score() > $currentPriority->score();
        });
    }
}
