-- ISO 27001 Compliance Platform - Database Schema
-- Version 2.0 - Normalized 3FN

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS empresa_requerimientos;
DROP TABLE IF EXISTS requerimientos_controles;
DROP TABLE IF EXISTS requerimientos_base;
DROP TABLE IF EXISTS evidencias;
DROP TABLE IF EXISTS acciones;
DROP TABLE IF EXISTS gap_items;
DROP TABLE IF EXISTS soa_entries;
DROP TABLE IF EXISTS controles;
DROP TABLE IF EXISTS controles_dominio;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS empresas;
SET FOREIGN_KEY_CHECKS=1;

-- Tabla: empresas
CREATE TABLE empresas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    ruc VARCHAR(20) NOT NULL UNIQUE,
    contacto VARCHAR(255),
    telefono VARCHAR(20),
    email VARCHAR(255),
    direccion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_ruc (ruc),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: usuarios
CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('super_admin', 'admin_empresa', 'auditor', 'consultor') NOT NULL DEFAULT 'consultor',
    estado ENUM('activo', 'inactivo', 'bloqueado') NOT NULL DEFAULT 'activo',
    ultimo_acceso TIMESTAMP NULL,
    intentos_login TINYINT UNSIGNED DEFAULT 0,
    bloqueado_hasta TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY unique_email_empresa (email, empresa_id),
    INDEX idx_empresa (empresa_id),
    INDEX idx_email (email),
    INDEX idx_rol (rol),
    INDEX idx_estado (estado),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: controles_dominio
CREATE TABLE controles_dominio (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL UNIQUE,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: controles
CREATE TABLE controles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dominio_id INT UNSIGNED NOT NULL,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    objetivo TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_dominio (dominio_id),
    INDEX idx_codigo (codigo),
    FOREIGN KEY (dominio_id) REFERENCES controles_dominio(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: soa_entries (Statement of Applicability)
CREATE TABLE soa_entries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NOT NULL,
    control_id INT UNSIGNED NOT NULL,
    aplicable TINYINT(1) NOT NULL DEFAULT 1,
    estado ENUM('no_implementado', 'parcial', 'implementado') NOT NULL DEFAULT 'no_implementado',
    justificacion TEXT,
    responsable VARCHAR(255),
    fecha_evaluacion DATE,
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_empresa_control (empresa_id, control_id),
    INDEX idx_empresa (empresa_id),
    INDEX idx_control (control_id),
    INDEX idx_aplicable (aplicable),
    INDEX idx_estado (estado),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (control_id) REFERENCES controles(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: gap_items
CREATE TABLE gap_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    soa_id INT UNSIGNED NOT NULL,
    empresa_id INT UNSIGNED NOT NULL,
    brecha TEXT NOT NULL,
    impacto ENUM('critico', 'alto', 'medio', 'bajo') NOT NULL,
    prioridad ENUM('alta', 'media', 'baja') NOT NULL,
    avance DECIMAL(5,2) DEFAULT 0.00 CHECK (avance BETWEEN 0 AND 100),
    fecha_objetivo DATE,
    fecha_real_cierre DATE NULL,
    estado_gap ENUM('activo', 'eliminado') NOT NULL DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_soa (soa_id),
    INDEX idx_empresa (empresa_id),
    INDEX idx_prioridad (prioridad),
    INDEX idx_estado (estado_gap),
    FULLTEXT idx_brecha (brecha),
    FOREIGN KEY (soa_id) REFERENCES soa_entries(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: acciones
CREATE TABLE acciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    gap_id INT UNSIGNED NOT NULL,
    descripcion TEXT NOT NULL,
    responsable VARCHAR(255),
    fecha_compromiso DATE NOT NULL,
    fecha_completado DATE NULL,
    estado ENUM('pendiente', 'en_proceso', 'completada', 'vencida') NOT NULL DEFAULT 'pendiente',
    estado_accion ENUM('activo', 'eliminado') NOT NULL DEFAULT 'activo',
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_gap (gap_id),
    INDEX idx_estado (estado),
    INDEX idx_estado_accion (estado_accion),
    INDEX idx_fecha_compromiso (fecha_compromiso),
    FOREIGN KEY (gap_id) REFERENCES gap_items(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: evidencias
CREATE TABLE evidencias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NOT NULL,
    control_id INT UNSIGNED NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(500) NOT NULL,
    tipo_mime VARCHAR(100) NOT NULL,
    tamano INT UNSIGNED NOT NULL,
    hash_sha256 VARCHAR(64) NOT NULL,
    estado_validacion ENUM('pendiente', 'aprobada', 'rechazada') NOT NULL DEFAULT 'pendiente',
    comentarios TEXT,
    subido_por INT UNSIGNED NOT NULL,
    validado_por INT UNSIGNED NULL,
    fecha_validacion TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_empresa (empresa_id),
    INDEX idx_control (control_id),
    INDEX idx_estado (estado_validacion),
    INDEX idx_hash (hash_sha256),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (control_id) REFERENCES controles(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (subido_por) REFERENCES usuarios(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (validado_por) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: requerimientos_base
CREATE TABLE requerimientos_base (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero TINYINT UNSIGNED NOT NULL UNIQUE,
    identificador VARCHAR(50) NOT NULL UNIQUE,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: requerimientos_controles
CREATE TABLE requerimientos_controles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    requerimiento_base_id INT UNSIGNED NOT NULL,
    control_id INT UNSIGNED NOT NULL,
    UNIQUE KEY unique_req_control (requerimiento_base_id, control_id),
    INDEX idx_requerimiento (requerimiento_base_id),
    INDEX idx_control (control_id),
    FOREIGN KEY (requerimiento_base_id) REFERENCES requerimientos_base(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (control_id) REFERENCES controles(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: empresa_requerimientos
CREATE TABLE empresa_requerimientos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NOT NULL,
    requerimiento_id INT UNSIGNED NOT NULL,
    estado ENUM('pendiente', 'en_proceso', 'completado') NOT NULL DEFAULT 'pendiente',
    fecha_inicio DATE NULL,
    fecha_completado DATE NULL,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_empresa_req (empresa_id, requerimiento_id),
    INDEX idx_empresa (empresa_id),
    INDEX idx_requerimiento (requerimiento_id),
    INDEX idx_estado (estado),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (requerimiento_id) REFERENCES requerimientos_base(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: audit_logs
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NULL,
    usuario_id INT UNSIGNED NULL,
    tabla VARCHAR(100) NOT NULL,
    accion ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    registro_id INT UNSIGNED NOT NULL,
    datos_previos JSON NULL,
    datos_nuevos JSON NULL,
    ip VARCHAR(45) NOT NULL,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_empresa (empresa_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_tabla (tabla),
    INDEX idx_accion (accion),
    INDEX idx_fecha (created_at),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
