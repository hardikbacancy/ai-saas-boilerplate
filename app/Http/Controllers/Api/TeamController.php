<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use App\Notifications\TeamInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    /**
     * Get current team details
     */
    public function show(): JsonResponse
    {
        $team = auth()->user()->currentTeam->load('members');
        return response()->json($team);
    }

    /**
     * Update team settings
     */
    public function update(Request $request): JsonResponse
    {
        $team = auth()->user()->currentTeam;

        $this->authorize('update', $team);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $team->update($data);

        return response()->json(['message' => 'Team updated.', 'team' => $team]);
    }

    /**
     * Invite member to team via email
     */
    public function inviteMember(Request $request): JsonResponse
    {
        $team = auth()->user()->currentTeam;

        $this->authorize('invite', $team);

        $data = $request->validate([
            'email' => ['required', 'email'],
            'role'  => ['required', 'in:admin,member'],
        ]);

        // Check plan member limit
        $plan    = \App\Services\StripeService::plans()[$team->plan ?? 'free'];
        $current = $team->members()->count();

        if ($plan['team_members'] !== -1 && $current >= $plan['team_members']) {
            return response()->json([
                'error' => 'Team member limit reached. Please upgrade your plan.',
            ], 402);
        }

        $token = Str::random(40);

        // Store invitation
        $team->invitations()->create([
            'email' => $data['email'],
            'role'  => $data['role'],
            'token' => $token,
        ]);

        // Send invitation email
        \Notification::route('mail', $data['email'])
            ->notify(new TeamInvitation($team, $token));

        return response()->json(['message' => "Invitation sent to {$data['email']}."]);
    }

    /**
     * Accept team invitation
     */
    public function acceptInvitation(string $token): JsonResponse
    {
        $invitation = \App\Models\TeamInvitation::where('token', $token)
            ->firstOrFail();

        $user = auth()->user();
        if (! $user) {
            return response()->json(['error' => 'Please login before accepting invitation.'], 401);
        }

        // Add user to team
        $invitation->team->members()->syncWithoutDetaching([$user->id]);
        if (! $user->hasRole($invitation->role)) {
            $user->assignRole($invitation->role);
        }
        $user->update(['current_team_id' => $invitation->team->id]);

        $invitation->delete();

        return response()->json(['message' => 'You have joined the team!']);
    }

    /**
     * Remove member from team
     */
    public function removeMember(Request $request, int $userId): JsonResponse
    {
        $team = auth()->user()->currentTeam;

        $this->authorize('removeMember', $team);

        $team->members()->detach($userId);

        return response()->json(['message' => 'Member removed from team.']);
    }
}