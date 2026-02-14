<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class PasswordPolicyService
{
    private PDO $db;
    
    // Configuración de políticas
    private int $historyCount = 5;           // No repetir últimas 5 contraseñas
    private int $expirationDays = 90;        // Contraseña expira en 90 días
    private int $minLength = 8;              // Mínimo 8 caracteres
    private int $warningDays = 14;           // Advertir 14 días antes de expirar

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Valida la complejidad de una contraseña
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateComplexity(string $password): array
    {
        $errors = [];

        if (strlen($password) < $this->minLength) {
            $errors[] = "La contraseña debe tener al menos {$this->minLength} caracteres";
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "La contraseña debe contener al menos una letra mayúscula";
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "La contraseña debe contener al menos una letra minúscula";
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "La contraseña debe contener al menos un número";
        }

        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
            $errors[] = "La contraseña debe contener al menos un carácter especial (!@#$%^&*...)";
        }

        // Verificar patrones comunes inseguros
        $commonPatterns = ['123456', 'password', 'qwerty', 'abc123', '111111', 'admin'];
        foreach ($commonPatterns as $pattern) {
            if (stripos($password, $pattern) !== false) {
                $errors[] = "La contraseña contiene un patrón común inseguro";
                break;
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Verifica si la contraseña ya fue usada anteriormente
     */
    public function isPasswordInHistory(int $usuarioId, string $newPassword): bool
    {
        $sql = "SELECT password_hash FROM password_history 
                WHERE usuario_id = :usuario_id 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $this->historyCount, PDO::PARAM_INT);
        $stmt->execute();
        
        $history = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($history as $oldHash) {
            if (password_verify($newPassword, $oldHash)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Guarda la contraseña en el historial
     */
    public function saveToHistory(int $usuarioId, string $passwordHash): bool
    {
        $sql = "INSERT INTO password_history (usuario_id, password_hash) VALUES (:usuario_id, :password_hash)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':password_hash', $passwordHash);
        
        $result = $stmt->execute();
        
        // Limpiar historial antiguo (mantener solo las últimas N)
        if ($result) {
            $this->cleanOldHistory($usuarioId);
        }
        
        return $result;
    }

    /**
     * Limpia el historial antiguo manteniendo solo las últimas N contraseñas
     */
    private function cleanOldHistory(int $usuarioId): void
    {
        // Obtener IDs a mantener
        $sql = "SELECT id FROM password_history 
                WHERE usuario_id = :usuario_id 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $this->historyCount + 2, PDO::PARAM_INT); // +2 por seguridad
        $stmt->execute();
        
        $keepIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($keepIds)) {
            $placeholders = implode(',', array_fill(0, count($keepIds), '?'));
            $sql = "DELETE FROM password_history 
                    WHERE usuario_id = ? AND id NOT IN ({$placeholders})";
            
            $stmt = $this->db->prepare($sql);
            $params = array_merge([$usuarioId], $keepIds);
            $stmt->execute($params);
        }
    }

    /**
     * Verifica si la contraseña ha expirado
     */
    public function isPasswordExpired(?string $passwordUpdatedAt): bool
    {
        if (empty($passwordUpdatedAt)) {
            return true;
        }
        
        $expirationDate = strtotime($passwordUpdatedAt . " +{$this->expirationDays} days");
        return time() > $expirationDate;
    }

    /**
     * Obtiene los días restantes antes de que expire la contraseña
     */
    public function getDaysUntilExpiration(?string $passwordUpdatedAt): int
    {
        if (empty($passwordUpdatedAt)) {
            return 0;
        }
        
        $expirationDate = strtotime($passwordUpdatedAt . " +{$this->expirationDays} days");
        $daysRemaining = ceil(($expirationDate - time()) / 86400);
        
        return max(0, (int)$daysRemaining);
    }

    /**
     * Verifica si debe mostrar advertencia de expiración próxima
     */
    public function shouldWarnExpiration(?string $passwordUpdatedAt): bool
    {
        $daysRemaining = $this->getDaysUntilExpiration($passwordUpdatedAt);
        return $daysRemaining > 0 && $daysRemaining <= $this->warningDays;
    }

    /**
     * Valida una nueva contraseña completamente
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateNewPassword(int $usuarioId, string $newPassword): array
    {
        $errors = [];
        
        // 1. Validar complejidad
        $complexityResult = $this->validateComplexity($newPassword);
        if (!$complexityResult['valid']) {
            $errors = array_merge($errors, $complexityResult['errors']);
        }
        
        // 2. Verificar historial
        if ($this->isPasswordInHistory($usuarioId, $newPassword)) {
            $errors[] = "No puedes reutilizar una de tus últimas {$this->historyCount} contraseñas";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Obtiene la configuración actual de políticas
     */
    public function getPolicyConfig(): array
    {
        return [
            'min_length' => $this->minLength,
            'history_count' => $this->historyCount,
            'expiration_days' => $this->expirationDays,
            'warning_days' => $this->warningDays,
            'requires_uppercase' => true,
            'requires_lowercase' => true,
            'requires_number' => true,
            'requires_special' => true
        ];
    }
}
