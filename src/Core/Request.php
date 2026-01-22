<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    private array $get;
    private array $post;
    private array $cookies;
    private array $files;
    private array $server;
    private array $headers;

    public function __construct()
    {
        $this->get     = $_GET;
        $this->post    = $_POST;
        $this->cookies = $_COOKIE;
        $this->files   = $_FILES;
        $this->server  = $_SERVER;
        $this->headers = $this->parseHeaders();
    }

    private function parseHeaders(): array
    {
        $headers = [];
        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', substr($key, 5));
                $headers[strtolower($name)] = $value;
            }
        }
        return $headers;
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function uri(): string
    {
        return strtok($this->server['REQUEST_URI'] ?? '/', '?');
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    public function only(array $keys): array
    {
        return array_intersect_key($this->all(), array_flip($keys));
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[strtolower($key)] ?? $default;
    }

    public function ip(): string
    {
        return $this->header('x-forwarded-for', $this->server['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public function userAgent(): string
    {
        return $this->header('user-agent', '');
    }

    public function isAjax(): bool
    {
        return $this->header('x-requested-with') === 'XMLHttpRequest';
    }

    public function validate(array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $rule) {
            $value = $this->input($field);
            if (str_contains($rule, 'required') && empty($value)) {
                $errors[$field][] = "$field is required";
            }
            if (str_contains($rule, 'email') && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field][] = "$field must be a valid email";
            }
            if (preg_match('/max:(\d+)/', $rule, $m) && strlen((string)$value) > (int)$m[1]) {
                $errors[$field][] = "$field must be max {$m[1]} characters";
            }
            if (preg_match('/min:(\d+)/', $rule, $m) && strlen((string)$value) < (int)$m[1]) {
                $errors[$field][] = "$field must be min {$m[1]} characters";
            }
        }
        return $errors;
    }
}
