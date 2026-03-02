<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function update(User $user, Team $team): bool
    {
        return $user->isOwnerOf($team) || $user->hasRole('admin');
    }

    public function invite(User $user, Team $team): bool
    {
        return $user->isOwnerOf($team) || $user->hasRole('admin');
    }

    public function removeMember(User $user, Team $team): bool
    {
        return $user->isOwnerOf($team);
    }
}
