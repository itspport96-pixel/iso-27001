<?php

namespace App\Services;

class FileService
{
    private string $uploadPath;
    private int $maxSize;
    private array $allowedMimes = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain'
    ];

    public function __construct()
    {
        $this->uploadPath = $_ENV['UPLOAD_PATH'] ?? '/var/www/html/public/uploads';
        $this->maxSize = (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 10485760);
    }

    public function upload(array $file, int $empresaId): ?array
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['error' => 'Archivo inválido'];
        }

        if ($file['size'] > $this->maxSize) {
            return ['error' => 'Archivo excede el tamaño máximo'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->allowedMimes)) {
            return ['error' => 'Tipo de archivo no permitido'];
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $hash = hash_file('sha256', $file['tmp_name']);
        $filename = $hash . '.' . $extension;

        $year = date('Y');
        $month = date('m');
        $directory = $this->uploadPath . '/' . $empresaId . '/' . $year . '/' . $month;

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $destination = $directory . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return [
                'success' => true,
                'filename' => $filename,
                'path' => $empresaId . '/' . $year . '/' . $month . '/' . $filename,
                'size' => $file['size'],
                'mime' => $mimeType,
                'hash' => $hash
            ];
        }

        return ['error' => 'Error al guardar el archivo'];
    }

    public function delete(string $path): bool
    {
        $fullPath = $this->uploadPath . '/' . $path;
        
        if (file_exists($fullPath) && strpos(realpath($fullPath), realpath($this->uploadPath)) === 0) {
            return unlink($fullPath);
        }

        return false;
    }

    public function exists(string $path): bool
    {
        $fullPath = $this->uploadPath . '/' . $path;
        return file_exists($fullPath);
    }
}
