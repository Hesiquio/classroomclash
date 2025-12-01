

<?php $__env->startSection('title', 'Desafíos Archivados - Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="dashboard-header">
    <h1>Desafíos Archivados</h1>
    <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-secondary">
        ← Volver al Dashboard
    </a>
</div>

<?php if($challenges->isEmpty()): ?>
    <div class="empty-state">
        <h2>No tienes desafíos archivados</h2>
        <p>Los desafíos finalizados aparecerán aquí.</p>
    </div>
<?php else: ?>
    <div class="challenges-list">
        <?php $__currentLoopData = $challenges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $challenge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="challenge-list-item">
                <div class="challenge-info">
                    <h3><?php echo e($challenge->name); ?></h3>
                    <span class="meta-info">Creado: <?php echo e($challenge->created_at->format('d/m/Y')); ?></span>
                    <span class="meta-info">Participantes: <?php echo e($challenge->participants->count()); ?></span>
                </div>
                <div class="challenge-actions">
                    <form action="<?php echo e(route('challenge.resume', $challenge)); ?>" method="POST" style="display:inline;" onsubmit="return confirm('¿Reactivar este desafío? Volverá a aparecer en el dashboard principal.')">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-sm btn-success" title="Reactivar">
                            ↩️ Reactivar
                        </button>
                    </form>
                    <form action="<?php echo e(route('challenge.destroy', $challenge)); ?>" method="POST" style="display:inline; margin-left:4px;" onsubmit="return confirm('¿Estás seguro de eliminar este desafío permanentemente?')">
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

<?php $__env->startPush('styles'); ?>
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
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\classroomclash\resources\views/dashboard/archived.blade.php ENDPATH**/ ?>