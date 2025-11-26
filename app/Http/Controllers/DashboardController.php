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
            $challenges = $user->challenges()->latest()->get();
            return view('dashboard.docente', compact('challenges'));
        }

        return view('dashboard.estudiante');
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
            'points' => 0,
        ]);

        return redirect()->route('challenge.show', $challenge)->with('success', 'Te has unido al desafío exitosamente.');
    }
}
