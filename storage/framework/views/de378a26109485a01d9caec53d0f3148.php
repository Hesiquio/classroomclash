

<?php $__env->startSection('title', 'Dashboard Estudiante - Classroom Clash'); ?>

<?php $__env->startSection('content'); ?>
<div class="dashboard-header">
    <h1>Unirse a un Desafío</h1>
</div>

<div class="join-challenge-container">
    <div class="join-challenge-card">
        <h2>Ingresa el código del desafío</h2>
        <p class="text-muted">Tu docente te proporcionará un código de 6 caracteres</p>

        <form action="<?php echo e(route('challenge.join')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label for="join_code">Código de acceso</label>
                <input
                    type="text"
                    id="join_code"
                    name="join_code"
                    class="form-control form-control-lg text-center"
                    placeholder="Ej: ABC123"
                    maxlength="6"
                    style="text-transform: uppercase;"
                    required
                    autofocus>
                <small class="form-text">Ingresa el código exactamente como te lo proporcionó tu docente</small>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Unirse al Desafío</button>
        </form>
    </div>

    <div class="info-box">
        <h3>¿Cómo funciona?</h3>
        <ol>
            <li>Tu docente creará un desafío y te dará el código de acceso</li>
            <li>Ingresa el código en el formulario de arriba</li>
            <li>Podrás ver la pizarra del desafío y tu puntuación en tiempo real</li>
            <li>Participa activamente en clase para ganar puntos</li>
        </ol>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.getElementById('join_code').addEventListener('input', function(e) {
    this.value = this.value.toUpperCase();
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\classroomclash\resources\views/dashboard/estudiante.blade.php ENDPATH**/ ?>