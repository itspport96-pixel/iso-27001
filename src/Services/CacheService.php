<?php

namespace App\Services;

class CacheService
{
    private string $cachePath;

    public function __construct()
    {
        $this->cachePath = $_ENV['CACHE_PATH'] ?? '/var/www/html/storage/cache';
    }

    public function get(string $key, $default = null)
    {
        $filename = $this->getFilename($key);

        if (!file_exists($filename)) {
            return $default;
        }

        $raw = file_get_contents($filename);

        if ($raw === false) {
            return $default;
        }

        $data = json_decode($raw, true);

        if ($data === null || !isset($data['expires_at']) || !isset($data['value'])) {
            $this->forget($key);
            return $default;
        }

        if ($data['expires_at'] < time()) {
            $this->forget($key);
            return $default;
        }

        return $data['value'];
    }

    public function put(string $key, $value, int $ttl = 3600): bool
    {
        $filename = $this->getFilename($key);

        $data = [
            'value' => $value,
            'expires_at' => time() + $ttl
        ];

        return file_put_contents($filename, json_encode($data), LOCK_EX) !== false;
    }

    public function has(string $key): bool
    {
        $filename = $this->getFilename($key);

        if (!file_exists($filename)) {
            return false;
        }

        $raw = file_get_contents($filename);

        if ($raw === false) {
            return false;
        }

        $data = json_decode($raw, true);

        if ($data === null || !isset($data['expires_at'])) {
            $this->forget($key);
            return false;
        }

        if ($data['expires_at'] < time()) {
            $this->forget($key);
            return false;
        }

        return true;
    }

    public function forget(string $key): bool
    {
        $filename = $this->getFilename($key);

        if (file_exists($filename)) {
            return unlink($filename);
        }

        return false;
    }

    public function flush(): void
    {
        $files = glob($this->cachePath . '/*.cache');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    private function getFilename(string $key): string
    {
        return $this->cachePath . '/' . md5($key) . '.cache';
    }

    public function remember(string $key, int $ttl, callable $callback)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $callback();
        $this->put($key, $value, $ttl);

        return $value;
    }
}
