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

    private array $mimeToExtensions = [
        'application/pdf' => ['pdf'],
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
        'text/plain' => ['txt']
    ];

    private array $dangerousExtensions = [
        'php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'phar',
        'js', 'jsp', 'asp', 'aspx', 'cgi', 'pl', 'py', 'rb',
        'sh', 'bash', 'bat', 'cmd', 'exe', 'dll', 'so',
        'htaccess', 'htpasswd', 'ini', 'conf', 'config', 'svg'
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

        $extensionValidation = $this->validateExtension($file['name'], $mimeType);
        if (!$extensionValidation['valid']) {
            return ['success' => false, 'error' => $extensionValidation['error']];
        }
        
        $safeExtension = $extensionValidation['extension'];
        $hash = hash_file('sha256', $file['tmp_name']);
        $filename = $hash . '.' . $safeExtension;

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

    private function validateExtension(string $filename, string $mimeType): array
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (empty($extension)) {
            return ['valid' => false, 'error' => 'Archivo sin extensión'];
        }

        if (!preg_match('/^[a-z0-9]+$/', $extension)) {
            return ['valid' => false, 'error' => 'Extensión contiene caracteres inválidos'];
        }

        if (strlen($extension) > 5) {
            return ['valid' => false, 'error' => 'Extensión demasiado larga'];
        }

        if (in_array($extension, $this->dangerousExtensions)) {
            return ['valid' => false, 'error' => 'Tipo de archivo no permitido por seguridad'];
        }

        if (!isset($this->mimeToExtensions[$mimeType])) {
            return ['valid' => false, 'error' => 'Tipo MIME no tiene extensiones permitidas'];
        }

        $allowedExtensionsForMime = $this->mimeToExtensions[$mimeType];
        
        if (!in_array($extension, $allowedExtensionsForMime)) {
            $extension = $allowedExtensionsForMime[0];
        }

        return ['valid' => true, 'extension' => $extension];
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
