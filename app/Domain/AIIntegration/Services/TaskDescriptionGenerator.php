<?php

declare(strict_types=1);

namespace App\Domain\AIIntegration\Services;

use App\Infrastructure\Persistence\Eloquent\Models\Task;

class TaskDescriptionGenerator
{
    public function __construct(
        private readonly OpenAIService $openAIService,
        private readonly PromptBuilder $promptBuilder
    ) {
    }

    /**
     * Generate a detailed technical description for a task.
     */
    public function generate(Task $task): string
    {
        if (!$this->openAIService->isAvailable()) {
            return $this->generateFallbackDescription($task);
        }

        $prompt = $this->promptBuilder->buildTaskDescriptionPrompt($task);

        return $this->openAIService->complete($prompt, [
            'temperature' => 0.7,
            'max_tokens' => 1000,
        ]);
    }

    /**
     * Generate acceptance criteria for a task.
     */
    public function generateAcceptanceCriteria(Task $task): string
    {
        if (!$this->openAIService->isAvailable()) {
            return $this->generateFallbackCriteria($task);
        }

        $prompt = $this->promptBuilder->buildAcceptanceCriteriaPrompt($task);

        return $this->openAIService->complete($prompt, [
            'temperature' => 0.6,
            'max_tokens' => 800,
        ]);
    }

    /**
     * Suggest subtasks for a complex task.
     */
    public function suggestSubtasks(Task $task): array
    {
        if (!$this->openAIService->isAvailable()) {
            return [];
        }

        $prompt = $this->promptBuilder->buildSubtaskPrompt($task);

        $response = $this->openAIService->generateJson(
            "Return a JSON array with key 'subtasks' containing suggested subtasks. {$prompt}",
            ['temperature' => 0.7]
        );

        return $response['subtasks'] ?? [];
    }

    /**
     * Improve task title to be more descriptive.
     */
    public function improveTitle(Task $task): string
    {
        if (!$this->openAIService->isAvailable()) {
            return $task->title;
        }

        $systemMessage = 'You are a technical project manager. Improve task titles to be clear, actionable, and follow best practices.';
        $userMessage = "Improve this task title: \"{$task->title}\"\n\nContext: {$task->description}\n\nReturn only the improved title, nothing else.";

        return $this->openAIService->completeWithSystem($systemMessage, $userMessage, [
            'temperature' => 0.5,
            'max_tokens' => 100,
        ]);
    }

    /**
     * Generate a fallback description when AI is unavailable.
     */
    private function generateFallbackDescription(Task $task): string
    {
        return "Task: {$task->title}\n\n"
            ."This task requires implementation and testing. "
            .'Please refer to the project requirements and ensure all acceptance criteria are met.';
    }

    /**
     * Generate fallback acceptance criteria.
     */
    private function generateFallbackCriteria(Task $task): string
    {
        return "- Task is completed as described\n"
            ."- Code is tested and reviewed\n"
            ."- Documentation is updated\n"
            .'- No regressions introduced';
    }
}
