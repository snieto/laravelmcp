<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\AssignTask;
use App\Mcp\Tools\CreateTask;
use App\Mcp\Tools\DeleteTask;
use App\Mcp\Tools\GetTask;
use App\Mcp\Tools\ListTasks;
use App\Mcp\Tools\UpdateTask;
use Laravel\Mcp\Server;

class TaskToolsServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'TaskMaster AI - Task Tools';

    /**
     * The MCP server's version.
     */
    protected string $version = '1.0.0';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = <<<'MARKDOWN'
        # TaskMaster AI - Task Management Tools

        This MCP server provides comprehensive task management capabilities for the TaskMaster AI platform.

        ## Available Tools

        - **create_task**: Create a new task in a project
        - **list_tasks**: List and filter tasks by project, status, assignee, or search
        - **get_task**: Get detailed information about a specific task
        - **update_task**: Update task details, status, priority, or assignments
        - **delete_task**: Delete a task
        - **assign_task**: Assign a task to a user

        ## Usage Guidelines

        1. Always specify required fields when creating tasks (project_id, title, created_by)
        2. Use appropriate priority levels: low, medium, high, critical
        3. Status transitions follow rules: pending → in_progress → review → completed
        4. Search tasks using keywords in title or description
        5. Filter tasks by project, status, or assignee for focused views

        ## Authentication

        This server requires authentication via Laravel Sanctum tokens.
        Include your token in the Authorization header.
    MARKDOWN;

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        CreateTask::class,
        ListTasks::class,
        GetTask::class,
        UpdateTask::class,
        DeleteTask::class,
        AssignTask::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        //
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [
        //
    ];
}
