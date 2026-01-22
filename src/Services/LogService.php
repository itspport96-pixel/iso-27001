<?php

namespace App\Services;

class LogService
{
    private string $logPath;
    private string $level;

    public function __construct()
    {
        $this->logPath = $_ENV['LOG_PATH'] ?? '/var/www/html/storage/logs';
        $this->level = $_ENV['LOG_LEVEL'] ?? 'debug';
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $levels = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3, 'critical' => 4];
        
        if ($levels[$level] < $levels[$this->level]) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextString = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logMessage = "[$timestamp] [$level] $message $contextString" . PHP_EOL;

        $filename = $this->logPath . '/' . date('Y-m-d') . '.log';
        file_put_contents($filename, $logMessage, FILE_APPEND | LOCK_EX);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    public function security(string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextString = json_encode($context, JSON_UNESCAPED_UNICODE);
        $logMessage = "[$timestamp] [SECURITY] $message $contextString" . PHP_EOL;

        $filename = $this->logPath . '/security.log';
        file_put_contents($filename, $logMessage, FILE_APPEND | LOCK_EX);
    }
}
