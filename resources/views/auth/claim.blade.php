@extends('layouts.app')

@section('title', 'Ingresar con Código - Classroom Clash')

@section('content')
<div class="auth-container">
    <div class="auth-card" style="max-width:420px; text-align:center;">

        <div style="font-size:3rem; margin-bottom:.5rem;">🎓</div>
        <h1 style="font-size:1.4rem; margin-bottom:.25rem;">Ingresar con Código</h1>
        <p class="text-muted" style="margin-bottom:1.5rem; font-size:.9rem;">
            Tu docente te proporcionó un código de 8 caracteres.<br>
            Ingrésalo aquí para acceder a Classroom Clash.
        </p>

        <form action="{{ route('guest.claim.login') }}" method="POST">
            @csrf

            <div class="form-group" style="margin-bottom:1rem;">
                <input
                    type="text"
                    name="claim_code"
                    id="claim_code_input"
                    class="form-control form-control-lg text-center @error('claim_code') is-invalid @enderror"
                    placeholder="Ej: AB3K7MNP"
                    maxlength="8"
                    autocomplete="off"
                    autofocus
                    style="letter-spacing:4px; font-weight:700; text-transform:uppercase; font-size:1.3rem;">
                @error('claim_code')
                    <div style="color:#ef4444; font-size:.85rem; margin-top:.4rem;">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="font-size:1rem; padding:.7rem;">
                Ingresar →
            </button>
        </form>

        <div style="margin-top:1.5rem; padding-top:1.5rem; border-top:1px solid #e2e8f0;">
            <p style="font-size:.85rem; color:#64748b; margin-bottom:.75rem;">
                ¿Tienes una cuenta propia?
            </p>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary" style="font-size:.875rem;">
                Iniciar Sesión Normal
            </a>
        </div>

    </div>
</div>

@push('scripts')
<script>
document.getElementById('claim_code_input').addEventListener('input', function () {
    this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
});
</script>
@endpush
@endsection
