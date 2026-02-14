-- Migración: Políticas de Contraseñas
-- Fecha: 2026-02-14

-- 1. Agregar campo para tracking de última actualización de password
ALTER TABLE usuarios 
ADD COLUMN password_updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER debe_cambiar_password;

-- 2. Crear tabla de historial de contraseñas
CREATE TABLE IF NOT EXISTS password_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario_id),
    INDEX idx_created (created_at),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Insertar contraseñas actuales en el historial
INSERT INTO password_history (usuario_id, password_hash, created_at)
SELECT id, password_hash, COALESCE(updated_at, created_at) FROM usuarios WHERE deleted_at IS NULL;

-- 4. Actualizar password_updated_at con la fecha actual para usuarios existentes
UPDATE usuarios SET password_updated_at = COALESCE(updated_at, created_at) WHERE password_updated_at IS NULL;
