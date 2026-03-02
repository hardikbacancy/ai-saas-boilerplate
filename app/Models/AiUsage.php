<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'tokens_used',
        'model',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
