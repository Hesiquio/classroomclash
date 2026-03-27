@extends('layouts.app')

@section('title', 'Gestión de Estudiantes - Classroom Clash')

@section('content')

{{-- ═══ HEADER ═══ --}}
<div class="st-page-header">
    <div class="st-page-title">
        <a href="{{ route('dashboard') }}" class="st-back">‹</a>
        <div>
            <h1>👥 Estudiantes</h1>
            <p>{{ $students->count() }} registrado{{ $students->count() !== 1 ? 's' : '' }}</p>
        </div>
    </div>
    {{-- Stats rápidas inline en móvil --}}
    <div class="st-mini-stats">
        <div class="st-ms-item">
            <span class="st-ms-num">{{ $students->where('is_guest', true)->count() }}</span>
            <span class="st-ms-lbl">Invitados</span>
        </div>
        <div class="st-ms-item">
            <span class="st-ms-num">{{ $students->where('is_guest', false)->count() }}</span>
            <span class="st-ms-lbl">Regulares</span>
        </div>
    </div>
</div>

{{-- ═══ ALERTAS DE SESIÓN ═══ --}}
@if(session('password_reset'))
<div class="st-alert st-alert--blue">
    <div class="st-alert-top">
        <div>
            <strong>🔑 Contraseña generada — {{ session('password_reset')['name'] }}</strong>
            <p>Comunícala al estudiante. No se volverá a mostrar.</p>
        </div>
        <div class="st-alert-code" onclick="copyText(this)" title="Clic para copiar">
            {{ session('password_reset')['password'] }}
        </div>
    </div>
</div>
@endif

@if(session('guest_created'))
<div class="st-alert st-alert--green">
    <strong>✅ {{ count(session('guest_created')) }} estudiante(s) creados · URL: {{ url('/claim') }}</strong>
    <div class="st-created-list">
        @foreach(session('guest_created') as $g)
        <div class="st-created-row">
            <span>{{ $g['name'] }}</span>
            <span class="st-created-code" onclick="copyText(this)">{{ $g['code'] }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ═══ ACCIONES RÁPIDAS (acordeón en móvil) ═══ --}}
<div class="st-actions-bar">
    <button class="st-action-btn st-action-btn--primary" onclick="toggleAccordion('accCreate')">
        ➕ Crear Invitados
    </button>
    <span class="st-action-sep">|</span>
    <button class="st-action-btn" onclick="toggleAccordion('accReset')" id="btnResetGlobal" style="opacity:.5; cursor:default;" disabled>
        🔑 Resetear (selecciona un estudiante ↓)
    </button>
</div>

{{-- Acordeón: Crear invitados --}}
<div class="st-accordion" id="accCreate">
    <form action="{{ route('guest.quick-create') }}" method="POST">
        @csrf
        <p class="acc-desc">Un nombre por línea. Se genera un código de acceso único para cada uno.</p>
        <textarea name="names" class="form-control" rows="5"
            placeholder="Juan García&#10;María López&#10;Carlos Pérez"
            required style="margin-bottom:.65rem;"></textarea>
        @error('names')
            <p class="field-error">{{ $message }}</p>
        @enderror
        <p class="acc-hint">💡 Acceso en: <strong>{{ url('/claim') }}</strong></p>
        <button type="submit" class="btn btn-primary btn-block" style="margin-top:.65rem;">Generar Códigos</button>
    </form>
</div>

{{-- Acordeón: Resetear contraseña --}}
<div class="st-accordion" id="accReset">
    <form action="{{ route('guest.reset-password') }}" method="POST">
        @csrf
        <input type="hidden" name="student_id" id="resetStudentId">
        <div class="acc-reset-target">
            <div class="acc-target-avatar" id="accResetAvatar">?</div>
            <div>
                <p class="acc-target-label">Estudiante seleccionado:</p>
                <strong id="accResetName" style="font-size:.95rem;">—</strong>
            </div>
        </div>
        @error('student_id')
            <p class="field-error">{{ $message }}</p>
        @enderror
        <button type="submit" class="btn btn-primary btn-block" style="margin-top:.75rem;">
            🔑 Generar Contraseña Temporal
        </button>
    </form>
</div>

{{-- ═══ BUSCADOR ═══ --}}
<div class="st-search-wrap">
    <input type="text" id="studentSearch" class="form-control"
        placeholder="🔍 Buscar por nombre..."
        oninput="filterStudents(this.value)">
