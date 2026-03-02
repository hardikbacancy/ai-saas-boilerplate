<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    /**
     * Register new user + create default team
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Auto-create personal team
        $team = Team::create([
            'name'    => $data['name'] . "'s Team",
            'user_id' => $user->id,
            'plan'    => 'free',
        ]);

        $user->update(['current_team_id' => $team->id]);
        $user->teams()->attach($team);

        // Assign default role
        Role::findOrCreate('team-owner', 'web');
        $user->assignRole('team-owner');

        // Send email verification
        $user->sendEmailVerificationNotification();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful! Please verify your email.',
            'token'   => $token,
            'user'    => $user->load('currentTeam'),
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
            'two_factor_code' => ['nullable', 'string'],
        ]);

        $credentials = [
            'email' => $data['email'],
            'password' => $data['password'],
        ];

        if (! auth()->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials.'], 401);
        }

        $user  = auth()->user();

        if ($user->two_factor_confirmed_at) {
            $cacheKey = "2fa_login:{$user->id}";
            $cachedCode = Cache::get($cacheKey);
            $inputCode = $data['two_factor_code'] ?? null;

            if (! $inputCode || ! hash_equals((string) $cachedCode, (string) $inputCode)) {
                $code = (string) random_int(100000, 999999);
                Cache::put($cacheKey, $code, now()->addMinutes(10));

                Mail::raw("Your login verification code is: {$code}", function ($message) use ($user): void {
                    $message->to($user->email)->subject('Your 2FA Login Code');
                });

                return response()->json([
                    'message' => 'Two-factor code required.',
                    'requires_2fa' => true,
                ], 202);
            }

            Cache::forget($cacheKey);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $user->load('currentTeam'),
        ]);
    }

    /**
     * Logout (revoke current token)
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * Get authenticated user profile
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json(
            $request->user()->load(['currentTeam', 'roles'])
        );
    }

    public function sendVerificationNotification(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.',
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification email sent.',
        ]);
    }

    public function enableTwoFactor(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update([
            'two_factor_secret' => Hash::make((string) random_int(100000, 999999)),
            'two_factor_confirmed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Two-factor authentication enabled.',
        ]);
    }

    public function disableTwoFactor(Request $request): JsonResponse
    {
        $request->user()->update([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return response()->json([
            'message' => 'Two-factor authentication disabled.',
        ]);
    }
}