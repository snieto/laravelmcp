<?php

namespace Tests\Integration\Mcp;

use App\Domain\TaskManagement\ValueObjects\Priority;
use App\Domain\TaskManagement\ValueObjects\Status;
use App\Infrastructure\Persistence\Eloquent\Models\Project;
use App\Infrastructure\Persistence\Eloquent\Models\Task;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Mcp\Tools\UpdateTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Mcp\Request;
use Tests\TestCase;

class UpdateTaskToolTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_updates_task_title_successfully()
    {
        // Arrange
        $task = Task::factory()->create([
            'title' => 'Old Title',
            'priority' => Priority::LOW,
        ]);

        $tool = app(UpdateTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => $task->id,
            'title' => 'New Updated Title',
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals('New Updated Title', $task->fresh()->title);
    }

    /** @test */
    public function it_updates_task_priority()
    {
        // Arrange
        $task = Task::factory()->create(['priority' => Priority::LOW]);
        $tool = app(UpdateTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => $task->id,
            'priority' => 'high',
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals(Priority::HIGH, $task->fresh()->priority);
    }

    /** @test */
    public function it_updates_task_status_with_valid_transition()
    {
        // Arrange
        $task = Task::factory()->create(['status' => Status::PENDING]);
        $tool = app(UpdateTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => $task->id,
            'status' => 'in_progress',
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals(Status::IN_PROGRESS, $task->fresh()->status);
    }

    /** @test */
    public function it_updates_multiple_fields_at_once()
    {
        // Arrange
        $task = Task::factory()->create([
            'title' => 'Old Title',
            'description' => 'Old Description',
            'priority' => Priority::LOW,
        ]);
        $tool = app(UpdateTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => $task->id,
            'title' => 'New Title',
            'description' => 'New Description',
            'priority' => 'critical',
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);

        $freshTask = $task->fresh();
        $this->assertEquals('New Title', $freshTask->title);
        $this->assertEquals('New Description', $freshTask->description);
        $this->assertEquals(Priority::CRITICAL, $freshTask->priority);
    }

    /** @test */
    public function it_returns_error_when_task_not_found()
    {
        // Arrange
        $tool = app(UpdateTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => 999, // Non-existent
            'title' => 'New Title',
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
    }

    /** @test */
    public function it_updates_due_date()
    {
        // Arrange
        $task = Task::factory()->create();
        $tool = app(UpdateTask::class);
        $newDueDate = now()->addDays(7)->toDateString();

        // Act
        $response = $tool->handle(new Request([
            'task_id' => $task->id,
            'due_date' => $newDueDate,
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals(
            $newDueDate,
            $task->fresh()->due_date->toDateString()
        );
    }

    /** @test */
    public function it_updates_estimated_hours()
    {
        // Arrange
        $task = Task::factory()->create(['estimated_hours' => 5]);
        $tool = app(UpdateTask::class);

        // Act
        $response = $tool->handle(new Request([
            'task_id' => $task->id,
            'estimated_hours' => 10,
        ]));

        // Assert
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals(10, $task->fresh()->estimated_hours);
    }
}
