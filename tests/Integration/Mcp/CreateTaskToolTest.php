<?php

namespace Tests\Integration\Mcp;

use App\Domain\TaskManagement\ValueObjects\Priority;
use App\Infrastructure\Persistence\Eloquent\Models\Project;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Mcp\Tools\CreateTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Mcp\Request;
use Tests\TestCase;

class CreateTaskToolTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_task_successfully()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        $tool = app(CreateTask::class);

        $request = new Request([
            'project_id' => $project->id,
            'title' => 'New Task from MCP',
            'description' => 'This task was created via MCP',
            'priority' => 'high',
            'created_by' => $user->id,
        ]);

        $response = $tool->handle($request);
        $data = json_decode($response->content(), true);

        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('task', $data);
        $this->assertEquals('New Task from MCP', $data['task']['title']);
        $this->assertEquals('high', $data['task']['priority']);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $tool = app(CreateTask::class);

        $request = new Request([
            // Missing required fields
            'description' => 'Task without title',
        ]);

        $this->expectException(\Exception::class);
        $tool->handle($request);
    }

    /** @test */
    public function it_creates_task_with_default_priority()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        $tool = app(CreateTask::class);

        $request = new Request([
            'project_id' => $project->id,
            'title' => 'Task with default priority',
            'created_by' => $user->id,
        ]);

        $response = $tool->handle($request);
        $data = json_decode($response->content(), true);

        $this->assertTrue($data['success']);
        $this->assertEquals('medium', $data['task']['priority']);
    }

    /** @test */
    public function it_can_assign_task_during_creation()
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $creator->id]);

        $tool = app(CreateTask::class);

        $request = new Request([
            'project_id' => $project->id,
            'title' => 'Assigned Task',
            'created_by' => $creator->id,
            'assigned_to' => $assignee->id,
        ]);

        $response = $tool->handle($request);
        $data = json_decode($response->content(), true);

        $this->assertTrue($data['success']);
        $this->assertEquals($assignee->name, $data['task']['assigned_to']);
    }
}
