<?php

declare(strict_types=1);

namespace App\Domain\TaskManagement\Contracts\Repositories;

use App\Domain\TaskManagement\ValueObjects\Priority;
use App\Domain\TaskManagement\ValueObjects\Status;
use App\Infrastructure\Persistence\Eloquent\Models\Task;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    /**
     * Find a task by its ID.
     */
    public function findById(int $id): ?Task;

    /**
     * Get all tasks.
     */
    public function all(): Collection;

    /**
     * Get tasks by project ID.
     */
    public function findByProjectId(int $projectId): Collection;

    /**
     * Get tasks assigned to a specific user.
     */
    public function findByAssignedTo(int $userId): Collection;

    /**
     * Get tasks created by a specific user.
     */
    public function findByCreator(int $userId): Collection;

    /**
     * Get tasks by status.
     */
    public function findByStatus(Status $status): Collection;

    /**
     * Get tasks by priority.
     */
    public function findByPriority(Priority $priority): Collection;

    /**
     * Get high priority tasks.
     */
    public function getHighPriority(): Collection;

    /**
     * Get overdue tasks.
     */
    public function getOverdue(): Collection;

    /**
     * Get pending tasks.
     */
    public function getPending(): Collection;

    /**
     * Get in-progress tasks.
     */
    public function getInProgress(): Collection;

    /**
     * Get completed tasks.
     */
    public function getCompleted(): Collection;

    /**
     * Create a new task.
     */
    public function create(array $data): Task;

    /**
     * Update an existing task.
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a task.
     */
    public function delete(int $id): bool;

    /**
     * Update task status.
     */
    public function updateStatus(int $id, Status $status): bool;

    /**
     * Assign task to a user.
     */
    public function assign(int $taskId, int $userId): bool;

    /**
     * Attach tags to a task.
     */
    public function attachTags(int $taskId, array $tagIds): void;

    /**
     * Detach tags from a task.
     */
    public function detachTags(int $taskId, array $tagIds): void;

    /**
     * Search tasks by title or description.
     */
    public function search(string $query): Collection;
}
