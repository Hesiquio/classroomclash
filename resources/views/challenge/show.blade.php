@extends('layouts.app')

@section('title', $challenge->name . ' - Classroom Clash')

@section('content')
<div class="challenge-show-header">
    <div class="header-left">
        <div class="challenge-info-row">
            <h1 class="challenge-title">{{ $challenge->name }}</h1>
            <div class="challenge-meta">
                <span class="code-badge">
                    <span class="code-label">Código:</span>
                    <span class="code-value" id="challengeCode">{{ $challenge->join_code }}</span>
                    <button type="button" class="code-copy-btn whatsapp-btn" onclick="shareOnWhatsApp()" title="Compartir en WhatsApp">
                        <svg class="whatsapp-icon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </button>
                </span>
                @if($challenge->is_active)
                    <span class="status-badge active">
                        <span class="status-dot">●</span>
                        <span class="status-text">Activo</span>
                    </span>
                @else
                    <span class="status-badge inactive">
                        <span class="status-dot">○</span>
                        <span class="status-text">Inactivo</span>
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="challenge-controls">
        @if(Auth::user()->isDocente())
            <div class="timer-actions">
                <div class="timer-display" id="timer">00:00:00</div>
                
                @if($challenge->is_active)
                    @if(!$challenge->started_at || $challenge->paused_at)
                        <form action="{{ route('challenge.start', $challenge) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn-icon-action btn-icon-success" data-tooltip="{{ $challenge->started_at ? 'Reanudar' : 'Iniciar' }}">
                                ▶️
                            </button>
                        </form>
                    @else
                        <form action="{{ route('challenge.pause', $challenge) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn-icon-action btn-icon-warning" data-tooltip="Pausar">
                                ⏸️
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('challenge.finalize', $challenge) }}" method="POST" onsubmit="return confirm('¿Estás seguro de finalizar este desafío?')" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-icon-action btn-icon-danger" data-tooltip="Finalizar">
                            ⏹️
                        </button>
                    </form>
                @else
                    <form action="{{ route('challenge.resume', $challenge) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-icon-action btn-icon-primary" data-tooltip="Reactivar">
                            🔄
                        </button>
                    </form>
                @endif
                
                @if(Auth::user()->isDocente())
                    <button type="button" class="btn-icon-action btn-icon-primary" onclick="openAddStudentModal()" data-tooltip="Añadir Estudiante">
                        ➕
                    </button>
                @endif

                @if($challenge->hasTeams())
                    <form action="{{ route('challenge.teams.delete', $challenge) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Deshacer los equipos y volver al modo individual?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-icon-action btn-icon-danger" data-tooltip="Deshacer Equipos">
                            👥🚫
                        </button>
                    </form>
                @else
                    <button type="button" class="btn-icon-action btn-icon-info" onclick="openTeamsModal()" data-tooltip="Formar Equipos">
                        👥
                    </button>
                @endif
                
                <button type="button" class="btn-icon-action btn-icon-info" onclick="openRouletteModal()" data-tooltip="Ruleta">
                    🎰
                </button>
            </div>
        @else
            <div class="timer-display" id="timer">00:00:00</div>
        @endif

        @if(Auth::user()->isEstudiante() && $challenge->is_active)
            @php
                $myParticipant = $participants->where('user_id', Auth::id())->first();
            @endphp
            @if($myParticipant && !$myParticipant->finished_at)
                <form action="{{ route('challenge.submit', $challenge) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary">¡Terminé!</button>
                </form>
            @elseif($myParticipant && $myParticipant->finished_at)
                <div class="alert-compact">
                    ✓ {{ gmdate("H:i:s", $myParticipant->duration_seconds) }}
                </div>
            @endif
        @endif
    </div>
</div>

