<?php

namespace App\Core;

class Session
{
    private static bool $started = false;
    private array $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/session.php';
        $this->start();
    }

    private function start(): void
    {
        if (self::$started) {
            return;
        }

        ini_set('session.save_path', $this->config['path']);
        ini_set('session.gc_probability', $this->config['gc_probability']);
        ini_set('session.gc_divisor', $this->config['gc_divisor']);
        ini_set('session.gc_maxlifetime', $this->config['gc_maxlifetime']);

        session_set_cookie_params([
            'lifetime' => $this->config['cookie']['lifetime'],
            'path' => $this->config['cookie']['path'],
            'domain' => $this->config['cookie']['domain'],
            'secure' => $this->config['cookie']['secure'],
            'httponly' => $this->config['cookie']['httponly'],
            'samesite' => $this->config['cookie']['samesite']
        ]);

        session_name($this->config['name']);
        session_start();
        self::$started = true;

        $this->validateFingerprint();
    }

    private function validateFingerprint(): void
    {
        $fingerprint = $this->generateFingerprint();
        
        if (!$this->has('_fingerprint')) {
            $this->set('_fingerprint', $fingerprint);
        } elseif ($this->get('_fingerprint') !== $fingerprint) {
            $this->destroy();
            session_start();
            $this->set('_fingerprint', $fingerprint);
        }
    }

    private function generateFingerprint(): string
    {
        $request = new Request();
        return hash('sha256', $request->userAgent() . $request->ip());
    }

    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function destroy(): void
    {
        session_destroy();
        self::$started = false;
    }

    public function regenerate(): void
    {
        session_regenerate_id(true);
        $this->set('_fingerprint', $this->generateFingerprint());
    }

    public function flash(string $key, $value): void
    {
        $this->set('_flash_' . $key, $value);
    }

    public function getFlash(string $key, $default = null)
    {
        $value = $this->get('_flash_' . $key, $default);
        $this->remove('_flash_' . $key);
        return $value;
    }
}
