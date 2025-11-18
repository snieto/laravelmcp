<?php

namespace Tests\Integration\Mcp;

use App\Infrastructure\Persistence\Eloquent\Models\Task;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Mcp\Tools\AssignTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Mcp\Request;
use Tests\TestCase;

class AssignTaskToolTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_assigns_task_to_user_successfully()
    {
        // Arrange
        $task = Task::factory()->create(['assigned_to' => null]);
        $user = User::factory()->create();
        $tool = app(AssignTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals($user->id, $task->fresh()->assigned_to);
    }

    /** @test */
    public function it_reassigns_task_to_different_user()
    {
        // Arrange
        $originalUser = User::factory()->create();
        $newUser = User::factory()->create();
        $task = Task::factory()->create(['assigned_to' => $originalUser->id]);
        $tool = app(AssignTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => $task->id,
            'user_id' => $newUser->id,
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals($newUser->id, $task->fresh()->assigned_to);
    }

    /** @test */
    public function it_returns_error_when_task_not_found()
    {
        // Arrange
        $user = User::factory()->create();
        $tool = app(AssignTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => 999,
            'user_id' => $user->id,
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
    }

    /** @test */
    public function it_returns_error_when_user_not_found()
    {
        // Arrange
        $task = Task::factory()->create();
        $tool = app(AssignTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => $task->id,
            'user_id' => 999, // Non-existent user
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
    }

    /** @test */
    public function it_includes_assignee_info_in_response()
    {
        // Arrange
        $task = Task::factory()->create();
        $user = User::factory()->create(['name' => 'John Doe']);
        $tool = app(AssignTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('task', $data);
        $this->assertEquals('John Doe', $data['task']['assigned_to']['name']);
    }

    /** @test */
    public function it_can_unassign_task()
    {
        // Arrange
        $user = User::factory()->create();
        $task = Task::factory()->create(['assigned_to' => $user->id]);
        $tool = app(AssignTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => $task->id,
            'user_id' => null, // Unassign
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertNull($task->fresh()->assigned_to);
    }
}
