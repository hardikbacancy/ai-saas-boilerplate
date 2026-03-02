<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Billable;

class Team extends Model
{
    use HasFactory, Billable;

    protected $fillable = [
        'name',
        'user_id',
        'plan',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function invitations()
    {
        return $this->hasMany(TeamInvitation::class);
    }

    public function aiUsages()
    {
        return $this->hasMany(AiUsage::class);
    }

    // ─── Accessors ────────────────────────────────────────────────

    public function getPlanNameAttribute(): string
    {
        return ucfirst($this->plan ?? 'free');
    }

    public function getIsOnProAttribute(): bool
    {
        return in_array($this->plan, ['pro', 'enterprise']);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function monthlyTokenUsage(): int
    {
        return $this->aiUsages()
            ->whereMonth('created_at', now()->month)
            ->sum('tokens_used');
    }
}