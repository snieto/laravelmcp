<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\TaskManagement\Contracts\Repositories\ProjectRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Project;
use Illuminate\Database\Eloquent\Collection;

class EloquentProjectRepository implements ProjectRepositoryInterface
{
    public function findById(int $id): ?Project
    {
        return Project::find($id);
    }

    public function all(): Collection
    {
        return Project::with('owner')->latest()->get();
    }

    public function findByOwnerId(int $ownerId): Collection
    {
        return Project::where('owner_id', $ownerId)
            ->with('tasks')
            ->latest()
            ->get();
    }

    public function getActive(): Collection
    {
        return Project::active()
            ->with('owner', 'tasks')
            ->latest()
            ->get();
    }

    public function getArchived(): Collection
    {
        return Project::archived()
            ->with('owner')
            ->latest()
            ->get();
    }

    public function create(array $data): Project
    {
        return Project::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $project = $this->findById($id);

        if (!$project) {
            return false;
        }

        return $project->update($data);
    }

    public function delete(int $id): bool
    {
        $project = $this->findById($id);

        if (!$project) {
            return false;
        }

        return $project->delete();
    }

    public function search(string $query): Collection
    {
        return Project::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->with('owner')
            ->latest()
            ->get();
    }

    public function withTaskCount(): Collection
    {
        return Project::withCount('tasks')
            ->with('owner')
            ->latest()
            ->get();
    }
}
