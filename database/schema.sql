-- ==========================================================
-- ISO 27001 COMPLIANCE PLATFORM v2.0
-- Esquema en 3FN – MySQL 8.0+
-- ==========================================================

CREATE DATABASE IF NOT EXISTS iso_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE iso_platform;

-- 1. TABLAS MAESTRAS (sin empresa_id)
CREATE TABLE controles_dominio (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(10) UNIQUE NOT NULL,
    nombre VARCHAR(150) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE controles (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    codigo      VARCHAR(10) UNIQUE NOT NULL,
    nombre      VARCHAR(200) NOT NULL,
    descripcion TEXT,
    dominio_id  INT NOT NULL,
    FOREIGN KEY (dominio_id) REFERENCES controles_dominio(id) ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE requerimientos_base (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    numero        INT UNIQUE NOT NULL,
    identificador VARCHAR(50) UNIQUE NOT NULL,
    descripcion   TEXT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE requerimientos_controles (
    id                      INT AUTO_INCREMENT PRIMARY KEY,
    requerimiento_base_id   INT NOT NULL,
    control_id              INT NOT NULL,
    UNIQUE(requerimiento_base_id, control_id),
    FOREIGN KEY (requerimiento_base_id) REFERENCES requerimientos_base(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (control_id)              REFERENCES controles(id)           ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 2. TABLAS MULTI-TENANT (con empresa_id)
CREATE TABLE empresas (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    ruc        VARCHAR(20) UNIQUE NOT NULL,
    razon_social VARCHAR(200) NOT NULL,
    contacto   JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE usuarios (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id  INT NOT NULL,
    nombre      VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol         ENUM('super_admin','admin_empresa','auditor','consultor') NOT NULL,
    estado      ENUM('activo','inactivo') DEFAULT 'activo',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE(empresa_id, email),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE soa_entries (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id    INT NOT NULL,
    control_id    INT NOT NULL,
    aplicable     BOOLEAN DEFAULT 1,
    estado        ENUM('no_implementado','parcial','implementado') DEFAULT 'no_implementado',
    justificacion TEXT,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE(empresa_id, control_id),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (control_id) REFERENCES controles(id) ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE gap_items (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    soa_id           INT NOT NULL,
    brecha           TEXT NOT NULL,
    objetivo         TEXT NOT NULL,
    prioridad        ENUM('alta','media','baja') DEFAULT 'media',
    avance           TINYINT GENERATED ALWAYS AS (0) STORED, -- trigger actualizará
    estado_gap       ENUM('activo','cerrado','eliminado') DEFAULT 'activo',
    fecha_estimada   DATE,
    fecha_real_cierre DATE,
    created_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (soa_id) REFERENCES soa_entries(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE acciones (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    gap_id           INT NOT NULL,
    descripcion      TEXT NOT NULL,
    responsable      VARCHAR(100),
    fecha_compromiso DATE,
    estado_accion    ENUM('pendiente','en_progreso','completada','eliminada') DEFAULT 'pendiente',
    created_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (gap_id) REFERENCES gap_items(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE evidencias (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id        INT NOT NULL,
    control_id        INT NOT NULL,
    tipo              VARCHAR(50),
    descripcion       TEXT,
    archivo           VARCHAR(500) NOT NULL,
    hash_archivo      CHAR(64) NOT NULL,
    estado_validacion ENUM('pendiente','aprobada','rechazada') DEFAULT 'pendiente',
    validado_por      INT,
    created_at        DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (control_id) REFERENCES controles(id) ON UPDATE CASCADE,
    FOREIGN KEY (validado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE empresa_requerimientos (
    id                      INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id              INT NOT NULL,
    requerimiento_base_id   INT NOT NULL,
    estado                  ENUM('pendiente','en_proceso','completado') DEFAULT 'pendiente',
    fecha_entrega           DATETIME,
    observaciones           TEXT,
    created_at              DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at              DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE(empresa_id, requerimiento_base_id),
    FOREIGN KEY (empresa_id)            REFERENCES empresas(id)            ON DELETE CASCADE,
    FOREIGN KEY (requerimiento_base_id) REFERENCES requerimientos_base(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id            BIGINT AUTO_INCREMENT PRIMARY KEY,
    empresa_id    INT,
    usuario_id    INT,
    tabla         VARCHAR(64) NOT NULL,
    accion        ENUM('INSERT','UPDATE','DELETE') NOT NULL,
    datos_previos JSON,
    datos_nuevos  JSON,
    ip            VARCHAR(45),
    user_agent    TEXT,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 3. ÍNDICES DE RENDIMIENTO
CREATE INDEX idx_soa_empresa        ON soa_entries(empresa_id);
CREATE INDEX idx_soa_control        ON soa_entries(control_id);
CREATE INDEX idx_gap_soa            ON gap_items(soa_id);
CREATE INDEX idx_acciones_gap       ON acciones(gap_id);
CREATE INDEX idx_evidencias_empresa ON evidencias(empresa_id);
CREATE INDEX idx_evidencias_control ON evidencias(control_id);
CREATE INDEX idx_emp_req_empresa    ON empresa_requerimientos(empresa_id);
CREATE INDEX idx_audit_empresa      ON audit_logs(empresa_id);
CREATE INDEX idx_audit_usuario      ON audit_logs(usuario_id);
CREATE INDEX idx_audit_creado       ON audit_logs(created_at);

-- 4. DATOS MAESTROS INICIALES
INSERT INTO controles_dominio (codigo,nombre) VALUES
('5','Organizacionales'),('6','Personas'),('7','Físicos'),('8','Tecnológicos');

-- 5. TRIGGERS PARA AUDITORÍA (ejemplo genérico)
DELIMITER //
CREATE TRIGGER trg_soa_update
AFTER UPDATE ON soa_entries
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (empresa_id,usuario_id,tabla,accion,datos_previos,datos_nuevos,ip,user_agent)
    VALUES (OLD.empresa_id,@uid,'soa_entries','UPDATE',JSON_OBJECT('estado',OLD.estado,'aplicable',OLD.aplicable),JSON_OBJECT('estado',NEW.estado,'aplicable',NEW.aplicable),@ip,@ua);
END//
DELIMITER ;
