<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;

class AnalyticsResourcesServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'Analytics Resources';

    /**
     * The MCP server's version.
     */
    protected string $version = '1.0.0';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = <<<'MARKDOWN'
        # Analytics Resources Server

        This server provides read-only access to analytics and metrics data through MCP Resources.

        ## Available Resources

        1. **team-metrics**: Get overall team performance metrics
           - Total tasks, completion rates, velocity
           - Status distribution, priority breakdown
           - Time-based trends (daily, weekly, monthly)

        2. **project-metrics/{project_id}**: Get detailed metrics for a specific project
           - Project completion percentage
           - Task breakdown by status and priority
           - Average task completion time
           - Team member contributions

        3. **user-productivity/{user_id}**: Get individual user productivity metrics
           - Tasks completed, in progress, pending
           - Average task completion time
           - Workload balance across priorities
           - Recent activity timeline

        4. **velocity-trends**: Get task velocity and trend analysis
           - Tasks completed per day/week/month
           - Velocity trends over time
           - Burndown/burnup data
           - Forecasted completion dates

        5. **overdue-report**: Get list of overdue tasks with analysis
           - All overdue tasks with details
           - Grouped by project and assignee
           - Risk assessment and impact analysis

        ## Usage Patterns

        Resources are read-only and automatically refresh with latest data.
        Use these resources to:
        - Monitor team performance in real-time
        - Identify bottlenecks and blockers
        - Track project health and progress
        - Analyze productivity trends
        - Generate insights for sprint planning

        ## Best Practices

        - Resources provide point-in-time snapshots
        - For historical analysis, use date range parameters
        - Combine multiple resources for comprehensive insights
        - Cache frequently accessed metrics for better performance
    MARKDOWN;

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        //
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        \App\Mcp\Resources\TeamMetrics::class,
        \App\Mcp\Resources\ProjectMetrics::class,
        \App\Mcp\Resources\UserProductivity::class,
        \App\Mcp\Resources\VelocityTrends::class,
        \App\Mcp\Resources\OverdueReport::class,
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
