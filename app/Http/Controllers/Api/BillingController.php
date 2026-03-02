<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function __construct(protected StripeService $stripeService) {}

    public function plans(): JsonResponse
    {
        return response()->json([
            'plans' => StripeService::plans(),
        ]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'plan' => ['required', 'in:pro,enterprise'],
            'payment_method_id' => ['required', 'string'],
        ]);

        $team = $request->user()->currentTeam;
        $this->stripeService->subscribe($team, $data['plan'], $data['payment_method_id']);
        $team->update(['plan' => $data['plan']]);

        return response()->json([
            'message' => 'Subscription updated successfully.',
            'plan' => $team->fresh()->plan,
        ]);
    }

    public function cancel(Request $request): JsonResponse
    {
        $team = $request->user()->currentTeam;
        $this->stripeService->cancel($team);
        $team->update(['plan' => 'free']);

        return response()->json([
            'message' => 'Subscription will be cancelled at period end.',
        ]);
    }

    public function resume(Request $request): JsonResponse
    {
        $team = $request->user()->currentTeam;
        $this->stripeService->resume($team);

        return response()->json([
            'message' => 'Subscription resumed successfully.',
        ]);
    }

    public function invoices(Request $request): JsonResponse
    {
        $team = $request->user()->currentTeam;
        $invoices = method_exists($team, 'invoices') ? $team->invoices() : collect();

        return response()->json([
            'invoices' => collect($invoices)->map(fn ($invoice) => [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'total' => $invoice->total(),
                'date' => $invoice->date()->toDateString(),
                'pdf' => null,
            ]),
        ]);
    }

    public function webhook(Request $request): JsonResponse
    {
        // Keep endpoint available for Stripe; event handling can be expanded later.
        return response()->json([
            'received' => true,
            'type' => $request->input('type'),
        ]);
    }
}
