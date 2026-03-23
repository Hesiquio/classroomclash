<?php $__env->startSection('title', 'Dashboard Estudiante - Classroom Clash'); ?>

<?php $__env->startSection('content'); ?>


<div class="join-bar">
    <form action="<?php echo e(route('challenge.join')); ?>" method="POST" class="join-bar-form">
        <?php echo csrf_field(); ?>
        <span class="join-bar-icon">🔑</span>
        <input
            type="text"
            name="join_code"
            id="join_code"
            class="join-bar-input"
            placeholder="Código (ej: ABC123)"
            maxlength="6"
            autocomplete="off"
            required>
        <button type="submit" class="join-bar-btn">Unirse</button>
    </form>
    <?php $__errorArgs = ['join_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="join-bar-error">⚠️ <?php echo e($message); ?></p>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>


<?php if($stats['total_challenges'] > 0): ?>
<div class="motivational-banner motivational-<?php echo e($stats['motivational'][1]); ?>">
    <?php echo e($stats['motivational'][0]); ?>

</div>
<?php endif; ?>


<div class="main-layout">

    
    <div class="main-left">

        
        <?php if($stats['total_challenges'] > 0): ?>
        <div class="stats-grid">

            <div class="stat-card stat-card--primary">
                <div class="stat-ring-wrap">
                    <svg class="stat-ring" viewBox="0 0 64 64">
                        <circle cx="32" cy="32" r="26" fill="none" stroke="#e2e8f0" stroke-width="6"/>
                        <circle cx="32" cy="32" r="26" fill="none" stroke="url(#grad1)" stroke-width="6"
                            stroke-dasharray="<?php echo e(round($stats['avg_performance'] * 1.634)); ?> 163.4"
                            stroke-dashoffset="40.8"
                            stroke-linecap="round"/>
                        <defs>
                            <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#6366f1"/>
                                <stop offset="100%" stop-color="#ec4899"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <span class="stat-ring-value"><?php echo e($stats['avg_performance']); ?>%</span>
                </div>
                <div class="stat-label">Rendimiento<br>Promedio</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🏁</div>
                <div class="stat-value"><?php echo e($stats['total_challenges']); ?></div>
                <div class="stat-label">Desafíos<br>jugados</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">⭐</div>
                <div class="stat-value"><?php echo e(number_format($stats['total_points'])); ?></div>
                <div class="stat-label">Puntos<br>totales</div>
            </div>

            <div class="stat-card <?php echo e($stats['top3_count'] > 0 ? 'stat-card--highlight' : ''); ?>">
                <div class="stat-icon">🏆</div>
                <div class="stat-value"><?php echo e($stats['top3_count']); ?></div>
                <div class="stat-label">Veces en<br>Top 3</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <?php if($stats['best_rank'] === 1): ?> 🥇
                    <?php elseif($stats['best_rank'] === 2): ?> 🥈
                    <?php elseif($stats['best_rank'] === 3): ?> 🥉
                    <?php else: ?> 🎯
                    <?php endif; ?>
                </div>
                <div class="stat-value">#<?php echo e($stats['best_rank'] ?? '—'); ?></div>
                <div class="stat-label">Mejor<br>posición</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📬</div>
                <div class="stat-value"><?php echo e($stats['submitted']); ?></div>
                <div class="stat-label">Entregas<br>realizadas</div>
            </div>

        </div>
        <?php endif; ?>

        
        <div class="section-block">
            <h2 class="section-heading">
                🔴 Desafíos Activos
                <?php if($myParticipations->isNotEmpty()): ?>
                    <span class="section-badge"><?php echo e($myParticipations->count()); ?></span>
                <?php endif; ?>
            </h2>

            <?php if($myParticipations->isEmpty()): ?>
                <div class="empty-challenges">
                    <div class="empty-challenges-icon">📋</div>
                    <p>No tienes desafíos activos.</p>
                    <p class="empty-hint">Ingresa un código arriba para unirte.</p>
                </div>
            <?php else: ?>
                <div class="challenges-list">
                    <?php $__currentLoopData = $myParticipations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('challenge.show', $p->challenge)); ?>" class="challenge-item">
                        <div class="ci-left">
                            <div class="ci-name"><?php echo e($p->challenge->name); ?></div>
                            <?php if($p->finished_at): ?>
                                <div class="ci-status ci-status--done">
                                    ✅ Entregado · ⏱ <?php echo e(gmdate('H:i:s', $p->duration_seconds)); ?>

                                </div>
                            <?php else: ?>
                                <div class="ci-status ci-status--active">⏳ En progreso</div>
                            <?php endif; ?>
                        </div>
                        <div class="ci-right">
                            <span class="ci-pts-num"><?php echo e($p->points); ?></span>
                            <span class="ci-pts-lbl">pts</span>
                        </div>
                        <div class="ci-arrow">›</div>
                    </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    
    <div class="main-right">

        <div class="section-block">
            <h2 class="section-heading">👤 Mi Perfil</h2>

            <div class="profile-card">
                
                <div class="profile-avatar">
                    <?php echo e(strtoupper(substr(Auth::user()->name, 0, 1))); ?>

                </div>
                <div class="profile-name-display"><?php echo e(Auth::user()->name); ?></div>
                <div class="profile-email-display"><?php echo e(Auth::user()->email); ?></div>

                <form action="<?php echo e(route('profile.update')); ?>" method="POST" class="profile-form">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    <div class="profile-field">
                        <label for="profile_name">Nombre completo</label>
                        <input
                            type="text"
                            id="profile_name"
                            name="name"
                            class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            value="<?php echo e(old('name', Auth::user()->name)); ?>"
                            required minlength="2" maxlength="80"
                            placeholder="Tu nombre completo">
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="field-error"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="profile-field">
                        <label for="profile_email">Correo electrónico</label>
                        <input
                            type="email"
                            id="profile_email"
                            name="email"
                            class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            value="<?php echo e(old('email', Auth::user()->email)); ?>"
                            required
                            placeholder="tu@correo.com">
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="field-error"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" style="margin-top:.875rem;">
                        💾 Guardar Cambios
                    </button>
                </form>
            </div>

        </div>

    </div>

