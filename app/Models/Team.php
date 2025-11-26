<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'challenge_id',
        'name',
        'color',
    ];

    /**
     * Relación con el desafío
     */
    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    /**
     * Relación con los participantes (miembros del equipo)
     */
    public function members()
    {
        return $this->belongsToMany(Participant::class, 'team_members');
    }

    /**
     * Obtener el puntaje total del equipo (suma de todos los miembros)
     */
    public function getTotalPointsAttribute()
    {
        return $this->members->sum('points');
    }

    /**
     * Obtener el tiempo promedio del equipo
     */
    public function getAverageTimeAttribute()
    {
        $finishedMembers = $this->members->whereNotNull('finished_at');
        
        if ($finishedMembers->isEmpty()) {
            return null;
        }

        return round($finishedMembers->avg('duration_seconds'));
    }

    /**
     * Verificar si todos los miembros han terminado
     */
    public function allMembersFinished()
    {
        return $this->members->every(fn($member) => $member->finished_at !== null);
    }
}
