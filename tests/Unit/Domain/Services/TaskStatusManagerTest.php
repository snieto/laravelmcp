<?php

namespace Tests\Unit\Domain\Services;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Domain\TaskManagement\Services\TaskStatusManager;
use App\Domain\TaskManagement\ValueObjects\Priority;
use App\Domain\TaskManagement\ValueObjects\Status;
use App\Infrastructure\Persistence\Eloquent\Models\Project;
use App\Infrastructure\Persistence\Eloquent\Models\Task;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskStatusManagerTest extends TestCase
{
    use RefreshDatabase;

    private TaskStatusManager $statusManager;
    private TaskRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(TaskRepositoryInterface::class);
        $this->statusManager = new TaskStatusManager($this->repository);
    }

    /** @test */
    public function it_allows_valid_status_transition()
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

        $result = $this->statusManager->transitionTo($task, Status::IN_PROGRESS);

        $this->assertTrue($result);

        $updatedTask = $this->repository->findById($task->id);
        $this->assertEquals(Status::IN_PROGRESS, $updatedTask->status);
    }

    /** @test */
    public function it_rejects_invalid_status_transition()
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

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot transition from pending to review');

        $this->statusManager->transitionTo($task, Status::REVIEW);
    }

    /** @test */
    public function it_prevents_transition_from_completed_status()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        $task = $this->repository->create([
            'project_id' => $project->id,
            'title' => 'Completed Task',
            'status' => Status::COMPLETED,
            'priority' => Priority::LOW,
            'created_by' => $user->id,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        $this->statusManager->transitionTo($task, Status::IN_PROGRESS);
    }

    /** @test */
    public function it_returns_all_possible_transitions_for_task()
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

        $transitions = $this->statusManager->getAvailableTransitions($task);

        $this->assertContains(Status::IN_PROGRESS, $transitions);
        $this->assertContains(Status::BLOCKED, $transitions);
        $this->assertNotContains(Status::REVIEW, $transitions);
        $this->assertNotContains(Status::COMPLETED, $transitions);
    }
}
