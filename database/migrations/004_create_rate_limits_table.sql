-- Tabla para Rate Limiting persistente
-- Almacena intentos de rate limiting por usuario/IP/acción

CREATE TABLE IF NOT EXISTS rate_limits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rate_key VARCHAR(255) NOT NULL,
    attempts INT UNSIGNED NOT NULL DEFAULT 1,
    last_attempt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_rate_key (rate_key),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Limpiar registros expirados (ejecutar periódicamente con cron)
-- DELETE FROM rate_limits WHERE expires_at < NOW();
