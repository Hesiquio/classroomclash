

<?php $__env->startSection('title', 'Dashboard Docente - Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="dashboard-header">
    <h1>Mis Desafíos</h1>
    <div class="header-actions" style="display: flex; align-items: center; gap: 0.5rem;">
        <a href="<?php echo e(route('dashboard.archived')); ?>" class="btn btn-secondary" style="line-height: 1.5;">
            📂 Archivados
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
    /* Tooltip positioning - show below buttons */
    .challenge-footer .btn,
    .challenge-footer a.btn {
        position: relative;
    }
    
    .challenge-footer .btn::after,
    .challenge-footer a.btn::after {
        content: attr(title);
        position: absolute;
        bottom: -30px;
        left: 50%;
        transform: translateX(-50%);
        background-color: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s;
        z-index: 1000;
    }
    
    .challenge-footer .btn:hover::after,
    .challenge-footer a.btn:hover::after {
        opacity: 1;
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

window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.classList.remove('show');
        }
    });
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\classroomclash\resources\views/dashboard/docente.blade.php ENDPATH**/ ?>