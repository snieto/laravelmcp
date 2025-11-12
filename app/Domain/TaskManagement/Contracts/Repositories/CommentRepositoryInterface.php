<?php

declare(strict_types=1);

namespace App\Domain\TaskManagement\Contracts\Repositories;

use App\Infrastructure\Persistence\Eloquent\Models\Comment;
use Illuminate\Database\Eloquent\Collection;

interface CommentRepositoryInterface
{
    /**
     * Find a comment by its ID.
     */
    public function findById(int $id): ?Comment;

    /**
     * Get all comments for a specific task.
     */
    public function findByTaskId(int $taskId): Collection;

    /**
     * Get comments by a specific user.
     */
    public function findByUserId(int $userId): Collection;

    /**
     * Get recent comments.
     */
    public function getRecent(int $limit = 10): Collection;

    /**
     * Create a new comment.
     */
    public function create(array $data): Comment;

    /**
     * Update an existing comment.
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a comment.
     */
    public function delete(int $id): bool;
}
