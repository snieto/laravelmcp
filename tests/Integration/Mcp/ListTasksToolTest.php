<?php

namespace Tests\Integration\Mcp;

use App\Domain\TaskManagement\ValueObjects\Priority;
use App\Domain\TaskManagement\ValueObjects\Status;
use App\Infrastructure\Persistence\Eloquent\Models\Project;
use App\Infrastructure\Persistence\Eloquent\Models\Task;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Mcp\Tools\ListTasks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Mcp\Request;
use Tests\TestCase;

class ListTasksToolTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_lists_all_tasks_successfully()
    {
        // Arrange
        Task::factory()->count(5)->create();
        $tool = app(ListTasks::class);

        // Act
        $response = $tool->handle(new Request([]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertCount(5, $data['tasks']);
    }

    /** @test */
    public function it_filters_tasks_by_project()
    {
        // Arrange
        $project1 = Project::factory()->create();
        $project2 = Project::factory()->create();

        Task::factory()->count(3)->create(['project_id' => $project1->id]);
        Task::factory()->count(2)->create(['project_id' => $project2->id]);

        $tool = app(ListTasks::class);

        // Act
        $response = $tool->handle(new Request([
            'project_id' => $project1->id,
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertCount(3, $data['tasks']);
    }

    /** @test */
    public function it_filters_tasks_by_status()
    {
        // Arrange
        Task::factory()->count(3)->create(['status' => Status::PENDING]);
        Task::factory()->count(2)->create(['status' => Status::COMPLETED]);
        Task::factory()->count(1)->create(['status' => Status::IN_PROGRESS]);

        $tool = app(ListTasks::class);

        // Act
        $response = $tool->handle(new Request([
            'status' => 'pending',
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertCount(3, $data['tasks']);

        foreach ($data['tasks'] as $task) {
            $this->assertEquals('pending', $task['status']);
        }
    }

    /** @test */
    public function it_filters_tasks_by_priority()
    {
        // Arrange
        Task::factory()->count(4)->create(['priority' => Priority::HIGH]);
        Task::factory()->count(3)->create(['priority' => Priority::LOW]);

        $tool = app(ListTasks::class);

        // Act
        $response = $tool->handle(new Request([
            'priority' => 'high',
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertCount(4, $data['tasks']);
    }

    /** @test */
    public function it_filters_tasks_by_assigned_user()
    {
        // Arrange
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Task::factory()->count(5)->create(['assigned_to' => $user1->id]);
        Task::factory()->count(3)->create(['assigned_to' => $user2->id]);

        $tool = app(ListTasks::class);

        // Act
        $response = $tool->handle(new Request([
            'assigned_to' => $user1->id,
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertCount(5, $data['tasks']);
    }

    /** @test */
    public function it_combines_multiple_filters()
    {
        // Arrange
        $project = Project::factory()->create();
        $user = User::factory()->create();

        Task::factory()->count(3)->create([
            'project_id' => $project->id,
            'assigned_to' => $user->id,
            'status' => Status::IN_PROGRESS,
            'priority' => Priority::HIGH,
        ]);

        // Other tasks that shouldn't match
        Task::factory()->count(2)->create([
            'project_id' => $project->id,
            'status' => Status::COMPLETED,
        ]);

        $tool = app(ListTasks::class);

        // Act
        $response = $tool->handle(new Request([
            'project_id' => $project->id,
            'assigned_to' => $user->id,
            'status' => 'in_progress',
            'priority' => 'high',
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertCount(3, $data['tasks']);
    }

    /** @test */
    public function it_returns_empty_array_when_no_tasks_match()
    {
        // Arrange
        Task::factory()->count(5)->create(['status' => Status::PENDING]);
        $tool = app(ListTasks::class);

        // Act
        $response = $tool->handle(new Request([
            'status' => 'completed',
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertCount(0, $data['tasks']);
    }

    /** @test */
    public function it_includes_related_data_in_task_list()
    {
        // Arrange
        $task = Task::factory()
            ->for(Project::factory())
            ->for(User::factory(), 'assignedTo')
            ->hasTags(2)
            ->create();

        $tool = app(ListTasks::class);

        // Act
        $response = $tool->handle(new Request([]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);

        $returnedTask = $data['tasks'][0];
        $this->assertArrayHasKey('project', $returnedTask);
        $this->assertArrayHasKey('assigned_to', $returnedTask);
        $this->assertArrayHasKey('tags', $returnedTask);
    }

    /** @test */
    public function it_orders_tasks_by_created_at_desc()
    {
        // Arrange
        $oldTask = Task::factory()->create(['created_at' => now()->subDays(3)]);
        $newTask = Task::factory()->create(['created_at' => now()]);

        $tool = app(ListTasks::class);

        // Act
        $response = $tool->handle(new Request([]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);

        // First task should be the newest
        $this->assertEquals($newTask->id, $data['tasks'][0]['id']);
        $this->assertEquals($oldTask->id, $data['tasks'][1]['id']);
    }

    /** @test */
    public function it_does_not_include_soft_deleted_tasks()
    {
        // Arrange
        Task::factory()->count(3)->create();
        $deletedTask = Task::factory()->create();
        $deletedTask->delete(); // Soft delete

        $tool = app(ListTasks::class);

        // Act
        $response = $tool->handle(new Request([]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertCount(3, $data['tasks']); // Only non-deleted tasks
    }
}