<div class="participants-grid">
    @if($challenge->hasTeams())
        @foreach($teams as $team)
            <div class="participant-card team-card" style="border-top: 4px solid {{ $team->color }};">
                <div class="card-header">
                    <div class="team-name" style="color: {{ $team->color }}; font-weight: bold;">{{ $team->name }}</div>
                </div>
                
                <div class="team-members-list">
                    @foreach($team->members as $member)
                        <div class="team-member-item">
                            <span class="member-name">{{ $member->user->name }}</span>
                            @if($member->finished_at)
                                <span class="member-status">✓</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="points-display">
                    <span class="points-value">{{ $team->total_points }}</span>
                    <span class="points-label">pts equipo</span>
                </div>
                
                @if($team->average_time)
                    <div class="submission-time">⏱ Prom: {{ gmdate("H:i:s", $team->average_time) }}</div>
                @endif
            </div>
        @endforeach
    @else
        @foreach($participants as $index => $participant)
            <div id="participant-card-{{ $participant->id }}" class="participant-card {{ $index < 3 ? 'top-rank rank-' . ($index + 1) : '' }} {{ $participant->user_id === Auth::id() ? 'current-user' : '' }}">
                <div class="card-header">
                    <div class="position-badge">
                        @if($index === 0)
                            <span class="medal">🥇</span>
                        @elseif($index === 1)
                            <span class="medal">🥈</span>
                        @elseif($index === 2)
                            <span class="medal">🥉</span>
                        @else
                            <span class="rank-number">#{{ $index + 1 }}</span>
                        @endif
                    </div>
                </div>

                <div class="student-info">
                    <div class="student-name">{{ $participant->user->name }}</div>
                    @if($participant->finished_at)
                        <div class="submission-time">⏱ {{ gmdate("H:i:s", $participant->duration_seconds) }}</div>
                    @endif
                </div>

                <div class="points-display">
                    <span class="points-value">{{ $participant->points }}</span>
                    <span class="points-label">pts</span>
                </div>

                @if(Auth::user()->isDocente() && $challenge->is_active)
                    <div class="card-actions">
                        <button type="button" class="btn-icon settings" onclick="openScoreModal({{ $participant->id }}, '{{ $participant->user->name }}', {{ $participant->points }})" title="Ajustar Puntos">
                            ⚙️
                        </button>
                        @if($participant->finished_at)
                            <button type="button" class="btn-icon validate" onclick="openValidateModal({{ $participant->id }}, '{{ $participant->user->name }}')" title="Validar Desafío">
                                ✅
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
        
        @if($participants->isEmpty())
            <div class="empty-state-container" style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #64748b;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">👻</div>
                <h3>No hay estudiantes activos</h3>
                <p>Comparte el código <strong>{{ $challenge->join_code }}</strong> o añade estudiantes manualmente.</p>
                @if(Auth::user()->isDocente())
                    <button type="button" class="btn btn-primary mt-3" onclick="openAddStudentModal()">
                        Añadir Estudiante Manualmente
                    </button>
                @endif
            </div>
        @endif
    @endif
</div>

<!-- Score Adjustment Modal -->
<div id="scoreModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalStudentName" class="modal-title">Ajustar Puntos</h2>
            <span class="close" onclick="closeScoreModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="suggestedPointsInfo" class="alert-compact-info" style="display:none; margin-bottom: 0.5rem;">
                💡 Sugerencia: <strong id="suggestedPointsValue"></strong> puntos
            </div>
            <div class="text-center mb-2 small text-muted">
                Rango permitido: {{ $challenge->min_points ?? 0 }} - {{ $challenge->max_points }} pts
            </div>
            <form id="scoreForm" method="POST" class="score-form-modal">
                @csrf
                <div class="score-controls">
                    <button type="button" class="btn-adjust minus" onclick="adjustModalScore(-1)">-1</button>
                    <button type="button" class="btn-adjust minus" onclick="adjustModalScore(-5)">-5</button>
                    
                    <input type="number" id="modalPointsInput" name="points" class="form-control text-center" min="0" max="{{ $challenge->max_points }}" required>
                    
                    <button type="button" class="btn-adjust plus" onclick="adjustModalScore(1)">+1</button>
                    <button type="button" class="btn-adjust plus" onclick="adjustModalScore(5)">+5</button>
                </div>
                <div style="text-align: center; margin-top: 0.5rem;">
                    <button type="button" class="btn btn-sm btn-outline" onclick="applySuggestedPoints()">Usar Sugerencia</button>
                </div>
                <div class="modal-footer-custom">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="closeScoreModal()">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Validation Modal -->
<div id="validateModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="validateModalStudentName" class="modal-title">Validar</h2>
            <span class="close" onclick="closeValidateModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p class="small mb-2">Penalización por errores:</p>
            <form id="validateForm" method="POST" class="score-form-modal">
                @csrf
                <div class="form-group">
                    <div class="penalty-controls">
                        <button type="button" class="btn-penalty" onclick="setPenalty(0)">0s</button>
                        <button type="button" class="btn-penalty" onclick="setPenalty(10)">10s</button>
                        <button type="button" class="btn-penalty" onclick="setPenalty(30)">30s</button>
                        <button type="button" class="btn-penalty" onclick="setPenalty(60)">60s</button>
                    </div>
                    <input type="number" id="penaltySeconds" name="penalty_seconds" class="form-control form-control-sm mt-1" value="0" min="0" placeholder="Segundos extra">
                </div>
                <div class="modal-footer-custom">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="closeValidateModal()">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-success">Aplicar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div id="addStudentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">➕ Añadir Estudiante</h2>
            <span class="close" onclick="closeAddStudentModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="{{ route('challenge.addStudent', $challenge) }}" method="POST" class="score-form-modal">
                @csrf
                <div class="form-group">
                    <label for="studentSelect" class="form-label">Seleccionar Estudiante:</label>
                    <select name="student_id" id="studentSelect" class="form-control" required style="width: 100%; padding: 0.5rem;">
                        <option value="">-- Buscar estudiante --</option>
                        @foreach($availableStudents as $student)
                            <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer-custom">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="closeAddStudentModal()">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-success">Añadir al Desafío</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Teams Modal -->
