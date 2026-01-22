USE iso_platform;

CREATE TABLE rate_limits (
    cache_key   VARCHAR(191) PRIMARY KEY,
    attempts    INT UNSIGNED NOT NULL DEFAULT 1,
    expires_at  DATETIME NOT NULL,
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB;
