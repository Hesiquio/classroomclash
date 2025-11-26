# Classroom Clash

Classroom Clash es una aplicación web diseñada para gestionar la participación de estudiantes en el aula de manera dinámica y motivadora. Los docentes pueden crear desafíos y otorgar puntos a los estudiantes por su participación activa en clase.

## Características

- **Autenticación de usuarios** con dos roles: Docente y Estudiante
- **Dashboard para Docentes**: Crear y gestionar desafíos de clase
- **Dashboard para Estudiantes**: Unirse a desafíos mediante códigos de acceso
- **Pizarra de desafío en tiempo real**: Visualización de participantes y puntuaciones
- **Sistema de puntos**: Los docentes pueden otorgar puntos con un solo clic
- **Rankings**: Los participantes se ordenan automáticamente por puntuación
- **Interfaz intuitiva y responsive**: Funciona en dispositivos móviles y escritorio

## Requisitos

- PHP >= 8.1
- Composer
- MySQL >= 5.7 o MariaDB >= 10.3
- Servidor web (Apache, Nginx)

## Instalación en Hostinger

### 1. Subir el proyecto

```bash
# Clona el repositorio en tu máquina local
git clone https://github.com/Hesiquio/classroomclash.git
cd classroomclash
```

Sube los archivos al servidor vía FTP o Git (si tu hosting lo soporta).

### 2. Instalar dependencias

Conéctate a tu servidor por SSH (si está disponible) o usa la terminal de Hostinger:

```bash
composer install --optimize-autoloader --no-dev
```

### 3. Configurar el archivo .env

Copia el archivo `.env.example` a `.env`:

```bash
cp .env.example .env
```

Edita el archivo `.env` con tus credenciales de base de datos:

```env
APP_NAME="Classroom Clash"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://tudominio.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nombre_de_tu_base_de_datos
DB_USERNAME=tu_usuario_mysql
DB_PASSWORD=tu_contraseña_mysql
```

### 4. Generar la clave de aplicación

```bash
php artisan key:generate
```

### 5. Ejecutar las migraciones

```bash
php artisan migrate
```

### 6. Configurar permisos

```bash
chmod -R 775 storage bootstrap/cache
```

### 7. Configurar el Document Root

En el panel de control de Hostinger, configura el **Document Root** de tu dominio para que apunte a la carpeta `public` del proyecto Laravel.

Ejemplo: `/home/usuario/public_html/classroomclash/public`

### 8. Archivo .htaccess (para Apache)

Verifica que el archivo `public/.htaccess` exista. Si no, créalo con este contenido:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

## Uso

### Para Docentes

1. **Registrarse** seleccionando el rol "Docente"
2. **Iniciar sesión** con tus credenciales
3. **Crear un desafío** desde el Dashboard (ejemplo: "Clase de Matemáticas")
4. **Compartir el código** de 6 caracteres con tus estudiantes
5. **Otorgar puntos** haciendo clic en "+1 Punto" para cada participación
6. **Finalizar el desafío** cuando termine la clase

### Para Estudiantes

1. **Registrarse** seleccionando el rol "Estudiante"
2. **Iniciar sesión** con tus credenciales
3. **Ingresar el código** proporcionado por tu docente
4. **Ver tu puntuación** en tiempo real en la pizarra del desafío

## Estructura del Proyecto

```
classroomclash/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AuthController.php
│   │       ├── DashboardController.php
│   │       └── ChallengeController.php
│   └── Models/
│       ├── User.php
│       ├── Challenge.php
│       └── Participant.php
├── database/
│   └── migrations/
│       ├── 2024_01_01_000001_create_users_table.php
│       ├── 2024_01_01_000002_create_challenges_table.php
│       └── 2024_01_01_000003_create_participants_table.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── auth/
│       │   ├── login.blade.php
│       │   └── register.blade.php
│       ├── dashboard/
│       │   ├── docente.blade.php
│       │   └── estudiante.blade.php
│       └── challenge/
│           └── show.blade.php
├── routes/
│   └── web.php
└── public/
    └── css/
        └── app.css
```

## Base de Datos

### Tabla: users
- `id`: Identificador único del usuario
- `name`: Nombre completo
- `email`: Correo electrónico (único)
- `password`: Contraseña hasheada
- `role`: Rol del usuario (docente o estudiante)

### Tabla: challenges
- `id`: Identificador único del desafío
- `name`: Nombre del desafío
- `teacher_id`: ID del docente creador (FK a users)
- `is_active`: Estado del desafío (activo/finalizado)
- `join_code`: Código de 6 caracteres para unirse

### Tabla: participants
- `id`: Identificador único
- `user_id`: ID del estudiante (FK a users)
- `challenge_id`: ID del desafío (FK a challenges)
- `points`: Puntos acumulados

## Seguridad

- Las contraseñas se hashean usando `bcrypt`
- Protección CSRF en todos los formularios
- Validación de datos en el servidor
- Middleware de autenticación para rutas protegidas
- Sentencias preparadas (PDO) para prevenir SQL injection

## Solución de Problemas

### Error 500

- Verifica que `APP_DEBUG=true` en `.env` para ver el error completo
- Revisa que los permisos de `storage` y `bootstrap/cache` sean correctos
- Verifica los logs en `storage/logs/laravel.log`

### Error de base de datos

- Confirma que las credenciales en `.env` sean correctas
- Verifica que la base de datos exista
- Asegúrate de haber ejecutado `php artisan migrate`

### Página en blanco

- Verifica que el Document Root apunte a la carpeta `public`
- Revisa el archivo `.htaccess` en `public/`

## Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Haz un fork del proyecto
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -m 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT.

## Contacto

Para preguntas o sugerencias, por favor abre un issue en el repositorio de GitHub.

---

Desarrollado con ❤️ para mejorar la experiencia educativa en el aula.
