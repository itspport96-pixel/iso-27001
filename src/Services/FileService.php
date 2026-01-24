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
        $this->uploadPath = $_ENV['UPLOAD_PATH'] ?? '/var/www/html/storage/uploads';
        $this->maxSize = (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 10485760);
    }

    public function upload(array $file, int $empresaId): array
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'error' => 'Archivo inválido'];
        }

        if ($file['size'] > $this->maxSize) {
            return ['success' => false, 'error' => 'Archivo excede el tamaño máximo'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->allowedMimes)) {
            return ['success' => false, 'error' => 'Tipo de archivo no permitido'];
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
                'nombre_original' => $file['name'],
                'ruta' => $destination,
                'tipo_mime' => $mimeType,
                'tamano' => $file['size'],
                'hash' => $hash
            ];
        }

        return ['success' => false, 'error' => 'Error al guardar el archivo'];
    }

    public function delete(string $path): bool
    {
        if (file_exists($path) && strpos(realpath($path), realpath($this->uploadPath)) === 0) {
            return unlink($path);
        }

        return false;
    }

    public function exists(string $path): bool
    {
        return file_exists($path);
    }
}