<div id="teamsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">👥 Formar Equipos</h2>
            <span class="close" onclick="closeTeamsModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p class="small mb-2 text-center">Selecciona el tamaño de los equipos:</p>
            <form action="{{ route('challenge.teams.create', $challenge) }}" method="POST" class="score-form-modal">
                @csrf
                <div class="form-group text-center">
                    <div class="score-controls">
                        <button type="button" class="btn-adjust minus" onclick="adjustTeamSize(-1)">-</button>
                        <input type="number" id="teamSizeInput" name="team_size" class="form-control text-center" value="2" min="2" max="10" required style="width: 60px; font-size: 1.2rem; font-weight: bold;">
                        <button type="button" class="btn-adjust plus" onclick="adjustTeamSize(1)">+</button>
                    </div>
                    <small class="text-muted">Integrantes por equipo</small>
                </div>
                <div class="modal-footer-custom">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="closeTeamsModal()">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary">Formar Equipos</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Roulette Modal -->
<div id="rouletteModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2 class="modal-title">🎰 Ruleta de Estudiantes</h2>
            <span class="close" onclick="closeRouletteModal()">&times;</span>
        </div>
        <div class="modal-body">
            <!-- Step 1: Select Participants -->
            <div id="rouletteStep1" class="roulette-step">
                <h3>Selecciona los participantes:</h3>
                <div class="participants-selection">
                    <div class="selection-controls">
                        <button type="button" class="btn btn-sm btn-outline" onclick="selectAllParticipants()">Seleccionar Todos</button>
                        <button type="button" class="btn btn-sm btn-outline" onclick="deselectAllParticipants()">Deseleccionar Todos</button>
                    </div>
                    <div id="participantsList" class="participants-checkboxes"></div>
                </div>
                <div class="modal-footer-custom">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="closeRouletteModal()">Cancelar</button>
                    <button type="button" class="btn btn-sm btn-primary" onclick="spinRoulette()">� Girar Ruleta</button>
                </div>
            </div>

            <!-- Step 2: Spinning Animation -->
            <div id="rouletteStep2" class="roulette-step" style="display:none;">
                <div class="roulette-spinner">
                    <div class="spinner-display" id="spinnerDisplay">Girando...</div>
                </div>
            </div>

            <!-- Step 3: Winner & Points -->
            <div id="rouletteStep3" class="roulette-step" style="display:none;">
                <div class="roulette-winner">
                    <h2>🎉 Ganador:</h2>
                    <div class="winner-name" id="winnerName"></div>
                    <div class="winner-current-points" id="winnerCurrentPoints" style="margin-top: 0.5rem; font-size: 1.1rem; color: #6b7280;"></div>
                </div>
                <div class="points-assignment">
                    <h3>Asignar puntos:</h3>
                    <div class="points-buttons">
                        <button type="button" class="btn-points" onclick="assignRoulettePoints(1)">+1</button>
                        <button type="button" class="btn-points" onclick="assignRoulettePoints(5)">+5</button>
                        <button type="button" class="btn-points" onclick="assignRoulettePoints(10)">+10</button>
                    </div>
                    <div class="custom-points">
                        <input type="number" id="customRoulettePoints" class="form-control" min="1" placeholder="Puntos personalizados">
                        <button type="button" class="btn btn-sm btn-success" onclick="assignCustomRoulettePoints()">Asignar</button>
                    </div>
                </div>
                <div class="modal-footer-custom">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="closeRouletteModal()">Volver a Pizarra</button>
                    <button type="button" class="btn btn-sm btn-primary" onclick="resetRoulette()">🎰 Nueva Ruleta</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.ChallengeConfig = {
        challengeId: {{ $challenge->id }},
        isDocente: {{ Auth::user()->isDocente() ? 'true' : 'false' }},
        userId: {{ Auth::id() }},
        minPoints: {{ $challenge->min_points ?? 0 }},
        maxPoints: {{ $challenge->max_points ?? 100 }},
        seconds: {{ $challenge->getCurrentTimeSeconds() }},
        isRunning: {{ $challenge->started_at && !$challenge->paused_at ? 'true' : 'false' }}
    };
</script>
<script src="{{ asset('js/challenge-show.js') }}?v={{ time() }}"></script>
<link rel="stylesheet" href="{{ asset('css/challenge-show.css') }}">
@endpush
@endsection
