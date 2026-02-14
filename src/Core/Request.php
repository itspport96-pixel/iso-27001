<?php

namespace App\Core;

class Request
{
    private array $get;
    private array $post;
    private array $server;
    private array $files;

    public function __construct()
    {
        $this->get = $this->sanitize($_GET);
        $this->post = $this->sanitize($_POST);
        $this->server = $_SERVER;
        $this->files = $_FILES;
    }

    private function sanitize(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitize($value);
            } else {
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            }
        }
        return $sanitized;
    }

    public function get(string $key, $default = null)
    {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function uri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        return strtok($uri, '?');
    }

    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    public function file(string $key)
    {
        return $this->files[$key] ?? null;
    }
}
