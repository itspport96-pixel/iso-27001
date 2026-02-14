-- Migración: Calendario de Auditorías y Versionado
-- Fecha: 2026-02-14

-- 1. Tabla de auditorías programadas
CREATE TABLE IF NOT EXISTS auditorias_programadas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    tipo ENUM('interna', 'externa', 'seguimiento', 'certificacion') NOT NULL DEFAULT 'interna',
    auditor_responsable VARCHAR(255),
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE,
    estado ENUM('programada', 'en_proceso', 'completada', 'cancelada') NOT NULL DEFAULT 'programada',
    controles_alcance TEXT COMMENT 'IDs de controles separados por coma',
    hallazgos TEXT,
    conclusiones TEXT,
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_empresa (empresa_id),
    INDEX idx_fecha (fecha_inicio),
    INDEX idx_estado (estado),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla de versiones de evidencias
CREATE TABLE IF NOT EXISTS evidencia_versiones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evidencia_id INT UNSIGNED NOT NULL,
    version INT NOT NULL DEFAULT 1,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(500) NOT NULL,
    tipo_mime VARCHAR(100),
    tamano INT UNSIGNED,
    hash_sha256 VARCHAR(64),
    comentarios TEXT,
    subido_por INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_evidencia (evidencia_id),
    INDEX idx_version (version),
    FOREIGN KEY (evidencia_id) REFERENCES evidencias(id) ON DELETE CASCADE,
    FOREIGN KEY (subido_por) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Agregar campo de versión a evidencias
ALTER TABLE evidencias 
ADD COLUMN IF NOT EXISTS version_actual INT NOT NULL DEFAULT 1 AFTER hash_sha256;
