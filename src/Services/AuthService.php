<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\{Database, Session};
use App\Repositories\UsuarioRepository;

final class AuthService
{
    private UsuarioRepository $repo;

    public function __construct()
    {
        $this->repo = new UsuarioRepository();
    }

    public function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_ARGON2ID, ['cost' => 12]);
    }

    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public function login(string $email, string $password, int $empresaId): ?array
    {
        $user = $this->repo->findByEmail($email, $empresaId);
        if ($user && $this->verifyPassword($password, $user['password_hash'])) {
            Session::put('user_id', (int)$user['id']);
            Session::put('empresa_id', $empresaId);
            Session::put('rol', $user['rol']);
            Session::regenerate();
            return $user;
        }
        return null;
    }

    public function logout(): void
    {
        Session::flush();
    }

    public function check(): bool
    {
        return Session::get('user_id') !== null;
    }

    public function user(): ?array
    {
        $id = Session::get('user_id');
        $empresaId = Session::get('empresa_id');
        if (!$id || !$empresaId) {
            return null;
        }
        return $this->repo->findById((int)$id, (int)$empresaId);
    }
}
