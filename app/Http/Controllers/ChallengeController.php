<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ChallengeController extends Controller
{
    public function show(Challenge $challenge)
    {
        $user = Auth::user();

        if ($user->isDocente() && $challenge->teacher_id !== $user->id) {
            abort(403, 'No tienes permiso para acceder a este desafío.');
        }

        if ($user->isEstudiante()) {
            $participant = $challenge->participants()->where('user_id', $user->id)->first();
            if (!$participant) {
                abort(403, 'No eres parte de este desafío.');
            }
        }

        $participants = $challenge->participants()
            ->with(['user', 'team'])
            ->orderByDesc('points')
            ->get();

        $teams = $challenge->teams()->with('members.user')->get();

        // Get students not in the challenge for manual addition
        $existingUserIds = $participants->pluck('user_id');
        $availableStudents = \App\Models\User::where('role', 'estudiante')
            ->whereNotIn('id', $existingUserIds)
            ->orderBy('name')
            ->get();

        return view('challenge.show', compact('challenge', 'participants', 'teams', 'availableStudents'));
    }

    public function getData(Challenge $challenge)
    {
        $user = Auth::user();
        
        if ($user->isDocente() && $challenge->teacher_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if ($user->isEstudiante()) {
            $isParticipant = $challenge->participants()->where('user_id', $user->id)->exists();
            if (!$isParticipant) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        $participants = $challenge->participants()
            ->with('user')
            ->orderByDesc('points')
            ->get()
            ->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'user_id' => $participant->user_id,
                    'name' => $participant->user->name,
                    'points' => $participant->points,
                    'finished_at' => $participant->finished_at,
                    'duration_seconds' => $participant->duration_seconds,
                    'formatted_time' => $participant->finished_at ? gmdate("H:i:s", $participant->duration_seconds) : null,
                    'participated' => $participant->participated,
                ];
            });

        return response()->json([
            'participants' => $participants,
            'challenge' => [
                'is_active' => $challenge->is_active,
                'started_at' => $challenge->started_at,
                'paused_at' => $challenge->paused_at,
                'current_time_seconds' => $challenge->getCurrentTimeSeconds(),
                'is_running' => $challenge->is_active && $challenge->started_at && !$challenge->paused_at,
            ]
        ]);
    }

    public function startTimer(Challenge $challenge)
    {
        $this->authorizeTeacher($challenge);

        if (!$challenge->started_at) {
            $challenge->update(['started_at' => now()]);
        } elseif ($challenge->paused_at) {
            // Resuming
            $challenge->update([
                'started_at' => now(),
                'paused_at' => null,
            ]);
        }

        return back()->with('success', 'Reloj iniciado.');
    }

    public function pauseTimer(Challenge $challenge)
    {
        $this->authorizeTeacher($challenge);

        if ($challenge->started_at && !$challenge->paused_at) {
            $elapsed = $challenge->started_at->diffInSeconds(now());
            $challenge->update([
                'paused_at' => now(),
                'accumulated_time' => $challenge->accumulated_time + $elapsed,
            ]);
        }

        return back()->with('success', 'Reloj pausado.');
    }

    public function submit(Challenge $challenge)
    {
        $user = Auth::user();
        $participant = $challenge->participants()->where('user_id', $user->id)->firstOrFail();

        if ($participant->finished_at) {
            return back()->with('error', 'Ya has enviado tu desafío.');
        }

        // Calculate duration
        $duration = 0;
        if ($challenge->started_at) {
            if ($challenge->paused_at) {
                 $duration = $challenge->accumulated_time;
            } else {
                 $duration = $challenge->accumulated_time + $challenge->started_at->diffInSeconds(now());
            }
        }

        $participant->update([
            'finished_at' => now(),
            'duration_seconds' => $duration,
        ]);

        return back()->with('success', 'Desafío enviado correctamente. Espera a que el profesor valide tu tiempo.');
    }

    public function updateScore(Challenge $challenge, Participant $participant, Request $request)
    {
        $this->authorizeTeacher($challenge);

        $validated = $request->validate([
            'points' => 'required|integer|min:0',
        ]);

        $participant->update([
            'points' => $validated['points'],
            'participated' => true,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Puntaje actualizado.');
    }

    public function validateSubmission(Challenge $challenge, Participant $participant, Request $request)
    {
        $this->authorizeTeacher($challenge);

        $validated = $request->validate([
            'penalty_seconds' => 'required|integer|min:0',
        ]);

        $penalty = $validated['penalty_seconds'];

        // Add penalty to duration
        $participant->update([
            'duration_seconds' => $participant->duration_seconds + $penalty,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('challenge.show', $challenge)->with('success', 'Validación aplicada.');
    }

    public function addPoint(Challenge $challenge, $participantId)
    {
        $this->authorizeTeacher($challenge);

        if (!$challenge->is_active) {
            return back()->withErrors(['challenge' => 'Este desafío ya ha finalizado.']);
        }

        $participant = $challenge->participants()->findOrFail($participantId);
        $participant->addPoint();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Punto agregado correctamente.');
    }

    public function finalize(Challenge $challenge)
    {
        $this->authorizeTeacher($challenge);

        // Pause timer if running to freeze time
        if ($challenge->started_at && !$challenge->paused_at) {
            $elapsed = $challenge->started_at->diffInSeconds(now());
            $challenge->update([
                'paused_at' => now(),
                'accumulated_time' => $challenge->accumulated_time + $elapsed,
            ]);
        }

        $challenge->update(['is_active' => false]);

        return back()->with('success', 'Desafío finalizado correctamente.');
    }

    public function update(Request $request, Challenge $challenge)
    {
        $this->authorizeTeacher($challenge);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'min_points' => 'required|integer|min:0',
            'max_points' => 'required|integer|min:1',
        ]);

        if ($validated['min_points'] >= $validated['max_points']) {
            return back()->withErrors(['min_points' => 'Los puntos mínimos deben ser menores que los máximos.']);
        }

        $challenge->update($validated);

        return back()->with('success', 'Desafío actualizado correctamente.');
    }

    public function roulette(Challenge $challenge, Request $request)
    {
        $this->authorizeTeacher($challenge);

        $validated = $request->validate([
            'participant_id' => 'required|exists:participants,id',
            'points' => 'required|integer|min:1',
        ]);

        $participant = Participant::findOrFail($validated['participant_id']);
        
        // Verify participant belongs to this challenge
        if ($participant->challenge_id !== $challenge->id) {
            return response()->json(['error' => 'Invalid participant'], 400);
        }

        // Add points and mark as participated
        $participant->increment('points', $validated['points']);
        $participant->update(['participated' => true]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'participant' => [
                    'id' => $participant->id,
                    'name' => $participant->user->name,
                    'points' => $participant->points,
                ]
            ]);
        }

        return back()->with('success', 'Puntos asignados correctamente.');
    }

    public function createTeams(Challenge $challenge, Request $request)
    {
        $this->authorizeTeacher($challenge);

        $validated = $request->validate([
            'team_size' => 'required|integer|min:2|max:10',
        ]);

        $teamSize = $validated['team_size'];
        
        // Get all participants
        $participants = $challenge->participants()->get();
        
        if ($participants->count() < 2) {
            return back()->with('error', 'Se necesitan al menos 2 participantes para formar equipos.');
        }

        // Shuffle participants
        $shuffledParticipants = $participants->shuffle();
        
        // Calculate number of teams
        $totalParticipants = $shuffledParticipants->count();
        $numberOfTeams = ceil($totalParticipants / $teamSize);
        
        // Create teams
        $teams = [];
        $colors = ['#ef4444', '#f97316', '#f59e0b', '#84cc16', '#10b981', '#06b6d4', '#3b82f6', '#6366f1', '#8b5cf6', '#d946ef', '#f43f5e'];
        
        for ($i = 0; $i < $numberOfTeams; $i++) {
            $teams[] = $challenge->teams()->create([
                'name' => 'Equipo ' . ($i + 1),
                'color' => $colors[$i % count($colors)],
            ]);
        }

        // Assign participants to teams
        $shuffledParticipants->each(function ($participant, $index) use ($teams, $numberOfTeams) {
            $teamIndex = $index % $numberOfTeams;
            $teams[$teamIndex]->members()->attach($participant->id);
        });

        return back()->with('success', 'Equipos formados aleatoriamente.');
    }

    public function addStudent(Challenge $challenge, Request $request)
    {
        $this->authorizeTeacher($challenge);

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);

        // Check if already exists
        if ($challenge->participants()->where('user_id', $validated['student_id'])->exists()) {
            return back()->with('error', 'El estudiante ya está en el desafío.');
        }

        $challenge->participants()->create([
            'user_id' => $validated['student_id'],
            'points' => 0,
        ]);

        return back()->with('success', 'Estudiante añadido correctamente.');
    }

    public function deleteTeams(Challenge $challenge)
    {
        $this->authorizeTeacher($challenge);

        // Delete all teams (cascade will handle members)
        $challenge->teams()->delete();

        return back()->with('success', 'Equipos disueltos. Se ha vuelto al modo individual.');
    }

    public function duplicate(Challenge $challenge)
    {
        $this->authorizeTeacher($challenge);

        // Replicate challenge attributes (except id, timestamps)
        $newChallenge = $challenge->replicate();
        $newChallenge->name = $challenge->name . ' (copia)';
        $newChallenge->teacher_id = Auth::id();
        $newChallenge->is_active = false;
        // Reset join_code so a new one is generated
        $newChallenge->join_code = null;
        // join_code will be generated automatically by model boot
        $newChallenge->save();

        return back()->with('success', 'Desafío duplicado correctamente.');
    }

    private function authorizeTeacher(Challenge $challenge)
    {
        $user = Auth::user();
        if (!$user->isDocente() || $challenge->teacher_id !== $user->id) {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }
    }

}
