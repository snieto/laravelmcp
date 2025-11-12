<?php

namespace Tests\Feature\Repositories;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Domain\TaskManagement\ValueObjects\Priority;
use App\Domain\TaskManagement\ValueObjects\Status;
use App\Infrastructure\Persistence\Eloquent\Models\Project;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TaskRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(TaskRepositoryInterface::class);
    }

    /** @test */
    public function it_can_create_a_task()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        $taskData = [
            'project_id' => $project->id,
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => Status::PENDING,
            'priority' => Priority::MEDIUM,
            'created_by' => $user->id,
        ];

        $task = $this->repository->create($taskData);

        $this->assertNotNull($task);
        $this->assertEquals('Test Task', $task->title);
        $this->assertEquals(Status::PENDING, $task->status);
        $this->assertEquals(Priority::MEDIUM, $task->priority);
    }

    /** @test */
    public function it_can_find_tasks_by_status()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        $this->repository->create([
            'project_id' => $project->id,
            'title' => 'Pending Task',
            'status' => Status::PENDING,
            'priority' => Priority::LOW,
            'created_by' => $user->id,
        ]);

        $this->repository->create([
            'project_id' => $project->id,
            'title' => 'Completed Task',
            'status' => Status::COMPLETED,
            'priority' => Priority::LOW,
            'created_by' => $user->id,
        ]);

        $pendingTasks = $this->repository->findByStatus(Status::PENDING);
        $this->assertCount(1, $pendingTasks);
        $this->assertEquals('Pending Task', $pendingTasks->first()->title);
    }

    /** @test */
    public function it_can_update_task_status()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        $task = $this->repository->create([
            'project_id' => $project->id,
            'title' => 'Test Task',
            'status' => Status::PENDING,
            'priority' => Priority::LOW,
            'created_by' => $user->id,
        ]);

        $this->repository->updateStatus($task->id, Status::IN_PROGRESS);

        $updatedTask = $this->repository->findById($task->id);
        $this->assertEquals(Status::IN_PROGRESS, $updatedTask->status);
    }

    /** @test */
    public function it_can_assign_task_to_user()
    {
        $owner = User::factory()->create();
        $assignee = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $task = $this->repository->create([
            'project_id' => $project->id,
            'title' => 'Test Task',
            'status' => Status::PENDING,
            'priority' => Priority::LOW,
            'created_by' => $owner->id,
        ]);

        $this->repository->assign($task->id, $assignee->id);

        $assignedTask = $this->repository->findById($task->id);
        $this->assertEquals($assignee->id, $assignedTask->assigned_to);
    }

    /** @test */
    public function it_can_search_tasks()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        $this->repository->create([
            'project_id' => $project->id,
            'title' => 'Bug Fix for Login',
            'status' => Status::PENDING,
            'priority' => Priority::HIGH,
            'created_by' => $user->id,
        ]);

        $this->repository->create([
            'project_id' => $project->id,
            'title' => 'Feature: Dashboard',
            'status' => Status::PENDING,
            'priority' => Priority::LOW,
            'created_by' => $user->id,
        ]);

        $results = $this->repository->search('Login');
        $this->assertCount(1, $results);
        $this->assertEquals('Bug Fix for Login', $results->first()->title);
    }
}
