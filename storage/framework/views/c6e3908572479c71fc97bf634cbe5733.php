

<?php $__env->startSection('title', 'Iniciar Sesión - Classroom Clash'); ?>

<?php $__env->startSection('content'); ?>
<div class="auth-container">
    <div class="auth-card">
        <h1>Iniciar Sesión</h1>
        <p class="text-muted">Accede a tu cuenta de Classroom Clash</p>

        <form action="<?php echo e(route('login')); ?>" method="POST">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo e(old('email')); ?>" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <div class="form-group form-check">
                <input type="checkbox" id="remember" name="remember" class="form-check-input">
                <label for="remember" class="form-check-label">Recordarme</label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
        </form>

        <p class="text-center mt-3">
            ¿No tienes cuenta? <a href="<?php echo e(route('register')); ?>">Regístrate aquí</a>
        </p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\classroomclash\resources\views/auth/login.blade.php ENDPATH**/ ?>