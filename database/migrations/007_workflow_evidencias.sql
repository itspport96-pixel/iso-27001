-- Migración: Workflow de Evidencias Mejorado
-- Fecha: 2026-02-14

-- 1. Crear tabla de historial de estados de evidencias
CREATE TABLE IF NOT EXISTS evidencia_historial (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evidencia_id INT UNSIGNED NOT NULL,
    estado_anterior VARCHAR(50),
    estado_nuevo VARCHAR(50) NOT NULL,
    comentarios TEXT,
    usuario_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_evidencia (evidencia_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_created (created_at),
    FOREIGN KEY (evidencia_id) REFERENCES evidencias(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Agregar campos de workflow a evidencias si no existen
ALTER TABLE evidencias 
ADD COLUMN IF NOT EXISTS motivo_rechazo TEXT AFTER comentarios_validacion,
ADD COLUMN IF NOT EXISTS notificado_rechazo TINYINT(1) DEFAULT 0 AFTER motivo_rechazo;
