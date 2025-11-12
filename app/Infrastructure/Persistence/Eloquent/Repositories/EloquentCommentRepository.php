<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\TaskManagement\Contracts\Repositories\CommentRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Comment;
use Illuminate\Database\Eloquent\Collection;

class EloquentCommentRepository implements CommentRepositoryInterface
{
    public function findById(int $id): ?Comment
    {
        return Comment::with(['task', 'user'])->find($id);
    }

    public function findByTaskId(int $taskId): Collection
    {
        return Comment::where('task_id', $taskId)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function findByUserId(int $userId): Collection
    {
        return Comment::where('user_id', $userId)
            ->with('task')
            ->latest()
            ->get();
    }

    public function getRecent(int $limit = 10): Collection
    {
        return Comment::recent($limit)
            ->with(['task', 'user'])
            ->get();
    }

    public function create(array $data): Comment
    {
        return Comment::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return false;
        }

        return $comment->update($data);
    }

    public function delete(int $id): bool
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return false;
        }

        return $comment->delete();
    }
}
