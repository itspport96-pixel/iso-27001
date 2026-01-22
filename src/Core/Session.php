<?php
declare(strict_types=1);

namespace App\Core;

final class Session
{
    private static bool $started = false;

    public static function start(bool $allowRegenerate = true): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $cfg = require __DIR__ . '/../../config/security.php';
        ini_set('session.gc_maxlifetime', (string)($cfg['session_lifetime'] * 60));
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_cookies', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.save_path', $_ENV['SESSION_PATH'] ?? __DIR__ . '/../../storage/sessions');
        session_name('ISOSESSID');
        session_start();
        self::$started = true;
        if ($allowRegenerate) {
            self::regenerateIfNeeded();
        }
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }

    private static function regenerateIfNeeded(): void
    {
        $ttl = 60 * 30; // 30 min
        if (!isset($_SESSION['last_regeneration']) || $_SESSION['last_regeneration'] < time() - $ttl) {
            self::regenerate();
        }
    }

    public static function put(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function pull(string $key, mixed $default = null): mixed
    {
        self::start();
        $value = $_SESSION[$key] ?? $default;
        unset($_SESSION[$key]);
        return $value;
    }

    public static function forget(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function flush(): void
    {
        self::start();
        session_unset();
        session_destroy();
        self::$started = false;
    }

    public static function token(): string
    {
        self::start();
        if (!isset($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_token'];
    }

    public static function fingerprint(string $ip, string $ua): bool
    {
        self::start();
        $hash = md5($ip . '|' . $ua);
        if (!isset($_SESSION['_fingerprint'])) {
            $_SESSION['_fingerprint'] = $hash;
            return true;
        }
        return hash_equals($_SESSION['_fingerprint'], $hash);
    }
}
