<?php

declare(strict_types=1);

namespace App\Domain\TaskManagement\Contracts\Repositories;

use App\Infrastructure\Persistence\Eloquent\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

interface TagRepositoryInterface
{
    /**
     * Find a tag by its ID.
     */
    public function findById(int $id): ?Tag;

    /**
     * Find a tag by its slug.
     */
    public function findBySlug(string $slug): ?Tag;

    /**
     * Get all tags.
     */
    public function all(): Collection;

    /**
     * Create a new tag.
     */
    public function create(array $data): Tag;

    /**
     * Update an existing tag.
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a tag.
     */
    public function delete(int $id): bool;

    /**
     * Search tags by name or description.
     */
    public function search(string $query): Collection;

    /**
     * Get tags with their task count.
     */
    public function withTaskCount(): Collection;

    /**
     * Find or create a tag by name.
     */
    public function findOrCreate(string $name): Tag;
}
