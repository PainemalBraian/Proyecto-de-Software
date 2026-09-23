-- ==============================================================================
-- Script de Creación / Actualización de Base de Datos y Tablas
-- Proyecto: Sprint 2 — ABM Usuarios y Roles con Login y Registro
-- Motor: MySQL / MariaDB (Compatible con WAMP / XAMPP / phpMyAdmin)
-- ==============================================================================

-- Creación de la base de datos con soporte para caracteres en español
CREATE DATABASE IF NOT EXISTS `sprint2` 
  DEFAULT CHARACTER SET utf8mb4 
  COLLATE utf8mb4_spanish_ci;

USE `sprint2`;

-- ------------------------------------------------------------------------------
-- 1. Creación de la tabla de roles (`role`)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `role` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ------------------------------------------------------------------------------
-- 2. Creación de la tabla de usuarios (`user`)
-- Relación 1 a N: user.role es FOREIGN KEY hacia role.id
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NULL,
  `role` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_user_role` FOREIGN KEY (`role`) 
    REFERENCES `role` (`id`) 
    ON UPDATE CASCADE 
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ------------------------------------------------------------------------------
-- 3. Inserción de Roles Iniciales de Ejemplo
-- ------------------------------------------------------------------------------
INSERT INTO `role` (`id`, `name`, `description`) VALUES
  (1, 'Administrador', 'Control total del sistema, gestión de usuarios y roles'),
  (2, 'Editor', 'Permisos de edición y gestión de contenidos'),
  (3, 'Invitado', 'Acceso de solo lectura y visualización básica')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`);

-- ------------------------------------------------------------------------------
-- 4. Inserción de Usuario Administrador Inicial
-- Usuario: admin / Clave: admin123 (hash BCRYPT)
-- ------------------------------------------------------------------------------
INSERT INTO `user` (`id`, `username`, `email`, `name`, `last_name`, `password`, `role`) VALUES
  (1, 'admin', 'admin@sistema.local', 'Administrador', 'Principal', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1)
ON DUPLICATE KEY UPDATE 
  `name` = VALUES(`name`), 
  `last_name` = VALUES(`last_name`), 
  `password` = IF(`password` IS NULL OR `password` = '', VALUES(`password`), `password`);
