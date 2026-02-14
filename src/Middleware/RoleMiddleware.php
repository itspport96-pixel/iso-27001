<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class RoleMiddleware
{
    private Session $session;
    private array $allowedRoles;

    public function __construct(array $allowedRoles = [])
    {
        $this->session      = new Session();
        $this->allowedRoles = $allowedRoles;
    }

    public function handle(Request $request, Response $response): void
    {
        if (!$this->session->has('user_id')) {
            $response->redirect('/login');
            exit;
        }

        $userRole = $this->session->get('user_rol');

        if (!empty($this->allowedRoles) && !in_array($userRole, $this->allowedRoles)) {
            $response->error('Acceso denegado', 403);
            exit;
        }
    }

    public static function can(string $permission): bool
    {
        $session = new Session();

        if (!$session->has('user_rol')) {
            return false;
        }

        $role = $session->get('user_rol');

        $permissions = [
            'super_admin' => [
                'controles.view', 'controles.edit',
                'gaps.view', 'gaps.create', 'gaps.edit', 'gaps.delete',
                'evidencias.view', 'evidencias.upload', 'evidencias.validate', 'evidencias.delete',
                'usuarios.view', 'usuarios.create', 'usuarios.edit', 'usuarios.delete',
                'requerimientos.view', 'requerimientos.edit',
                'dashboard.view', 'audit.view',
                'configuracion.manage',
            ],
            'admin_empresa' => [
                'controles.view', 'controles.edit',
                'gaps.view', 'gaps.create', 'gaps.edit', 'gaps.delete',
                'evidencias.view', 'evidencias.upload', 'evidencias.validate', 'evidencias.delete',
                'usuarios.view', 'usuarios.create', 'usuarios.edit', 'usuarios.delete',
                'requerimientos.view', 'requerimientos.edit',
                'dashboard.view', 'audit.view',
                'configuracion.manage',
            ],
            'auditor' => [
                'controles.view',
                'gaps.view',
                'evidencias.view', 'evidencias.validate',
                'requerimientos.view',
                'dashboard.view', 'audit.view',
            ],
            'consultor' => [
                'controles.view',
                'gaps.view', 'gaps.create', 'gaps.edit',
                'evidencias.view', 'evidencias.upload',
                'requerimientos.view',
                'dashboard.view',
            ],
        ];

        if (!isset($permissions[$role])) {
            return false;
        }

        return in_array($permission, $permissions[$role]);
    }
}
