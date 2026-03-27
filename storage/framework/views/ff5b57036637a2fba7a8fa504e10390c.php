

<?php $__env->startSection('title', 'Dashboard Docente - Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="dashboard-header">
    <h1>Mis Desafíos</h1>
    <div class="header-actions" style="display: flex; align-items: center; gap: 0.5rem;">
        <a href="<?php echo e(route('dashboard.archived')); ?>" class="btn btn-secondary" style="line-height: 1.5;">
            📂 Archivados
        </a>
        <a href="<?php echo e(route('students.index')); ?>" class="btn btn-outline-primary" style="line-height:1.5;">
            👥 Estudiantes
        </a>
        <button type="button" class="btn btn-primary" style="line-height: 1.5;" onclick="toggleModal('createChallengeModal')">
            Crear Nuevo Desafío
        </button>
    </div>
</div>

<?php if($challenges->isEmpty()): ?>
    <div class="empty-state">
        <h2>No tienes desafíos creados</h2>
        <p>Crea tu primer desafío para comenzar a gestionar la participación de tus estudiantes.</p>
    </div>
<?php else: ?>
    <div class="challenges-grid">
        <?php $__currentLoopData = $challenges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $challenge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="challenge-card">
                <div class="challenge-header">
                    <h3><?php echo e($challenge->name); ?></h3>
                    <?php if($challenge->is_active): ?>
                        <span class="badge badge-success">Activo</span>
                    <?php else: ?>
                        <span class="badge badge-secondary">Finalizado</span>
                    <?php endif; ?>
                </div>
                <div class="challenge-body">
                    <p><strong>Código de acceso:</strong> <code><?php echo e($challenge->join_code); ?></code></p>
                    <p><strong>Participantes:</strong> <?php echo e($challenge->participants->count()); ?></p>
                    <p><strong>Creado:</strong> <?php echo e($challenge->created_at->format('d/m/Y H:i')); ?></p>
                </div>
                <div class="challenge-footer">
                    <a href="<?php echo e(route('challenge.show', $challenge)); ?>" class="btn btn-sm btn-primary" title="Ver Pizarra">
                        📊
                    </a>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="openEditModal(<?php echo e($challenge->id); ?>, '<?php echo e($challenge->name); ?>', <?php echo e($challenge->min_points ?? 0); ?>, <?php echo e($challenge->max_points ?? 100); ?>)" title="Editar">
                        ✏️
                    </button>
                    <form action="<?php echo e(route('challenge.duplicate', $challenge)); ?>" method="POST" style="display:inline; margin-left:4px;" onsubmit="return confirm('¿Crear una copia de este desafío?')">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Duplicar">
                            📋
                        </button>
                    </form>
                    <form action="<?php echo e(route('challenge.archive', $challenge)); ?>" method="POST" style="display:inline; margin-left:4px;" onsubmit="return confirm('¿Archivar este desafío? Podrás reactivarlo desde la sección de Archivados.')">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Archivar">
                            📦
                        </button>
                    </form>
                    <form action="<?php echo e(route('challenge.destroy', $challenge)); ?>" method="POST" style="display:inline; margin-left:4px;" onsubmit="return confirm('¿Estás seguro de eliminar este desafío? Esta acción no se puede deshacer.')">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                            🗑️
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>


<?php if(session('guest_created')): ?>
<div style="background:#f0fdf4; border:1.5px solid #86efac; border-radius:12px; padding:1.25rem; margin-bottom:1.5rem;">
    <h3 style="color:#166534; font-size:1rem; margin-bottom:.75rem;">✅ Estudiantes creados — guarda estos códigos</h3>
    <table style="width:100%; border-collapse:collapse; font-size:.9rem;">
        <thead>
            <tr style="background:#dcfce7;">
                <th style="padding:.5rem .75rem; text-align:left; border-radius:6px 0 0 6px;">Estudiante</th>
                <th style="padding:.5rem .75rem; text-align:center;">Código de acceso</th>
                <th style="padding:.5rem .75rem; text-align:center; border-radius:0 6px 6px 0;">Enlace directo</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = session('guest_created'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr style="border-bottom:1px solid #bbf7d0;">
                <td style="padding:.5rem .75rem; font-weight:600;"><?php echo e($g['name']); ?></td>
                <td style="padding:.5rem .75rem; text-align:center;">
                    <code style="background:#dcfce7; padding:.2rem .6rem; border-radius:6px; font-size:1rem; letter-spacing:2px; font-weight:700; color:#166534;"><?php echo e($g['code']); ?></code>
                </td>
                <td style="padding:.5rem .75rem; text-align:center; font-size:.8rem; color:#16a34a;">
                    <?php echo e(url('/claim')); ?>?c=<?php echo e($g['code']); ?>

                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <p style="font-size:.8rem; color:#15803d; margin-top:.75rem;">
        ⚠️ Guarda estos códigos antes de salir — no se mostrarán de nuevo.
        El estudiante puede entrar en <strong><?php echo e(url('/claim')); ?></strong>
    </p>
</div>
<?php endif; ?>


<?php if(session('password_reset')): ?>
<div style="background:#eff6ff; border:1.5px solid #93c5fd; border-radius:12px; padding:1.25rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
    <div style="flex:1; min-width:0;">
        <strong style="color:#1e40af;">🔑 Contraseña temporal generada</strong>
        <p style="color:#3b82f6; font-size:.85rem; margin:.2rem 0 0;">Estudiante: <strong><?php echo e(session('password_reset')['name']); ?></strong></p>
    </div>
    <div id="resetPassDisplay" style="background:#1e40af; color:white; border-radius:10px; padding:.5rem 1.25rem; font-size:1.4rem; font-weight:800; letter-spacing:3px; cursor:pointer; font-family:monospace;" onclick="copyResetPass(this)" title="Clic para copiar">
        <?php echo e(session('password_reset')['password']); ?>

    </div>
    <p style="width:100%; font-size:.75rem; color:#3b82f6; margin:0;">⚠️ Comunícala al estudiante ahora — no se mostrará de nuevo. Clic en el código para copiar.</p>
</div>
<?php endif; ?>


<div id="quickStudentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>👥 Gestión de Estudiantes</h2>
            <button type="button" class="close" onclick="toggleModal('quickStudentModal')">&times;</button>
        </div>

        
        <div class="student-tabs">
            <button class="stab stab--active" onclick="switchStudentTab('tabCreate', this)">➕ Crear Invitados</button>
            <button class="stab" onclick="switchStudentTab('tabReset', this)">🔑 Resetear Contraseña</button>
        </div>

        
        <div id="tabCreate">
            <form action="<?php echo e(route('guest.quick-create')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <p style="font-size:.875rem; color:#64748b; margin-bottom:.75rem;">
                        Escribe un nombre por línea. Se generará un código único de acceso para cada estudiante.
                    </p>
                    <div class="form-group">
                        <label for="guest_names">Nombres de estudiantes</label>
                        <textarea
                            id="guest_names"
                            name="names"
                            class="form-control"
                            rows="5"
                            placeholder="Juan García&#10;María López&#10;Carlos Pérez"
                            required></textarea>
                        <?php $__errorArgs = ['names'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div style="color:#ef4444; font-size:.82rem; margin-top:.3rem;"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <p style="font-size:.78rem; color:#94a3b8; margin-top:.4rem;">💡 El estudiante entra en <strong><?php echo e(url('/claim')); ?></strong> con su código.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="toggleModal('quickStudentModal')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Generar Códigos</button>
                </div>
            </form>
        </div>

        
        <div id="tabReset" style="display:none;">
            <form action="<?php echo e(route('guest.reset-password')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <p style="font-size:.875rem; color:#64748b; margin-bottom:.75rem;">
                        Selecciona un estudiante y genera una contraseña temporal. Comunícasela personalmente.
                    </p>

                    
                    <div class="form-group" style="margin-bottom:.5rem;">
                        <input
                            type="text"
                            id="studentSearchReset"
                            class="form-control"
                            placeholder="🔍 Buscar estudiante..."
                            oninput="filterStudentsReset(this.value)">
                    </div>

                    
                    <div id="studentResetList" style="max-height:220px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:8px;">
                        <?php $__empty_1 = true; $__currentLoopData = $allStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <label class="student-reset-row" id="srow-<?php echo e($s->id); ?>">
                            <input type="radio" name="student_id" value="<?php echo e($s->id); ?>" required>
                            <span class="srr-name"><?php echo e($s->name); ?></span>
                            <?php if($s->is_guest): ?>
                                <span class="srr-badge">invitado</span>
                            <?php endif; ?>
                        </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p style="padding:.75rem; color:#94a3b8; font-size:.85rem; text-align:center;">No hay estudiantes registrados todavía.</p>
                        <?php endif; ?>
                    </div>
                    <?php $__errorArgs = ['student_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div style="color:#ef4444; font-size:.82rem; margin-top:.3rem;"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="toggleModal('quickStudentModal')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">🔑 Generar Contraseña Temporal</button>
                </div>
            </form>
        </div>

    </div>
</div>

<div id="createChallengeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Crear Nuevo Desafío</h2>
            <button type="button" class="close" onclick="toggleModal('createChallengeModal')">&times;</button>
        </div>
        <form action="<?php echo e(route('challenge.create')); ?>" method="POST">
            <?php echo csrf_field(); ?>
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
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
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

<?php $__env->startPush('styles'); ?>
<style>
    /* Tooltips en botones */
    .challenge-footer .btn, .challenge-footer a.btn { position: relative; }
    .challenge-footer .btn::after, .challenge-footer a.btn::after {
        content: attr(title); position: absolute; bottom: -30px; left: 50%;
        transform: translateX(-50%); background: rgba(0,0,0,0.8); color: white;
        padding: 4px 8px; border-radius: 4px; font-size: 0.75rem;
        white-space: nowrap; opacity: 0; pointer-events: none;
        transition: opacity 0.2s; z-index: 1000;
    }
    .challenge-footer .btn:hover::after, .challenge-footer a.btn:hover::after { opacity: 1; }

    /* Pestañas del modal de estudiantes */
    .student-tabs {
        display: flex; border-bottom: 2px solid #e2e8f0;
        padding: 0 1rem; gap: .25rem;
    }
    .stab {
        background: none; border: none; padding: .6rem .875rem;
        font-size: .85rem; font-weight: 600; cursor: pointer;
        color: #64748b; border-bottom: 2px solid transparent;
        margin-bottom: -2px; transition: all .15s;
        font-family: 'Inter', sans-serif;
    }
    .stab:hover { color: #6366f1; }
    .stab--active { color: #6366f1; border-bottom-color: #6366f1; }

    /* Lista de estudiantes para reset */
    .student-reset-row {
        display: flex; align-items: center; gap: .6rem;
        padding: .55rem .875rem; cursor: pointer;
        transition: background .15s; border-bottom: 1px solid #f1f5f9;
    }
    .student-reset-row:hover { background: #f8fafc; }
    .student-reset-row input[type="radio"] { flex-shrink: 0; accent-color: #6366f1; }
    .srr-name { flex: 1; font-size: .875rem; color: #1e293b; font-weight: 500; }
    .srr-badge {
        font-size: .65rem; font-weight: 700; text-transform: uppercase;
        background: #e0e7ff; color: #4f46e5; padding: .15rem .4rem;
        border-radius: 4px; letter-spacing: .04em;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
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

/** Cambia de pestaña en el modal de estudiantes */
function switchStudentTab(tabId, btn) {
    ['tabCreate', 'tabReset'].forEach(id => {
        document.getElementById(id).style.display = id === tabId ? 'block' : 'none';
    });
    document.querySelectorAll('.stab').forEach(b => b.classList.remove('stab--active'));
    btn.classList.add('stab--active');
}

/** Filtra la lista de estudiantes en el tab de reset */
function filterStudentsReset(q) {
    const term = q.toLowerCase();
    document.querySelectorAll('.student-reset-row').forEach(row => {
        const name = row.querySelector('.srr-name').textContent.toLowerCase();
        row.style.display = name.includes(term) ? '' : 'none';
    });
}

/** Copia la contraseña temporal al portapapeles */
function copyResetPass(el) {
    const text = el.textContent.trim();
    navigator.clipboard.writeText(text).then(() => {
        const orig = el.textContent;
        el.textContent = '✅ Copiado';
        setTimeout(() => el.textContent = orig, 1800);
    }).catch(() => {
        // Fallback
        const ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        ta.remove();
        el.textContent = '✅ Copiado';
        setTimeout(() => el.textContent = text, 1800);
    });
}

window.onclick = function(event) {
    document.querySelectorAll('.modal').forEach(modal => {
        if (event.target === modal) modal.classList.remove('show');
    });
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\classroomclash\resources\views/dashboard/docente.blade.php ENDPATH**/ ?>