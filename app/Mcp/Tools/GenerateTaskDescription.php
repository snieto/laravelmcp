<?php

namespace App\Mcp\Tools;

use App\Domain\AIIntegration\Services\TaskDescriptionGenerator;
use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class GenerateTaskDescription extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Generate a detailed AI-powered description for a task.
        Requires OpenAI API key to be configured.
        Returns a comprehensive technical description with approach, challenges, and expected outcome.
    MARKDOWN;

    public function __construct(
        private readonly TaskDescriptionGenerator $descriptionGenerator,
        private readonly TaskRepositoryInterface $taskRepository
    ) {
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $taskId = $request->input('task_id');
        $task = $this->taskRepository->findById($taskId);

        if (!$task) {
            return Response::json([
                'success' => false,
                'error' => "Task #{$taskId} not found",
            ]);
        }

        try {
            $generatedDescription = $this->descriptionGenerator->generate($task);

            return Response::json([
                'success' => true,
                'task_id' => $task->id,
                'task_title' => $task->title,
                'original_description' => $task->description,
                'generated_description' => $generatedDescription,
                'message' => 'AI-generated description created successfully',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => 'Failed to generate description: '.$e->getMessage(),
                'hint' => 'Make sure OPENAI_API_KEY is configured in .env',
            ]);
        }
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'task_id' => $schema->integer()
                ->description('The ID of the task to generate description for')
                ->minimum(1)
                ->required(),
        ];
    }
}
