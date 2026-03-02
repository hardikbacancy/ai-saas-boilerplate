<?php

namespace App\Services;

use App\Models\Team;
use Laravel\Cashier\Subscription;

class StripeService
{
    /**
     * Available subscription plans
     */
    public static function plans(): array
    {
        return [
            'free' => [
                'name'        => 'Free',
                'price'       => 0,
                'ai_tokens'   => 10000,
                'team_members' => 2,
                'features'    => ['Basic AI Access', '2 Team Members', '10K Tokens/month'],
            ],
            'pro' => [
                'name'        => 'Pro',
                'price'       => 29,
                'stripe_id'   => config('services.stripe.pro_price_id'),
                'ai_tokens'   => 100000,
                'team_members' => 10,
                'features'    => ['Full AI Access', '10 Team Members', '100K Tokens/month', 'Priority Support'],
            ],
            'enterprise' => [
                'name'        => 'Enterprise',
                'price'       => 99,
                'stripe_id'   => config('services.stripe.enterprise_price_id'),
                'ai_tokens'   => -1, // unlimited
                'team_members' => -1, // unlimited
                'features'    => ['Unlimited AI', 'Unlimited Members', 'Dedicated Support', 'Custom Integrations'],
            ],
        ];
    }

    /**
     * Subscribe team to a plan
     */
    public function subscribe(Team $team, string $plan, string $paymentMethodId): Subscription
    {
        $plans = self::plans();

        if (! isset($plans[$plan]['stripe_id'])) {
            throw new \InvalidArgumentException("Plan '{$plan}' is not valid.");
        }

        // Add payment method to Stripe customer
        $team->addPaymentMethod($paymentMethodId);
        $team->updateDefaultPaymentMethod($paymentMethodId);

        return $team->newSubscription('default', $plans[$plan]['stripe_id'])->create($paymentMethodId);
    }

    /**
     * Cancel subscription at period end
     */
    public function cancel(Team $team): void
    {
        $team->subscription('default')->cancel();
    }

    /**
     * Resume a cancelled subscription
     */
    public function resume(Team $team): void
    {
        $team->subscription('default')->resume();
    }

    /**
     * Check if team can use AI (based on plan limits)
     */
    public function canUseAi(Team $team, int $requestedTokens = 100): bool
    {
        $planName = $team->plan ?? 'free';
        $plan     = self::plans()[$planName];

        // Unlimited
        if ($plan['ai_tokens'] === -1) {
            return true;
        }

        $usedTokens = app(OpenAiService::class)->getMonthlyUsage($team->id);

        return ($usedTokens + $requestedTokens) <= $plan['ai_tokens'];
    }
}