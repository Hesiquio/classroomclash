<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'claim_code',
        'is_guest',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'         => 'hashed',
        'is_guest'         => 'boolean',
    ];

    public function isDocente(): bool
    {
        return $this->role === 'docente';
    }

    public function isEstudiante(): bool
    {
        return $this->role === 'estudiante';
    }

    public function isGuest(): bool
    {
        return (bool) $this->is_guest;
    }

    /** Genera un código de reclamo único de 10 caracteres alfanuméricos */
    public static function generateClaimCode(): string
    {
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8));
        } while (self::where('claim_code', $code)->exists());
        return $code;
    }

    public function challenges()
    {
        return $this->hasMany(Challenge::class, 'teacher_id');
    }

    public function participations()
    {
        return $this->hasMany(Participant::class);
    }
}
