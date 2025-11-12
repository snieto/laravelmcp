<?php

declare(strict_types=1);

namespace App\Domain\TaskManagement\Contracts\Repositories;

use App\Infrastructure\Persistence\Eloquent\Models\Project;
use Illuminate\Database\Eloquent\Collection;

interface ProjectRepositoryInterface
{
    /**
     * Find a project by its ID.
     */
    public function findById(int $id): ?Project;

    /**
     * Get all projects.
     */
    public function all(): Collection;

    /**
     * Get projects by owner ID.
     */
    public function findByOwnerId(int $ownerId): Collection;

    /**
     * Get active projects.
     */
    public function getActive(): Collection;

    /**
     * Get archived projects.
     */
    public function getArchived(): Collection;

    /**
     * Create a new project.
     */
    public function create(array $data): Project;

    /**
     * Update an existing project.
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a project.
     */
    public function delete(int $id): bool;

    /**
     * Search projects by name or description.
     */
    public function search(string $query): Collection;

    /**
     * Get projects with their task count.
     */
    public function withTaskCount(): Collection;
}
