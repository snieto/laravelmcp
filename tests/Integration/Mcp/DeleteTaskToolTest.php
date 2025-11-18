<?php

namespace Tests\Integration\Mcp;

use App\Infrastructure\Persistence\Eloquent\Models\Task;
use App\Mcp\Tools\DeleteTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Mcp\Request;
use Tests\TestCase;

class DeleteTaskToolTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_soft_deletes_task_successfully()
    {
        // Arrange
        $task = Task::factory()->create(['title' => 'Task to Delete']);
        $taskId = $task->id;
        $tool = app(DeleteTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => $taskId,
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);

        // Task should be soft deleted
        $this->assertSoftDeleted('tasks', ['id' => $taskId]);

        // Can still find with trashed()
        $this->assertNotNull(Task::withTrashed()->find($taskId));
    }

    /** @test */
    public function it_returns_error_when_task_not_found()
    {
        // Arrange
        $tool = app(DeleteTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => 999, // Non-existent
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
    }

    /** @test */
    public function it_includes_deleted_task_info_in_response()
    {
        // Arrange
        $task = Task::factory()->create([
            'title' => 'Important Task',
        ]);
        $tool = app(DeleteTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => $task->id,
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('message', $data);
    }

    /** @test */
    public function it_deletes_task_with_relationships()
    {
        // Arrange
        $task = Task::factory()
            ->hasComments(3)
            ->create();

        $taskId = $task->id;
        $tool = app(DeleteTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => $taskId,
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertSoftDeleted('tasks', ['id' => $taskId]);

        // Comments should still exist (or cascade depending on setup)
        // This depends on your database constraints
    }
}
