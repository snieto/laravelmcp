<?php

declare(strict_types=1);

namespace App\Domain\AIIntegration\Services;

use App\Infrastructure\Persistence\Eloquent\Models\Task;

class PromptBuilder
{
    /**
     * Build a prompt for generating task descriptions.
     */
    public function buildTaskDescriptionPrompt(Task $task): string
    {
        $project = $task->project;

        return <<<PROMPT
        You are a technical project manager. Generate a detailed technical description for the following task.

        **Project:** {$project->name}
        **Task Title:** {$task->title}
        **Priority:** {$task->priority->value}
        **Current Description:** {$task->description}

        Generate a comprehensive technical description that includes:
        1. Overview of what needs to be done
        2. Technical approach or considerations
        3. Potential challenges or dependencies
        4. Expected outcome

        Keep it concise but informative. Use markdown formatting.
        PROMPT;
    }

    /**
     * Build a prompt for generating acceptance criteria.
     */
    public function buildAcceptanceCriteriaPrompt(Task $task): string
    {
        return <<<PROMPT
        Generate clear acceptance criteria for this task:

        **Title:** {$task->title}
        **Description:** {$task->description}

        Return a bullet-point list of specific, testable acceptance criteria.
        Each criterion should be measurable and verifiable.
        Use markdown formatting.
        PROMPT;
    }

    /**
     * Build a prompt for suggesting subtasks.
     */
    public function buildSubtaskPrompt(Task $task): string
    {
        return <<<PROMPT
        Break down this task into smaller, actionable subtasks:

        **Title:** {$task->title}
        **Description:** {$task->description}
        **Estimated Hours:** {$task->estimated_hours}

        Suggest 3-7 logical subtasks that together accomplish this task.
        Each subtask should be completable independently.
        PROMPT;
    }

    /**
     * Build a prompt for productivity analysis.
     */
    public function buildProductivityAnalysisPrompt(array $metrics): string
    {
        $metricsJson = json_encode($metrics, JSON_PRETTY_PRINT);

        return <<<PROMPT
        Analyze the following productivity metrics and provide insights:

        ```json
        {$metricsJson}
        ```

        Provide:
        1. Key observations about team productivity
        2. Potential bottlenecks or issues
        3. Specific recommendations for improvement
        4. Trends to watch

        Use clear, actionable language. Format in markdown.
        PROMPT;
    }

    /**
     * Build a prompt for priority suggestions.
     */
    public function buildPrioritySuggestionPrompt(array $tasks): string
    {
        $taskList = collect($tasks)->map(fn ($task) => "- {$task->title} (Due: {$task->due_date}, Priority: {$task->priority->value})"
        )->join("\n");

        return <<<PROMPT
        Analyze these tasks and suggest which ones should be prioritized:

        {$taskList}

        Consider:
        - Due dates
        - Current priorities
        - Dependencies (if mentioned)
        - Business impact

        Return a prioritized list with brief reasoning for each.
        PROMPT;
    }

    /**
     * Build a prompt for report generation.
     */
    public function buildReportPrompt(string $reportType, array $data): string
    {
        $dataJson = json_encode($data, JSON_PRETTY_PRINT);

        return <<<PROMPT
        Generate a {$reportType} report based on this data:

        ```json
        {$dataJson}
        ```

        The report should be:
        - Professional and well-structured
        - Include key metrics and insights
        - Highlight important trends
        - Provide actionable recommendations

        Format in markdown with appropriate headings and sections.
        PROMPT;
    }
}
