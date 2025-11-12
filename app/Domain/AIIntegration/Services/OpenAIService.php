<?php

declare(strict_types=1);

namespace App\Domain\AIIntegration\Services;

use OpenAI\Laravel\Facades\OpenAI;

class OpenAIService
{
    /**
     * Generate a completion using OpenAI.
     */
    public function complete(string $prompt, array $options = []): string
    {
        $defaultOptions = [
            'model' => config('openai.default_model', 'gpt-4-turbo-preview'),
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => $options['max_tokens'] ?? 2000,
            'temperature' => $options['temperature'] ?? 0.7,
        ];

        $response = OpenAI::chat()->create(array_merge($defaultOptions, $options));

        return $response->choices[0]->message->content ?? '';
    }

    /**
     * Generate a completion with a system message.
     */
    public function completeWithSystem(string $systemMessage, string $userMessage, array $options = []): string
    {
        $messages = [
            ['role' => 'system', 'content' => $systemMessage],
            ['role' => 'user', 'content' => $userMessage],
        ];

        $defaultOptions = [
            'model' => config('openai.default_model', 'gpt-4-turbo-preview'),
            'messages' => $messages,
            'max_tokens' => $options['max_tokens'] ?? 2000,
            'temperature' => $options['temperature'] ?? 0.7,
        ];

        $response = OpenAI::chat()->create(array_merge($defaultOptions, $options));

        return $response->choices[0]->message->content ?? '';
    }

    /**
     * Generate structured JSON output.
     */
    public function generateJson(string $prompt, array $options = []): array
    {
        $options['response_format'] = ['type' => 'json_object'];

        $response = $this->complete($prompt, $options);

        return json_decode($response, true) ?? [];
    }

    /**
     * Count tokens in a text (approximate).
     */
    public function estimateTokens(string $text): int
    {
        // Rough approximation: 1 token ≈ 4 characters
        return (int) ceil(strlen($text) / 4);
    }

    /**
     * Check if API is configured and available.
     */
    public function isAvailable(): bool
    {
        return !empty(config('openai.api_key'));
    }
}
