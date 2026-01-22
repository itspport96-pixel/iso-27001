<?php
declare(strict_types=1);

namespace App\Core;

final class Response
{
    private int $status = 200;
    private array $headers = [];

    public function status(int $code): self
    {
        $this->status = $code;
        return $this;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function secureHeaders(): self
    {
        $cfg = require __DIR__ . '/../../config/security.php';
        foreach ($cfg['headers'] as $k => $v) {
            $this->headers[$k] = $v;
        }
        return $this;
    }

    public function json(mixed $data): void
    {
        $this->header('Content-Type', 'application/json; charset=utf-8');
        $this->sendHeaders();
        echo json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function redirect(string $url): void
    {
        $this->status(302);
        $this->header('Location', $url);
        $this->sendHeaders();
        exit;
    }

    public function view(string $path, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $this->header('Content-Type', 'text/html; charset=utf-8');
        $this->sendHeaders();
        require __DIR__ . '/../../src/Views/' . $path . '.php';
        exit;
    }

    public function text(string $content): void
    {
        $this->header('Content-Type', 'text/plain; charset=utf-8');
        $this->sendHeaders();
        echo $content;
        exit;
    }

    private function sendHeaders(): void
    {
        if (headers_sent()) {
            return;
        }
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
    }
}
