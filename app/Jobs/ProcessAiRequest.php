<?php

namespace App\Jobs;

use App\Services\OpenAiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ProcessAiRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $teamId,
        public int $userId,
        public string $prompt,
        public string $jobId,
    ) {}

    public function handle(OpenAiService $openAiService): void
    {
        $result = $openAiService->generate(
            prompt: $this->prompt,
            maxTokens: 1000,
            teamId: $this->teamId,
        );

        Cache::put("ai_job:{$this->jobId}", $result, now()->addHour());
    }
}
