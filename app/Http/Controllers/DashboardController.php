<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isDocente()) {
            $challenges = $user->challenges()->where('is_active', true)->latest()->get();
            $allStudents = \App\Models\User::where('role', 'estudiante')
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'is_guest']);
            return view('dashboard.docente', compact('challenges', 'allStudents'));
        }

        // Desafíos activos en los que participa
        $myParticipations = $user->participations()
            ->with('challenge')
            ->whereHas('challenge', fn($q) => $q->where('is_active', true))
            ->latest()
            ->get();

        // ── Estadísticas históricas (todos los desafíos) ──────────────────
        $allParticipations = $user->participations()
            ->with(['challenge.participants'])
            ->get();

        $totalChallenges  = $allParticipations->count();
        $totalPoints      = $allParticipations->sum('points');
        $bestPoints       = $allParticipations->max('points') ?? 0;
        $submitted        = $allParticipations->whereNotNull('finished_at')->count();

        $top3Count  = 0;
        $bestRank   = null;
        $rankPcts   = [];

        foreach ($allParticipations as $p) {
            $allInChallenge = $p->challenge->participants;
            $total = $allInChallenge->count();
            if ($total === 0) continue;

            // Rank = cuántos tienen MÁS puntos + 1
            $rank = $allInChallenge->where('points', '>', $p->points)->count() + 1;

            if ($rank <= 3)                              $top3Count++;
            if ($bestRank === null || $rank < $bestRank) $bestRank = $rank;

            // % de rendimiento: 100% = 1er lugar, 0% = último
            $rankPcts[] = round(($total - $rank + 1) / $total * 100);
        }

        $avgPerformance = count($rankPcts) > 0 ? round(array_sum($rankPcts) / count($rankPcts)) : 0;

        // Mensaje motivacional según rendimiento promedio
        $motivationalMsg = match(true) {
            $totalChallenges === 0       => ['🌱 ¡Empieza tu primer desafío!',              'info'],
            $avgPerformance >= 80        => ['🔥 ¡Eres uno de los mejores de la clase!',     'gold'],
            $avgPerformance >= 60        => ['⭐ ¡Excelente rendimiento! Sigue así.',         'green'],
            $avgPerformance >= 40        => ['💪 Vas por buen camino, sigue mejorando.',      'blue'],
            $top3Count > 0               => ['🏅 ¡Has alcanzado el top 3! No te rindas.',    'purple'],
            default                      => ['📚 Cada desafío es una oportunidad de crecer.', 'gray'],
        };

        $stats = [
            'total_challenges' => $totalChallenges,
            'total_points'     => $totalPoints,
            'best_points'      => $bestPoints,
            'submitted'        => $submitted,
            'top3_count'       => $top3Count,
            'best_rank'        => $bestRank,
            'avg_performance'  => $avgPerformance,
            'motivational'     => $motivationalMsg,
        ];

        return view('dashboard.estudiante', compact('myParticipations', 'stats'));
    }

    public function archived()
    {
        $user = Auth::user();

        if (!$user->isDocente()) {
            return redirect()->route('dashboard');
        }

        $challenges = $user->challenges()->where('is_active', false)->latest()->get();
        return view('dashboard.archived', compact('challenges'));
    }

    public function createChallenge(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'min_points' => ['required', 'integer', 'min:0'],
            'max_points' => ['required', 'integer', 'min:1'],
        ]);

        // Ensure min <= max
        if ($validated['min_points'] > $validated['max_points']) {
            return back()->withErrors(['min_points' => 'Los puntos mínimos deben ser menores o iguales que los máximos.']);
        }

        $challenge = Challenge::create([
            'name' => $validated['name'],
            'teacher_id' => Auth::id(),
            'min_points' => $validated['min_points'],
            'max_points' => $validated['max_points'],
        ]);

        return redirect()->route('challenge.show', $challenge)->with('success', 'Desafío creado exitosamente.');
    }

    public function joinChallenge(Request $request)
    {
        $validated = $request->validate([
            'join_code' => ['required', 'string', 'size:6', 'exists:challenges,join_code'],
        ]);

        $challenge = Challenge::where('join_code', $validated['join_code'])->firstOrFail();

        if (!$challenge->is_active) {
            return back()->withErrors(['join_code' => 'Este desafío ya ha finalizado.']);
        }

        $alreadyJoined = $challenge->participants()
            ->where('user_id', Auth::id())
            ->exists();

        if ($alreadyJoined) {
            return redirect()->route('challenge.show', $challenge)->with('info', 'Ya eres parte de este desafío.');
        }

        $challenge->participants()->create([
            'user_id' => Auth::id(),
            'points'  => 0,
        ]);

        return redirect()->route('challenge.show', $challenge)->with('success', 'Te has unido al desafío exitosamente.');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'min:2', 'max:80'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return back()->with('success', '¡Perfil actualizado correctamente!');
    }
}
