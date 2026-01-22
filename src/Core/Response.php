<?php

namespace App\Core;

class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private string $content = '';

    public function __construct()
    {
        $this->setSecurityHeaders();
    }

    private function setSecurityHeaders(): void
    {
        $this->headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Content-Type' => 'text/html; charset=UTF-8'
        ];
    }

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function setHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function json(array $data, int $statusCode = 200): void
    {
        $this->statusCode = $statusCode;
        $this->headers['Content-Type'] = 'application/json';
        $this->content = json_encode($data, JSON_UNESCAPED_UNICODE);
        $this->send();
    }

    public function html(string $content, int $statusCode = 200): void
    {
        $this->statusCode = $statusCode;
        $this->content = $content;
        $this->send();
    }

    public function redirect(string $url, int $statusCode = 302): void
    {
        $this->statusCode = $statusCode;
        $this->setHeader('Location', $url);
        $this->send();
        exit;
    }

    private function send(): void
    {
        http_response_code($this->statusCode);
        
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }
        
        echo $this->content;
    }

    public function notFound(string $message = 'Not Found'): void
    {
        $this->html("<h1>404 - $message</h1>", 404);
    }

    public function error(string $message = 'Internal Server Error', int $code = 500): void
    {
        $this->html("<h1>$code - $message</h1>", $code);
    }
}
