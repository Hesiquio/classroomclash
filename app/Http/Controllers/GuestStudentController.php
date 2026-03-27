<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class GuestStudentController extends Controller
{
    /**
     * Vista principal de gestión de estudiantes (solo docentes).
     */
    public function index()
    {
        $this->authorizeDocente();

        $students = \App\Models\User::where('role', 'estudiante')
            ->withCount('participations')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'is_guest', 'claim_code', 'created_at']);

        return view('students.index', compact('students'));
    }

    /**
     * Docente crea uno o varios estudiantes invitados rápidamente.
     * Solo necesita el nombre; el sistema genera email temporal y claim_code.
     */
    public function quickCreate(Request $request)
    {
        $this->authorizeDocente();

        $validated = $request->validate([
            'names' => ['required', 'string'],
        ]);

        // Soporte para múltiples nombres (uno por línea)
        $names  = array_filter(array_map('trim', explode("\n", $validated['names'])));
        $created = [];

        foreach ($names as $name) {
            if (strlen($name) < 2) continue;

            $claimCode = User::generateClaimCode();

            // Email ficticio único: no se usa para login
            $fakeEmail = 'guest_' . strtolower($claimCode) . '@classroomclash.local';

            $user = User::create([
                'name'       => $name,
                'email'      => $fakeEmail,
                'password'   => Hash::make($claimCode), // password = claim_code (no relevante)
                'role'       => 'estudiante',
                'is_guest'   => true,
                'claim_code' => $claimCode,
            ]);

            $created[] = ['name' => $user->name, 'code' => $claimCode];
        }

        if (empty($created)) {
            return back()->withErrors(['names' => 'Ingresa al menos un nombre válido.']);
        }

        return back()->with('guest_created', $created);
    }

    /**
     * Muestra el formulario para entrar con claim_code (vista pública).
     */
    public function claimForm()
    {
        // Si ya está autenticado, redirigir al dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.claim');
    }

    /**
     * Autentica al estudiante con su claim_code (actúa como login temporal).
     */
    public function claimLogin(Request $request)
    {
        $validated = $request->validate([
            'claim_code' => ['required', 'string', 'size:8'],
        ]);

        $user = User::where('claim_code', strtoupper($validated['claim_code']))->first();

        if (!$user || !$user->isGuest()) {
            return back()->withErrors(['claim_code' => 'Código inválido o ya fue reclamado con una cuenta definitiva.']);
        }

        Auth::login($user, remember: true);

        return redirect()->route('dashboard')->with('info', '¡Bienvenido/a, ' . $user->name . '! Puedes establecer tu contraseña en tu perfil.');
    }

    /**
     * El estudiante invitado establece email + contraseña reales.
     * Convierte la cuenta guest en cuenta permanente.
     */
    public function claimUpgrade(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->isGuest()) {
            return back()->with('error', 'Esta acción solo aplica para cuentas invitadas.');
        }

        $validated = $request->validate([
            'name'                  => ['required', 'string', 'min:2', 'max:80'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'is_guest'   => false,
            'claim_code' => null,   // Invalidar el código al reclamar
        ]);

        return back()->with('success', '¡Cuenta activada! Ya puedes iniciar sesión con tu correo y contraseña.');
    }

    /**
     * Docente genera contraseña temporal para cualquier estudiante.
     * Devuelve la contraseña generada en sesión para que el docente la comunique.
     */
    public function resetPassword(Request $request)
    {
        $this->authorizeDocente();

        $validated = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
        ]);

        $student = User::findOrFail($validated['student_id']);

        // Solo puede resetear estudiantes
        if (!$student->isEstudiante()) {
            return back()->withErrors(['student_id' => 'Solo puedes resetear contraseñas de estudiantes.']);
        }

        // Generar contraseña temporal legible: XXXX-XXXX
        $chars   = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $part1   = substr(str_shuffle($chars), 0, 4);
        $part2   = substr(str_shuffle($chars), 0, 4);
        $tempPass = $part1 . '-' . $part2;

        $student->update([
            'password' => Hash::make($tempPass),
        ]);

        return back()->with('password_reset', [
            'name'     => $student->name,
            'password' => $tempPass,
        ]);
    }

    private function authorizeDocente(): void
    {
        if (!Auth::user()->isDocente()) {
            abort(403);
        }
    }
}
