@extends('layouts.app')

@section('title', 'Desafíos Archivados - Dashboard')

@section('content')
<div class="dashboard-header">
    <h1>Desafíos Archivados</h1>
    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
        ← Volver al Dashboard
    </a>
</div>

@if($challenges->isEmpty())
    <div class="empty-state">
        <h2>No tienes desafíos archivados</h2>
        <p>Los desafíos finalizados aparecerán aquí.</p>
    </div>
@else
    <div class="challenges-list">
        @foreach($challenges as $challenge)
            <div class="challenge-list-item">
                <div class="challenge-info">
                    <h3>{{ $challenge->name }}</h3>
                    <span class="meta-info">Creado: {{ $challenge->created_at->format('d/m/Y') }}</span>
                    <span class="meta-info">Participantes: {{ $challenge->participants->count() }}</span>
                </div>
                <div class="challenge-actions">
                    <form action="{{ route('challenge.resume', $challenge) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Reactivar este desafío? Volverá a aparecer en el dashboard principal.')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success" title="Reactivar">
                            ↩️ Reactivar
                        </button>
                    </form>
                    <form action="{{ route('challenge.destroy', $challenge) }}" method="POST" style="display:inline; margin-left:4px;" onsubmit="return confirm('¿Estás seguro de eliminar este desafío permanentemente?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                            🗑️
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endif

@push('styles')
<style>
    .challenges-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .challenge-list-item {
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .challenge-info h3 {
        margin: 0 0 0.25rem 0;
        font-size: 1.1rem;
        color: #1e293b;
    }
    .meta-info {
        font-size: 0.85rem;
        color: #64748b;
        margin-right: 1rem;
    }
    .challenge-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
</style>
@endpush
@endsection
