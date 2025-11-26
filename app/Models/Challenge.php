<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Challenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'teacher_id',
        'is_active',
        'join_code',
        'max_points',
        'min_points',
        'started_at',
        'paused_at',
        'accumulated_time',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'started_at' => 'datetime',
        'paused_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($challenge) {
            if (empty($challenge->join_code)) {
                $challenge->join_code = self::generateUniqueCode();
            }
        });
    }

    public static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (self::where('join_code', $code)->exists());
        return $code;    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'participants')
            ->withPivot('points')
            ->withTimestamps();
    }

    public function getCurrentTimeSeconds(): int
    {
        if (!$this->started_at) {
            return $this->accumulated_time;
        }

        if ($this->paused_at) {
            return $this->accumulated_time;
        }

        return $this->accumulated_time + $this->started_at->diffInSeconds(now());
    }

    /**
     * Calculate suggested points for a participant based on their ranking position
     * 
     * @param int $position Position in ranking (1-based, 1 = first place)
     * @param int $totalParticipants Total number of participants
     * @return int Suggested points
     */
    public function getSuggestedPoints(int $position, int $totalParticipants): int
    {
        if ($totalParticipants <= 1) {
            return $this->max_points;
        }

        // Linear distribution from max_points (1st place) to min_points (last place)
        $pointsRange = $this->max_points - $this->min_points;
        $positionFactor = ($totalParticipants - $position) / ($totalParticipants - 1);
        
        return (int) round($this->min_points + ($pointsRange * $positionFactor));
    }

    /**
     * Relación con equipos
     */
    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    /**
     * Verificar si el desafío tiene equipos formados
     */
    public function hasTeams(): bool
    {
        return $this->teams()->exists();
    }
}
