-- Classroom Clash - Base de datos completa
-- Sistema de gestión de participación estudiantil

-- Eliminar tablas si existen (opcional, comentar si no deseas eliminar datos existentes)
DROP TABLE IF EXISTS `participants`;
DROP TABLE IF EXISTS `challenges`;
DROP TABLE IF EXISTS `users`;

-- Tabla de usuarios (docentes y estudiantes)
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('docente','estudiante') NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_index` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de desafíos
CREATE TABLE `challenges` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `teacher_id` bigint(20) UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `join_code` varchar(6) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `challenges_join_code_unique` (`join_code`),
  KEY `challenges_teacher_id_foreign` (`teacher_id`),
  KEY `challenges_is_active_index` (`is_active`),
  CONSTRAINT `challenges_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de participantes
CREATE TABLE `participants` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `challenge_id` bigint(20) UNSIGNED NOT NULL,
  `points` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `participants_user_id_challenge_id_unique` (`user_id`,`challenge_id`),
  KEY `participants_challenge_id_foreign` (`challenge_id`),
  KEY `participants_points_index` (`points`),
  CONSTRAINT `participants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `participants_challenge_id_foreign` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de ejemplo (opcional - puedes comentar esta sección si no deseas datos de prueba)

-- Insertar un docente de prueba
-- Contraseña: password123
INSERT INTO `users` (`name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
('Profesor Demo', 'profesor@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'docente', NOW(), NOW());

-- Insertar estudiantes de prueba
-- Contraseña: password123
INSERT INTO `users` (`name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
('Juan Pérez', 'juan@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'estudiante', NOW(), NOW()),
('María García', 'maria@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'estudiante', NOW(), NOW()),
('Carlos López', 'carlos@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'estudiante', NOW(), NOW());

-- Insertar un desafío de prueba
INSERT INTO `challenges` (`name`, `teacher_id`, `is_active`, `join_code`, `created_at`, `updated_at`) VALUES
('Clase de Matemáticas', 1, 1, 'ABC123', NOW(), NOW());

-- Insertar participantes de prueba
INSERT INTO `participants` (`user_id`, `challenge_id`, `points`, `created_at`, `updated_at`) VALUES
(2, 1, 5, NOW(), NOW()),
(3, 1, 8, NOW(), NOW()),
(4, 1, 3, NOW(), NOW());

-- Fin del script