</div>

{{-- ═══ LISTA DE ESTUDIANTES ═══ --}}
@if($students->isEmpty())
    <div class="st-empty">
        <div style="font-size:2.5rem; margin-bottom:.5rem;">🎓</div>
        <p>No hay estudiantes registrados aún.</p>
        <p>Usa el botón <strong>Crear Invitados</strong> para empezar.</p>
    </div>
@else

    {{-- TARJETAS (móvil y tablet) --}}
    <div class="st-cards" id="stCardList">
        @foreach($students as $s)
        <div class="st-card" data-name="{{ strtolower($s->name) }}">
            <div class="st-card-left">
                <div class="st-avatar {{ $s->is_guest ? 'st-avatar--guest' : '' }}">
                    {{ strtoupper(substr($s->name, 0, 1)) }}
                </div>
            </div>
            <div class="st-card-body">
                <div class="st-card-name">{{ $s->name }}</div>
                <div class="st-card-meta">
                    @if($s->is_guest)
                        <span class="st-badge st-badge--guest">⚡ invitado</span>
                    @else
                        <span class="st-card-email">{{ $s->email }}</span>
                    @endif
                    <span class="st-card-challenges">{{ $s->participations_count }} desafío{{ $s->participations_count !== 1 ? 's' : '' }}</span>
                </div>
                <div class="st-card-date">Desde {{ $s->created_at->format('d/m/Y') }}</div>
            </div>
            <div class="st-card-actions">
                <button
                    class="st-icon-btn"
                    onclick="selectForReset({{ $s->id }}, '{{ addslashes($s->name) }}')"
                    title="Contraseña temporal">
                    🔑
                </button>
            </div>
        </div>
        @endforeach
    </div>

    {{-- TABLA (solo desktop, oculta en móvil) --}}
    <div class="st-table-wrap" id="stTableWrap">
        <table class="st-table">
            <thead>
                <tr>
                    <th>Estudiante</th>
                    <th>Correo / Tipo</th>
                    <th>Desafíos</th>
                    <th>Registrado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $s)
                <tr class="st-row" data-name="{{ strtolower($s->name) }}">
                    <td>
                        <div style="display:flex; align-items:center; gap:.6rem;">
                            <div class="st-avatar st-avatar--sm {{ $s->is_guest ? 'st-avatar--guest' : '' }}">
                                {{ strtoupper(substr($s->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="st-card-name">{{ $s->name }}</div>
                                @if($s->is_guest)
                                    <span class="st-badge st-badge--guest">⚡ invitado</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td style="font-size:.82rem; color:#64748b;">
                        @if($s->is_guest)
                            <em style="color:#94a3b8;">Sin email (invitado)</em>
                        @else
                            {{ $s->email }}
                        @endif
                    </td>
                    <td>
                        <span class="st-count">{{ $s->participations_count }}</span>
                    </td>
                    <td style="font-size:.8rem; color:#64748b;">
                        {{ $s->created_at->format('d/m/Y') }}
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary"
                            onclick="selectForReset({{ $s->id }}, '{{ addslashes($s->name) }}')"
                            title="Contraseña temporal">
                            🔑
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endif

@push('scripts')
<script>
/** Abre/cierra un acordeón */
function toggleAccordion(id) {
    const el = document.getElementById(id);
    const isOpen = el.classList.contains('st-accordion--open');
    // Cerrar todos
    document.querySelectorAll('.st-accordion').forEach(a => a.classList.remove('st-accordion--open'));
    if (!isOpen) el.classList.add('st-accordion--open');
}

/** Selecciona un estudiante para resetear y abre el acordeón */
function selectForReset(id, name) {
    document.getElementById('resetStudentId').value = id;
    document.getElementById('accResetName').textContent = name;
    document.getElementById('accResetAvatar').textContent = name.charAt(0).toUpperCase();

    // Habilitar botón global
    const btn = document.getElementById('btnResetGlobal');
    btn.disabled = false;
    btn.style.opacity = '1';
    btn.style.cursor = 'pointer';

    // Abrir acordeón de reset
    document.querySelectorAll('.st-accordion').forEach(a => a.classList.remove('st-accordion--open'));
    document.getElementById('accReset').classList.add('st-accordion--open');

    // Scroll suave al acordeón
    document.getElementById('accReset').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/** Filtra tarjetas Y filas de tabla */
function filterStudents(q) {
    const term = q.toLowerCase();
    document.querySelectorAll('.st-card, .st-row').forEach(el => {
        el.style.display = el.dataset.name.includes(term) ? '' : 'none';
    });
}

/** Copia texto al portapapeles con feedback visual */
function copyText(el) {
    const txt = el.textContent.trim();
    navigator.clipboard.writeText(txt).then(() => {
        const orig = el.textContent;
        el.textContent = '✅ Copiado'; 
        setTimeout(() => el.textContent = orig, 1800);
    }).catch(() => {
        const ta = document.createElement('textarea');
        ta.value = txt; document.body.appendChild(ta);
        ta.select(); document.execCommand('copy'); ta.remove();
    });
}
</script>

<style>
/* ─── Header ─── */
.st-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}
.st-page-title {
    display: flex;
    align-items: center;
    gap: .75rem;
}
.st-back {
    display: flex; align-items: center; justify-content: center;
    width: 36px; height: 36px; border-radius: 50%;
    background: white; border: 1px solid #e2e8f0;
    color: #475569; font-size: 1.3rem; font-weight: 700;
    text-decoration: none; flex-shrink: 0;
    transition: background .15s;
}
.st-back:hover { background: #f1f5f9; }
.st-page-title h1 { font-size: 1.2rem; margin: 0; color: #1e293b; }
.st-page-title p  { font-size: .78rem; color: #94a3b8; margin: 0; }

/* ─── Mini stats ─── */
.st-mini-stats {
    display: flex;
    gap: .75rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: .5rem .875rem;
}
.st-ms-item { text-align: center; }
.st-ms-num  { display: block; font-size: 1.2rem; font-weight: 800; color: #6366f1; line-height: 1; }
.st-ms-lbl  { display: block; font-size: .65rem; color: #94a3b8; font-weight: 500; text-transform: uppercase; }

/* ─── Alertas ─── */
.st-alert {
    border-radius: 12px;
    padding: .875rem 1rem;
    margin-bottom: 1rem;
    font-size: .875rem;
}
.st-alert--blue  { background: #eff6ff; border: 1.5px solid #93c5fd; color: #1e40af; }
.st-alert--green { background: #f0fdf4; border: 1.5px solid #86efac; color: #166534; }
.st-alert-top {
    display: flex;
    align-items: center;
    gap: .875rem;
    flex-wrap: wrap;
}
.st-alert strong { display: block; font-size: .875rem; }
.st-alert p { font-size: .78rem; margin: .15rem 0 0; opacity: .8; }
.st-alert-code {
    background: #1e40af; color: white;
    border-radius: 8px; padding: .4rem .875rem;
    font-size: 1.2rem; font-weight: 800; letter-spacing: 3px;
    cursor: pointer; font-family: monospace; flex-shrink: 0;
    transition: opacity .15s;
}
.st-alert-code:hover { opacity: .85; }
.st-created-list { display: flex; flex-direction: column; gap: .25rem; margin-top: .6rem; }
.st-created-row  { display: flex; align-items: center; gap: .75rem; }
.st-created-row span:first-child { font-weight: 600; flex: 1; font-size: .85rem; }
.st-created-code {
    background: #dcfce7; color: #166534; font-weight: 700;
    padding: .15rem .5rem; border-radius: 6px; font-family: monospace;
    letter-spacing: 2px; cursor: pointer; font-size: .82rem;
}

/* ─── Barra de acciones ─── */
.st-actions-bar {
    display: flex;
    align-items: center;
    gap: .5rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: .6rem .875rem;
    margin-bottom: .5rem;
    flex-wrap: wrap;
}
.st-action-btn {
    background: none; border: none; cursor: pointer;
    font-size: .85rem; font-weight: 600; color: #475569;
    padding: .25rem .5rem; border-radius: 6px;
    transition: background .15s, color .15s;
    font-family: 'Inter', sans-serif;
    white-space: nowrap;
}
.st-action-btn:hover:not(:disabled) { background: #f1f5f9; color: #6366f1; }
.st-action-btn--primary { color: #6366f1; }
.st-action-sep { color: #e2e8f0; font-size: 1.1rem; }

/* ─── Acordeón ─── */
.st-accordion {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0;
    max-height: 0;
    overflow: hidden;
    transition: max-height .3s ease, padding .3s ease, margin .3s ease;
    margin-bottom: 0;
}
.st-accordion--open {
    max-height: 600px;
    padding: 1rem;
    margin-bottom: .75rem;
}
.acc-desc { font-size: .82rem; color: #64748b; margin-bottom: .6rem; }
.acc-hint { font-size: .75rem; color: #94a3b8; }
.acc-reset-target {
    display: flex;
    align-items: center;
    gap: .75rem;
    background: #f8fafc;
    border-radius: 8px;
    padding: .65rem .875rem;
}
.acc-target-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #ec4899);
    color: white; font-size: .9rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.acc-target-label { font-size: .72rem; color: #94a3b8; margin-bottom: .1rem; }

/* ─── Buscador ─── */
.st-search-wrap { margin-bottom: .75rem; }

/* ─── Empty ─── */
.st-empty {
    background: white; border: 2px dashed #e2e8f0;
    border-radius: 12px; padding: 2.5rem 1rem;
    text-align: center; color: #94a3b8; font-size: .875rem;
}

/* ─── TARJETAS (mobile & tablet) ─── */
.st-cards { display: flex; flex-direction: column; gap: .5rem; }

.st-card {
    display: flex;
    align-items: center;
    gap: .75rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: .75rem .875rem;
    transition: border-color .2s, box-shadow .2s;
}
.st-card:hover { border-color: #c7d2fe; box-shadow: 0 2px 8px rgba(99,102,241,.08); }

.st-card-body  { flex: 1; min-width: 0; }
.st-card-name  { font-size: .9rem; font-weight: 700; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.st-card-meta  { display: flex; align-items: center; gap: .5rem; margin-top: .2rem; flex-wrap: wrap; }
.st-card-email { font-size: .75rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px; }
.st-card-challenges { font-size: .72rem; color: #94a3b8; white-space: nowrap; }
.st-card-date  { font-size: .7rem; color: #cbd5e1; margin-top: .15rem; }
.st-card-actions { flex-shrink: 0; }

/* Avatar */
.st-avatar {
    width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, #6366f1, #a855f7);
    color: white; font-size: 1rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
}
.st-avatar--sm  { width: 32px; height: 32px; font-size: .8rem; }
.st-avatar--guest { background: linear-gradient(135deg, #f59e0b, #ef4444); }

.st-badge { display: inline-block; font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; padding: .1rem .4rem; border-radius: 4px; }
.st-badge--guest { background: #e0e7ff; color: #4f46e5; }

.st-icon-btn {
    background: #f8fafc; border: 1px solid #e2e8f0;
    border-radius: 8px; width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 1rem; transition: background .15s;
}
.st-icon-btn:hover { background: #e0e7ff; border-color: #a5b4fc; }

.field-error { font-size: .78rem; color: #ef4444; margin-top: .25rem; }

/* ─── TABLA (solo desktop ≥ 900px) ─── */
.st-table-wrap { display: none; } /* oculta en móvil */

@media (min-width: 900px) {
    .st-cards     { display: none; }  /* ocultar tarjetas en desktop */
    .st-table-wrap {
        display: block;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,.05);
    }
    .st-table {
        width: 100%; border-collapse: collapse; font-size: .875rem;
    }
    .st-table thead tr { background: #f8fafc; border-bottom: 2px solid #e2e8f0; }
    .st-table th {
        padding: .7rem 1rem; text-align: left; font-size: .72rem;
        font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .05em;
    }
    .st-table td {
        padding: .75rem 1rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle;
    }
    .st-row:last-child td { border-bottom: none; }
    .st-row:hover td { background: #fafafa; }
    .st-count {
        display: inline-block; background: #f1f5f9; color: #475569;
        font-weight: 700; font-size: .82rem; padding: .15rem .5rem; border-radius: 6px;
    }

    /* En desktop el layout es de 2 columnas: acciones + lista */
    body .st-actions-bar { border-radius: 10px 10px 0 0; margin-bottom: 0; border-bottom: none; }
    body .st-accordion { border-radius: 0; border-top: none; }
    body .st-accordion--open { margin-bottom: 0; border-bottom: 2px solid #e2e8f0; }
}

/* ─── Tablet intermedio ─── */
@media (min-width: 640px) and (max-width: 899px) {
    .st-cards { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; }
}
</style>
@endpush
@endsection
