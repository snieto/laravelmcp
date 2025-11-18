<?php

namespace Tests\Integration\Mcp;

use App\Infrastructure\Persistence\Eloquent\Models\Task;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Mcp\Tools\GetTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Mcp\Request;
use Tests\TestCase;

class GetTaskToolTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_retrieves_task_by_id_successfully()
    {
        // Arrange
        $task = Task::factory()->create([
            'title' => 'Test Task',
            'description' => 'Test Description',
        ]);
        $tool = app(GetTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => $task->id,
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals($task->id, $data['task']['id']);
        $this->assertEquals('Test Task', $data['task']['title']);
    }

    /** @test */
    public function it_includes_related_project_data()
    {
        // Arrange
        $task = Task::factory()
            ->for(\App\Infrastructure\Persistence\Eloquent\Models\Project::factory())
            ->create();
        $tool = app(GetTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => $task->id,
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('project', $data['task']);
        $this->assertNotNull($data['task']['project']);
    }

    /** @test */
    public function it_includes_assigned_user_data()
    {
        // Arrange
        $user = User::factory()->create(['name' => 'John Doe']);
        $task = Task::factory()->create(['assigned_to' => $user->id]);
        $tool = app(GetTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => $task->id,
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('assigned_to', $data['task']);
        $this->assertEquals('John Doe', $data['task']['assigned_to']['name']);
    }

    /** @test */
    public function it_includes_tags()
    {
        // Arrange
        $task = Task::factory()
            ->hasTags(3)
            ->create();
        $tool = app(GetTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => $task->id,
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('tags', $data['task']);
        $this->assertCount(3, $data['task']['tags']);
    }

    /** @test */
    public function it_includes_comments()
    {
        // Arrange
        $task = Task::factory()
            ->hasComments(5)
            ->create();
        $tool = app(GetTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => $task->id,
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('comments', $data['task']);
        $this->assertCount(5, $data['task']['comments']);
    }

    /** @test */
    public function it_returns_error_when_task_not_found()
    {
        // Arrange
        $tool = app(GetTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => 999,
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
    }

    /** @test */
    public function it_does_not_return_soft_deleted_tasks()
    {
        // Arrange
        $task = Task::factory()->create();
        $task->delete(); // Soft delete
        $tool = app(GetTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => $task->id,
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
    }
}
