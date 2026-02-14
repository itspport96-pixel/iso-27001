-- Actualizar tabla rate_limits para compatibilidad con RateLimitMiddleware

-- Renombrar cache_key a rate_key
ALTER TABLE rate_limits CHANGE cache_key rate_key VARCHAR(255) NOT NULL;

-- Agregar columna id como PRIMARY KEY
ALTER TABLE rate_limits DROP PRIMARY KEY;
ALTER TABLE rate_limits ADD id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY FIRST;

-- Agregar columna last_attempt
ALTER TABLE rate_limits ADD last_attempt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER attempts;

-- Agregar columna created_at
ALTER TABLE rate_limits ADD created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER expires_at;

-- Modificar expires_at a TIMESTAMP
ALTER TABLE rate_limits MODIFY expires_at TIMESTAMP NOT NULL;

-- Crear índice único en rate_key
ALTER TABLE rate_limits ADD UNIQUE KEY unique_rate_key (rate_key);

-- Recrear índice de expires_at
ALTER TABLE rate_limits ADD INDEX idx_expires_at (expires_at);
