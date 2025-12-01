<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'challenge_id',
        'points',
        'finished_at',
        'duration_seconds',
        'participated',
        'validated_at',
    ];

    protected $casts = [
        'points' => 'integer',
        'finished_at' => 'datetime',
        'participated' => 'boolean',
        'validated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    public function addPoint(): void
    {
        $this->increment('points');
    }

    /**
     * Relación con equipos (un participante puede estar en un equipo)
     */
    public function team()
    {
        return $this->belongsToMany(Team::class, 'team_members');
    }
}
