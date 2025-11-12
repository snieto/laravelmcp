<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;

class ReportsServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'Reports';

    /**
     * The MCP server's version.
     */
    protected string $version = '1.0.0';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = <<<'MARKDOWN'
        # Reports Server

        This server provides tools for generating comprehensive reports and exports.

        ## Available Tools

        1. **generate-sprint-report**: Generate a detailed sprint report
           - Sprint summary with completion metrics
           - Velocity analysis and trends
           - Team performance breakdown
           - Blockers and impediments
           - Export formats: JSON, Markdown, HTML

        2. **generate-project-status-report**: Generate project status report
           - Project health overview
           - Task completion status
           - Budget and time tracking
           - Risk assessment
           - Team contribution metrics
           - Export formats: JSON, Markdown, HTML, PDF

        3. **generate-user-performance-report**: Generate individual user performance report
           - Task completion statistics
           - Productivity metrics
           - Workload analysis
           - Time tracking summary
           - Strengths and improvement areas
           - Export formats: JSON, Markdown, HTML

        4. **generate-executive-summary**: Generate executive summary report
           - High-level organizational metrics
           - Cross-project analysis
           - Team capacity overview
           - Key achievements and blockers
           - Strategic recommendations
           - Export formats: JSON, Markdown, HTML, PDF

        5. **export-tasks**: Export filtered tasks to various formats
           - Filter by project, status, priority, assignee
           - Date range filtering
           - Export formats: CSV, JSON, Excel

        ## Usage Patterns

        - Use specific date ranges for historical reports
        - Combine reports for comprehensive project reviews
        - Schedule recurring reports for sprint planning
        - Export data for external analysis tools

        ## Best Practices

        - Generate sprint reports at sprint end
        - Create project status reports weekly
        - Use executive summaries for stakeholder updates
        - Export tasks regularly for backup purposes
    MARKDOWN;

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        \App\Mcp\Tools\GenerateSprintReport::class,
        \App\Mcp\Tools\GenerateProjectStatusReport::class,
        \App\Mcp\Tools\GenerateUserPerformanceReport::class,
        \App\Mcp\Tools\GenerateExecutiveSummary::class,
        \App\Mcp\Tools\ExportTasks::class,
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