</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.getElementById('join_code').addEventListener('input', function () {
    this.value = this.value.toUpperCase();
});
</script>

<style>
/* ════════════════════════════════════════
   MOBILE FIRST — base = celular
   ════════════════════════════════════════ */

/* ── Barra unirse ── */
.join-bar {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: .65rem 1rem;
    margin-bottom: 1rem;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
}
.join-bar-form {
    display: flex;
    align-items: center;
    gap: .5rem;
}
.join-bar-icon { font-size: 1.1rem; flex-shrink:0; }
.join-bar-input {
    flex: 1;
    min-width: 0;                    /* ← permite que shrinkee bien */
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: .5rem .75rem;
    font-size: .95rem;
    font-family: 'Inter', sans-serif;
    letter-spacing: 2px;
    font-weight: 600;
    text-transform: uppercase;
    outline: none;
    transition: border-color .2s;
}
.join-bar-input:focus { border-color: #6366f1; }
.join-bar-input::placeholder {
    letter-spacing: 0; font-weight: 400;
    text-transform: none; color: #94a3b8; font-size:.85rem;
}
.join-bar-btn {
    background: linear-gradient(135deg,#6366f1,#8b5cf6);
    color: white; border: none; border-radius: 8px;
    padding: .5rem 1rem; font-size: .875rem; font-weight: 600;
    cursor: pointer; transition: opacity .2s, transform .15s;
    font-family: 'Inter', sans-serif; flex-shrink: 0;
    white-space: nowrap;
}
.join-bar-btn:hover { opacity:.88; transform:translateY(-1px); }
.join-bar-error { margin:.3rem 0 0; color:#ef4444; font-size:.8rem; }

/* ── Banner motivacional ── */
.motivational-banner {
    border-radius: 10px;
    padding: .6rem 1rem;
    margin-bottom: 1rem;
    font-weight: 600;
    font-size: .88rem;
    text-align: center;
}
.motivational-gold   { background:linear-gradient(135deg,#fef3c7,#fde68a); color:#92400e; }
.motivational-green  { background:linear-gradient(135deg,#d1fae5,#a7f3d0); color:#065f46; }
.motivational-blue   { background:linear-gradient(135deg,#dbeafe,#bfdbfe); color:#1e40af; }
.motivational-purple { background:linear-gradient(135deg,#ede9fe,#ddd6fe); color:#5b21b6; }
.motivational-gray   { background:#f1f5f9; color:#475569; }
.motivational-info   { background:linear-gradient(135deg,#e0f2fe,#bae6fd); color:#0c4a6e; }

/* ── Layout principal: 1 col en móvil ── */
.main-layout {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.main-left  { display:flex; flex-direction:column; gap:1rem; }
.main-right { display:flex; flex-direction:column; gap:1rem; }

/* ── Stats grid: 3 col en móvil ── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: .6rem;
}
.stat-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: .75rem .5rem;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
    transition: transform .2s, box-shadow .2s;
}
.stat-card:hover { transform:translateY(-2px); box-shadow:0 5px 14px rgba(0,0,0,.08); }
.stat-card--highlight {
    border-color:#fbbf24;
    background:linear-gradient(to bottom right,white,#fffbeb);
}
.stat-card--primary {
    border-color:#a5b4fc;
    background:linear-gradient(to bottom right,white,#eef2ff);
}
.stat-icon  { font-size:1.4rem; margin-bottom:.3rem; }
.stat-value { font-size:1.35rem; font-weight:800; color:#1e293b; line-height:1; }
.stat-label { font-size:.65rem; color:#64748b; margin-top:.25rem; line-height:1.3; font-weight:500; }

/* SVG ring */
.stat-ring-wrap { position:relative; width:52px; height:52px; margin:0 auto .3rem; }
.stat-ring       { width:52px; height:52px; transform:rotate(-90deg); }
.stat-ring-value {
    position:absolute; inset:0;
    display:flex; align-items:center; justify-content:center;
    font-size:.7rem; font-weight:800; color:#6366f1;
}

/* ── Desafíos activos ── */
.section-block { display:flex; flex-direction:column; gap:.5rem; }
.section-heading {
    font-size:.95rem; font-weight:700; color:#1e293b;
    display:flex; align-items:center; gap:.4rem;
    margin:0;
}
.section-badge {
    background:#6366f1; color:white;
    border-radius:9999px; font-size:.65rem;
    font-weight:700; padding:.1rem .45rem;
}

.challenges-list { display:flex; flex-direction:column; gap:.5rem; }

.challenge-item {
    display:flex; align-items:center;
    background:white; border:1px solid #e2e8f0; border-radius:12px;
    padding:.875rem 1rem; text-decoration:none; color:inherit;
    transition:all .2s; box-shadow:0 1px 3px rgba(0,0,0,.04);
    gap:.75rem;
}
.challenge-item:hover {
    border-color:#6366f1; transform:translateY(-1px);
    box-shadow:0 5px 14px rgba(99,102,241,.12);
}
/* Tap highlight en móvil */
.challenge-item:active { transform:scale(.98); }

.ci-left  { flex:1; min-width:0; }
.ci-name  { font-size:.9rem; font-weight:700; color:#1e293b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ci-status { font-size:.75rem; font-weight:500; margin-top:.15rem; }
.ci-status--active { color:#10b981; }
.ci-status--done   { color:#64748b; }

.ci-right  { text-align:center; flex-shrink:0; }
.ci-pts-num { display:block; font-size:1.5rem; font-weight:800; color:#6366f1; line-height:1; }
.ci-pts-lbl { font-size:.6rem; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:.08em; }

.ci-arrow { color:#cbd5e1; font-size:1.4rem; font-weight:300; flex-shrink:0; }

.empty-challenges {
    background:white; border:2px dashed #e2e8f0; border-radius:12px;
    padding:2rem 1rem; text-align:center; color:#94a3b8;
}
.empty-challenges-icon { font-size:2rem; margin-bottom:.5rem; }
.empty-hint { font-size:.8rem; margin-top:.2rem; }

/* ── Perfil ── */
.profile-card {
    background:white; border:1px solid #e2e8f0; border-radius:12px;
    padding:1.25rem; box-shadow:0 1px 4px rgba(0,0,0,.05);
}
.profile-avatar {
    width:52px; height:52px; border-radius:50%;
    background:linear-gradient(135deg,#6366f1,#ec4899);
    color:white; font-size:1.3rem; font-weight:800;
    display:flex; align-items:center; justify-content:center;
    margin:0 auto .4rem;
}
.profile-name-display  { text-align:center; font-weight:700; color:#1e293b; font-size:.9rem; }
.profile-email-display { text-align:center; font-size:.75rem; color:#94a3b8; margin-bottom:.875rem; }

.profile-form { display:flex; flex-direction:column; gap:.65rem; }
.profile-field label {
    display:block; font-size:.72rem; font-weight:600;
    color:#64748b; text-transform:uppercase; letter-spacing:.05em; margin-bottom:.2rem;
}
.profile-field .form-control { font-size:.9rem; padding:.45rem .7rem; }
.field-error { display:block; font-size:.75rem; color:#ef4444; margin-top:.15rem; }


/* ════════════════════════════════════════
   TABLET  ≥ 600px
   ════════════════════════════════════════ */
@media (min-width: 600px) {
    .stats-grid { grid-template-columns: repeat(3, 1fr); gap: .75rem; }
    .stat-icon  { font-size:1.6rem; }
    .stat-value { font-size:1.5rem; }
    .stat-label { font-size:.7rem; }
    .stat-ring-wrap { width:60px; height:60px; }
    .stat-ring       { width:60px; height:60px; }
    .stat-ring-value { font-size:.75rem; }
}


/* ════════════════════════════════════════
   DESKTOP ≥ 900px — 2 columnas
   ════════════════════════════════════════ */
@media (min-width: 900px) {
    .main-layout {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 1.5rem;
        align-items: start;
    }

    /* Stats: 3 cols en columna izquierda */
    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: .875rem;
    }

    .stat-card { padding: .875rem .75rem; }
    .stat-icon  { font-size: 1.6rem; }
    .stat-value { font-size: 1.6rem; }
    .stat-label { font-size: .72rem; }

    .challenge-item { padding: 1rem 1.1rem; }
    .ci-name        { font-size: .95rem; }
    .ci-pts-num     { font-size: 1.7rem; }

    .join-bar-input { max-width: 220px; font-size: 1rem; }
}


/* ════════════════════════════════════════
   LARGE ≥ 1100px — stats en 2 filas × 3
   ════════════════════════════════════════ */
@media (min-width: 1100px) {
    .main-layout {
        grid-template-columns: 1fr 320px;
    }
}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\classroomclash\resources\views/dashboard/estudiante.blade.php ENDPATH**/ ?>