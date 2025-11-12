<?php

declare(strict_types=1);

namespace App\Domain\TaskManagement\Services;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Domain\TaskManagement\ValueObjects\Status;
use App\Infrastructure\Persistence\Eloquent\Models\Task;
use InvalidArgumentException;

class TaskStatusManager
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository
    ) {
    }

    /**
     * Transition a task to a new status.
     *
     * @throws InvalidArgumentException If the transition is not allowed
     */
    public function transitionTo(Task $task, Status $newStatus): bool
    {
        $currentStatus = $task->status;

        // Check if transition is valid
        if (!$currentStatus->canTransitionTo($newStatus)) {
            throw new InvalidArgumentException(
                "Cannot transition from {$currentStatus->value} to {$newStatus->value}"
            );
        }

        // Update the task status
        return $this->taskRepository->updateStatus($task->id, $newStatus);
    }

    /**
     * Mark a task as in progress.
     */
    public function markAsInProgress(Task $task): bool
    {
        return $this->transitionTo($task, Status::IN_PROGRESS);
    }

    /**
     * Mark a task as completed.
     */
    public function markAsCompleted(Task $task): bool
    {
        return $this->transitionTo($task, Status::COMPLETED);
    }

    /**
     * Mark a task as blocked.
     */
    public function markAsBlocked(Task $task): bool
    {
        return $this->transitionTo($task, Status::BLOCKED);
    }

    /**
     * Mark a task as in review.
     */
    public function markAsReview(Task $task): bool
    {
        return $this->transitionTo($task, Status::REVIEW);
    }

    /**
     * Reopen a task (move back to pending).
     */
    public function reopen(Task $task): bool
    {
        return $this->transitionTo($task, Status::PENDING);
    }

    /**
     * Get available transitions for a task's current status.
     *
     * @return array<Status>
     */
    public function getAvailableTransitions(Task $task): array
    {
        return $task->status->availableTransitions();
    }

    /**
     * Check if a transition is valid for a task.
     */
    public function canTransition(Task $task, Status $newStatus): bool
    {
        return $task->status->canTransitionTo($newStatus);
    }

    /**
     * Get the status label.
     */
    public function getStatusLabel(Status $status): string
    {
        return $status->label();
    }

    /**
     * Get the status color for UI.
     */
    public function getStatusColor(Status $status): string
    {
        return $status->color();
    }
}
