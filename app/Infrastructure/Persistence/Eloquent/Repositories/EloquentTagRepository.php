<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\TaskManagement\Contracts\Repositories\TagRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

class EloquentTagRepository implements TagRepositoryInterface
{
    public function findById(int $id): ?Tag
    {
        return Tag::find($id);
    }

    public function findBySlug(string $slug): ?Tag
    {
        return Tag::findBySlug($slug);
    }

    public function all(): Collection
    {
        return Tag::orderBy('name')->get();
    }

    public function create(array $data): Tag
    {
        return Tag::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $tag = $this->findById($id);

        if (!$tag) {
            return false;
        }

        return $tag->update($data);
    }

    public function delete(int $id): bool
    {
        $tag = $this->findById($id);

        if (!$tag) {
            return false;
        }

        return $tag->delete();
    }

    public function search(string $query): Collection
    {
        return Tag::search($query)->get();
    }

    public function withTaskCount(): Collection
    {
        return Tag::withCount('tasks')
            ->orderBy('name')
            ->get();
    }

    public function findOrCreate(string $name): Tag
    {
        return Tag::firstOrCreate(
            ['name' => $name],
            ['color' => '#6B7280']
        );
    }
}
