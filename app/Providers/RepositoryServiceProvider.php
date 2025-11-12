<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\TaskManagement\Contracts\Repositories\CommentRepositoryInterface;
use App\Domain\TaskManagement\Contracts\Repositories\ProjectRepositoryInterface;
use App\Domain\TaskManagement\Contracts\Repositories\TagRepositoryInterface;
use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentCommentRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentProjectRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentTagRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentTaskRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        ProjectRepositoryInterface::class => EloquentProjectRepository::class,
        TaskRepositoryInterface::class => EloquentTaskRepository::class,
        TagRepositoryInterface::class => EloquentTagRepository::class,
        CommentRepositoryInterface::class => EloquentCommentRepository::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
