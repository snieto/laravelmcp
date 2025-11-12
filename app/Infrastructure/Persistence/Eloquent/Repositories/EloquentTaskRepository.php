<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Domain\TaskManagement\ValueObjects\Priority;
use App\Domain\TaskManagement\ValueObjects\Status;
use App\Infrastructure\Persistence\Eloquent\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    public function findById(int $id): ?Task
    {
        return Task::with(['project', 'assignedTo', 'createdBy', 'tags', 'comments'])->find($id);
    }

    public function all(): Collection
    {
        return Task::with(['project', 'assignedTo', 'tags'])
            ->latest()
            ->get();
    }

    public function findByProjectId(int $projectId): Collection
    {
        return Task::where('project_id', $projectId)
            ->with(['assignedTo', 'tags'])
            ->latest()
            ->get();
    }

    public function findByAssignedTo(int $userId): Collection
    {
        return Task::where('assigned_to', $userId)
            ->with(['project', 'tags'])
            ->latest()
            ->get();
    }

    public function findByCreator(int $userId): Collection
    {
        return Task::where('created_by', $userId)
            ->with(['project', 'assignedTo', 'tags'])
            ->latest()
            ->get();
    }

    public function findByStatus(Status $status): Collection
    {
        return Task::where('status', $status)
            ->with(['project', 'assignedTo', 'tags'])
            ->latest()
            ->get();
    }

    public function findByPriority(Priority $priority): Collection
    {
        return Task::byPriority($priority)
            ->with(['project', 'assignedTo', 'tags'])
            ->latest()
            ->get();
    }

    public function getHighPriority(): Collection
    {
        return Task::highPriority()
            ->with(['project', 'assignedTo', 'tags'])
            ->latest()
            ->get();
    }

    public function getOverdue(): Collection
    {
        return Task::overdue()
            ->with(['project', 'assignedTo', 'tags'])
            ->latest()
            ->get();
    }

    public function getPending(): Collection
    {
        return Task::pending()
            ->with(['project', 'assignedTo', 'tags'])
            ->latest()
            ->get();
    }

    public function getInProgress(): Collection
    {
        return Task::inProgress()
            ->with(['project', 'assignedTo', 'tags'])
            ->latest()
            ->get();
    }

    public function getCompleted(): Collection
    {
        return Task::completed()
            ->with(['project', 'assignedTo', 'tags'])
            ->latest()
            ->get();
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $task = Task::find($id);

        if (!$task) {
            return false;
        }

        return $task->update($data);
    }

    public function delete(int $id): bool
    {
        $task = Task::find($id);

        if (!$task) {
            return false;
        }

        return $task->delete();
    }

    public function updateStatus(int $id, Status $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }

    public function assign(int $taskId, int $userId): bool
    {
        return $this->update($taskId, ['assigned_to' => $userId]);
    }

    public function attachTags(int $taskId, array $tagIds): void
    {
        $task = Task::find($taskId);

        if ($task) {
            $task->tags()->attach($tagIds);
        }
    }

    public function detachTags(int $taskId, array $tagIds): void
    {
        $task = Task::find($taskId);

        if ($task) {
            $task->tags()->detach($tagIds);
        }
    }

    public function search(string $query): Collection
    {
        return Task::where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->with(['project', 'assignedTo', 'tags'])
            ->latest()
            ->get();
    }
}
