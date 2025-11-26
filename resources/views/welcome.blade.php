<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classroom Clash - Transforma tu Aula</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    <link rel="stylesheet" href="{{ asset('css/landing-modals.css') }}">
</head>
<body>
    <div class="bg-gradient-mesh"></div>
    <div class="hero-bg"></div>

    <nav class="navbar">
        <a href="/" class="logo">Classroom Clash</a>
        <div class="nav-links">
            <a href="javascript:void(0)" onclick="openModal('loginModal')" class="btn btn-ghost">Iniciar Sesión</a>
            <a href="javascript:void(0)" onclick="openModal('registerModal')" class="btn btn-primary">Empezar Ahora</a>
        </div>
    </nav>

    <header class="hero">
        <div class="hero-badge">✨ Nueva Experiencia Educativa</div>
        <h1>Transforma tu Aula en una <br><span>Arena Competitiva</span></h1>
        <p>
            Classroom Clash convierte la participación en un juego dinámico.
        </p>
        <div class="cta-group">
            <a href="javascript:void(0)" onclick="openModal('registerModal')" class="btn btn-primary btn-lg">Crea tu Primer Desafío Gratis</a>
        </div>
    </header>

    <section class="features">
        <div class="section-header">
            <h2>Características Principales:</h2>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🖥️</div>
                <h3>Pizarra Interactiva</h3>
                <p>Gestiona a tus estudiantes en tiempo real. Asigna puntos, controla cronómetros y pausa la participación con un solo clic.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🏆</div>
                <h3>Clasificación de Podio</h3>
                <p>Una tabla de líderes dinámica con coronas de oro, plata y bronce para motivar a los 3 primeros puestos.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🎰</div>
                <h3>Ruleta Animada</h3>
                <p>Selecciona estudiantes al azar con una ruleta visual y funcional. Asegura una participación justa y recompensa con puntos al instante.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">👥</div>
                <h3>Modo Individual y Equipos</h3>
                <p>Crea equipos aleatorios con un clic. La puntuación y el tiempo se gestionan tanto para individuos como para equipos.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">⏱️</div>
                <h3>Control Total del Tiempo</h3>
                <p>Un temporizador central y cronómetros individuales que puedes pausar, reanudar y ajustar manualmente.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Exporta tus Resultados</h3>
                <p>Al finalizar un desafío, descarga un reporte en formato CSV con los puntos y tiempos de todos los participantes para tus registros.</p>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; {{ date('Y') }} Classroom Clash. Todos los derechos reservados.</p>
    </footer>

    <div id="loginModal" class="modal-overlay">
        <div class="modal-container">
            <button class="modal-close" onclick="closeModal('loginModal')">&times;</button>
            <div class="modal-header">
                <h2>Bienvenido de nuevo</h2>
                <p>Ingresa a tu cuenta para continuar</p>
            </div>
            
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="login_email">Email</label>
                    <input type="email" id="login_email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="login_password">Contraseña</label>
                    <input type="password" id="login_password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-check">
                    <input type="checkbox" id="remember" name="remember" class="form-check-input" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember" class="form-check-label">Recordarme</label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
            </form>

            <div class="auth-switch">
                ¿No tienes cuenta? <a onclick="switchModal('loginModal', 'registerModal')">Regístrate aquí</a>
            </div>
        </div>
    </div>

    <div id="registerModal" class="modal-overlay">
        <div class="modal-container">
            <button class="modal-close" onclick="closeModal('registerModal')">&times;</button>
            <div class="modal-header">
                <h2>Crea tu cuenta</h2>
                <p>Únete a Classroom Clash hoy mismo</p>
            </div>

            <form action="{{ route('register') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="register_name">Nombre completo</label>
                    <input type="text" id="register_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="register_email">Email</label>
                    <input type="email" id="register_email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="register_password">Contraseña</label>
                    <input type="password" id="register_password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    <small style="color: var(--text-muted); font-size: 0.85rem;">Mínimo 8 caracteres</small>
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmar contraseña</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="role">Tipo de cuenta</label>
                    <select id="role" name="role" class="form-control @error('role') is-invalid @enderror" required>
                        <option value="">Selecciona tu rol</option>
                        <option value="docente" {{ old('role') == 'docente' ? 'selected' : '' }}>Docente</option>
                        <option value="estudiante" {{ old('role') == 'estudiante' ? 'selected' : '' }}>Estudiante</option>
                    </select>
                    @error('role')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary btn-block">Registrarse</button>
            </form>

            <div class="auth-switch">
                ¿Ya tienes cuenta? <a onclick="switchModal('registerModal', 'loginModal')">Inicia sesión aquí</a>
            </div>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'flex';
            modal.offsetHeight;
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }, 300);
        }

        function switchModal(closeId, openId) {
            closeModal(closeId);
            setTimeout(() => {
                openModal(openId);
            }, 300);
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                closeModal(event.target.id);
            }
        }

        @if($errors->any())
            @if($errors->has('email') || $errors->has('password'))
                 @if(old('name'))
                    openModal('registerModal');
                 @else
                    openModal('loginModal');
                 @endif
            @else
                openModal('registerModal');
            @endif
        @endif
    </script>
</body>
</html>
