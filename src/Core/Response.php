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
        $isProduction = ($_ENV['APP_ENV'] ?? 'development') === 'production';
        
        $this->headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Powered-By' => '',
            'Server' => '',
        ];
        
        // Content Security Policy actualizado para Tailwind CDN y Google Fonts
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com",
            "style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://fonts.googleapis.com https://cdnjs.cloudflare.com",
            "img-src 'self' data: https:",
            "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com",
            "connect-src 'self'",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'"
        ];
        
        $this->headers['Content-Security-Policy'] = implode('; ', $csp);
        
        // HSTS solo en producción con HTTPS
        if ($isProduction && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $this->headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';
        }
        
        // Permissions Policy
        $this->headers['Permissions-Policy'] = 'geolocation=(), microphone=(), camera=()';
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
            if ($value !== '') {
                header("$key: $value");
            } else {
                header_remove($key);
            }
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
