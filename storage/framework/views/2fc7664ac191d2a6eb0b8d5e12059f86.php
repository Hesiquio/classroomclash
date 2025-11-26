

<?php $__env->startSection('title', 'Registro - Classroom Clash'); ?>

<?php $__env->startSection('content'); ?>
<div class="auth-container">
    <div class="auth-card">
        <h1>Registro</h1>
        <p class="text-muted">Crea tu cuenta en Classroom Clash</p>

        <form action="<?php echo e(route('register')); ?>" method="POST">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label for="name">Nombre completo</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo e(old('name')); ?>" required autofocus>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo e(old('email')); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" required>
                <small class="form-text">Mínimo 8 caracteres</small>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmar contraseña</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="role">Tipo de cuenta</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="">Selecciona tu rol</option>
                    <option value="docente" <?php echo e(old('role') == 'docente' ? 'selected' : ''); ?>>Docente</option>
                    <option value="estudiante" <?php echo e(old('role') == 'estudiante' ? 'selected' : ''); ?>>Estudiante</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Registrarse</button>
        </form>

        <p class="text-center mt-3">
            ¿Ya tienes cuenta? <a href="<?php echo e(route('login')); ?>">Inicia sesión aquí</a>
        </p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\classroomclash\resources\views/auth/register.blade.php ENDPATH**/ ?>