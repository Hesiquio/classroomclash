@extends('layouts.app')

@section('title', 'Dashboard Docente - Classroom Clash')

@section('content')
<div class="dashboard-header">
    <h1>Mis Desafíos</h1>
    <button type="button" class="btn btn-primary" onclick="toggleModal('createChallengeModal')">
        Crear Nuevo Desafío
    </button>
</div>

@if($challenges->isEmpty())
    <div class="empty-state">
        <h2>No tienes desafíos creados</h2>
        <p>Crea tu primer desafío para comenzar a gestionar la participación de tus estudiantes.</p>
    </div>
@else
    <div class="challenges-grid">
        @foreach($challenges as $challenge)
            <div class="challenge-card">
                <div class="challenge-header">
                    <h3>{{ $challenge->name }}</h3>
                    @if($challenge->is_active)
                        <span class="badge badge-success">Activo</span>
                    @else
                        <span class="badge badge-secondary">Finalizado</span>
                    @endif
                </div>
                <div class="challenge-body">
                    <p><strong>Código de acceso:</strong> <code>{{ $challenge->join_code }}</code></p>
                    <p><strong>Participantes:</strong> {{ $challenge->participants->count() }}</p>
                    <p><strong>Creado:</strong> {{ $challenge->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="challenge-footer">
                    <a href="{{ route('challenge.show', $challenge) }}" class="btn btn-sm btn-primary">Ver Pizarra</a>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="openEditModal({{ $challenge->id }}, '{{ $challenge->name }}', {{ $challenge->min_points ?? 0 }}, {{ $challenge->max_points ?? 100 }})">
                        Editar
                    </button>
                    <form action="{{ route('challenge.duplicate', $challenge) }}" method="POST" style="display:inline; margin-left:4px;" onsubmit="return confirm('¿Crear una copia de este desafío?')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Duplicar">
                            Copia
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endif

<div id="createChallengeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Crear Nuevo Desafío</h2>
            <button type="button" class="close" onclick="toggleModal('createChallengeModal')">&times;</button>
        </div>
        <form action="{{ route('challenge.create') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="challenge_name">Nombre del desafío</label>
                    <input type="text" id="challenge_name" name="name" class="form-control" placeholder="Ej: Clase de Lunes" required>
                </div>
                <div class="form-group mt-3">
                    <label for="min_points">Puntos Mínimos</label>
                    <input type="number" id="min_points" name="min_points" class="form-control" min="0" value="0" required>
                </div>
                <div class="form-group mt-3">
                    <label for="max_points">Puntos Máximos</label>
                    <input type="number" id="max_points" name="max_points" class="form-control" min="1" value="100" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="toggleModal('createChallengeModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear Desafío</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Challenge Modal -->
<div id="editChallengeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Editar Desafío</h2>
            <button type="button" class="close" onclick="toggleModal('editChallengeModal')">&times;</button>
        </div>
        <form id="editChallengeForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_challenge_name">Nombre del desafío</label>
                    <input type="text" id="edit_challenge_name" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_min_points">Puntos Mínimos</label>
                    <input type="number" id="edit_min_points" name="min_points" class="form-control" min="0" value="0" required>
                    <small class="form-text">Puntos para el último lugar</small>
                </div>
                
                <div class="form-group">
                    <label for="edit_max_points">Puntos Máximos</label>
                    <input type="number" id="edit_max_points" name="max_points" class="form-control" min="1" value="100" required>
                    <small class="form-text">Puntos para el primer lugar</small>
                </div>
                
                <div class="alert alert-info">
                    <strong>💡 Sugerencia automática:</strong> Los puntos se calcularán automáticamente según la posición del estudiante entre el mínimo y máximo configurado.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="toggleModal('editChallengeModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.toggle('show');
}

function openEditModal(challengeId, name, minPoints, maxPoints) {
    document.getElementById('edit_challenge_name').value = name;
    document.getElementById('edit_min_points').value = minPoints;
    document.getElementById('edit_max_points').value = maxPoints;
    document.getElementById('editChallengeForm').action = `/challenge/${challengeId}/update`;
    toggleModal('editChallengeModal');
}

window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.classList.remove('show');
        }
    });
}
</script>
@endpush
@endsection
