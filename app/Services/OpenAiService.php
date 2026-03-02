<?php

namespace App\Services;

use App\Models\AiUsage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiService
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->model  = config('services.openai.model', 'gpt-4');
    }

    /**
     * Generate AI text response
     */
    public function generate(string $prompt, int $maxTokens = 1000, ?int $teamId = null): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model'      => $this->model,
                    'messages'   => [
                        ['role' => 'system', 'content' => 'You are a helpful AI assistant.'],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                    'max_tokens' => $maxTokens,
                    'temperature' => 0.7,
                ]);

            if ($response->failed()) {
                Log::error('OpenAI API Error', ['response' => $response->json()]);
                throw new \Exception('OpenAI API request failed.');
            }

            $data         = $response->json();
            $tokensUsed   = $data['usage']['total_tokens'];
            $generatedText = $data['choices'][0]['message']['content'];

            // Track token usage per team
            if ($teamId) {
                $this->trackUsage($teamId, $tokensUsed);
            }

            return [
                'success'    => true,
                'text'       => $generatedText,
                'tokens_used' => $tokensUsed,
            ];

        } catch (\Exception $e) {
            Log::error('OpenAI Service Error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Track AI token usage per team
     */
    protected function trackUsage(int $teamId, int $tokens): void
    {
        AiUsage::create([
            'team_id'     => $teamId,
            'tokens_used' => $tokens,
            'model'       => $this->model,
        ]);
    }

    /**
     * Get total tokens used by team this month
     */
    public function getMonthlyUsage(int $teamId): int
    {
        return AiUsage::where('team_id', $teamId)
            ->whereMonth('created_at', now()->month)
            ->sum('tokens_used');
    }
}