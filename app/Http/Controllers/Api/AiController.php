<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AiGenerateRequest;
use App\Jobs\ProcessAiRequest;
use App\Services\OpenAiService;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;

class AiController extends Controller
{
    public function __construct(
        protected OpenAiService $aiService,
        protected StripeService $stripeService,
    ) {}

    /**
     * Generate AI text (sync — for small requests)
     */
    public function generate(AiGenerateRequest $request): JsonResponse
    {
        $team = $request->user()->currentTeam;

        // Check plan limits
        if (! $this->stripeService->canUseAi($team)) {
            return response()->json([
                'error'   => 'AI token limit reached. Please upgrade your plan.',
                'upgrade' => url('/billing'),
            ], 402);
        }

        $result = $this->aiService->generate(
            prompt:    $request->input('prompt'),
            maxTokens: $request->input('max_tokens', 500),
            teamId:    $team->id,
        );

        if (! $result['success']) {
            return response()->json(['error' => $result['error']], 500);
        }

        return response()->json([
            'text'         => $result['text'],
            'tokens_used'  => $result['tokens_used'],
            'monthly_usage' => $this->aiService->getMonthlyUsage($team->id),
        ]);
    }

    /**
     * Generate AI text (async — for large requests via queue)
     */
    public function generateAsync(AiGenerateRequest $request): JsonResponse
    {
        $team = $request->user()->currentTeam;

        if (! $this->stripeService->canUseAi($team)) {
            return response()->json(['error' => 'Token limit reached.'], 402);
        }

        $jobId = uniqid('ai_', true);

        // Dispatch to queue
        ProcessAiRequest::dispatch(
            teamId:  $team->id,
            userId:  $request->user()->id,
            prompt:  $request->input('prompt'),
            jobId:   $jobId,
        );

        return response()->json([
            'message' => 'Request queued successfully.',
            'job_id'  => $jobId,
            'status'  => 'processing',
        ], 202);
    }

    /**
     * Get AI usage stats for current team
     */
    public function usage(): JsonResponse
    {
        $team  = auth()->user()->currentTeam;
        $plans = StripeService::plans();
        $plan  = $plans[$team->plan ?? 'free'];

        return response()->json([
            'plan'         => $team->plan ?? 'free',
            'tokens_used'  => $this->aiService->getMonthlyUsage($team->id),
            'tokens_limit' => $plan['ai_tokens'],
            'reset_date'   => now()->endOfMonth()->toDateString(),
        ]);
    }
}