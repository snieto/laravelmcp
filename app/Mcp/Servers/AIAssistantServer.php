<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\AnalyzeProductivity;
use App\Mcp\Tools\GenerateTaskDescription;
use App\Mcp\Tools\ImproveTaskTitle;
use App\Mcp\Tools\SuggestPriority;
use Laravel\Mcp\Server;

class AIAssistantServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'TaskMaster AI - AI Assistant';

    /**
     * The MCP server's version.
     */
    protected string $version = '1.0.0';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = <<<'MARKDOWN'
        # TaskMaster AI - AI Assistant

        This MCP server provides AI-powered assistance for task management using OpenAI's GPT models.

        ## Available Tools

        - **generate_task_description**: Generate comprehensive technical descriptions for tasks
        - **improve_task_title**: Improve task titles to be more clear and actionable
        - **suggest_priority**: Calculate optimal task priority based on multiple factors
        - **analyze_productivity**: Analyze team productivity and identify bottlenecks

        ## Features

        1. **AI-Generated Content**: Uses GPT-4 to generate high-quality task descriptions
        2. **Smart Prioritization**: Analyzes due dates, complexity, and activity to suggest priorities
        3. **Productivity Insights**: Identifies bottlenecks and provides actionable recommendations
        4. **Title Optimization**: Improves task titles following best practices

        ## Requirements

        - OpenAI API key must be configured in OPENAI_API_KEY environment variable
        - Default model: gpt-4-turbo-preview (configurable via OPENAI_DEFAULT_MODEL)

        ## Usage Guidelines

        1. Ensure OpenAI API key is configured before using AI tools
        2. AI-generated content is suggestions - review before applying
        3. Productivity analysis works best with at least 7 days of data
        4. Priority suggestions consider multiple factors including urgency and complexity

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
        GenerateTaskDescription::class,
        ImproveTaskTitle::class,
        SuggestPriority::class,
        AnalyzeProductivity::class,
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
