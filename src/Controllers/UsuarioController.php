<?php

namespace App\Controllers;

use App\Controllers\Base\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\TenantContext;
use App\Core\Validator;
use App\Repositories\UsuarioRepository;
use App\Services\AuditService;
use App\Models\Usuario;
use App\Middleware\RoleMiddleware;

class UsuarioController extends Controller
{
    private UsuarioRepository $usuarioRepo;
    private AuditService $auditService;

    public function __construct()
    {
        parent::__construct();
        $this->usuarioRepo = new UsuarioRepository();
        $this->auditService = new AuditService();
    }

    public function index(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('usuarios.view')) {
            $this->response->error('Acceso denegado', 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $usuarioModel = new Usuario();
        $usuarios = $usuarioModel->findAll();

        $this->view('usuarios/index', [
            'usuarios' => $usuarios,
            'user_actual' => $this->user()
        ]);
    }

    public function create(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('usuarios.create')) {
            $this->response->error('Acceso denegado', 403);
            return;
        }

        $this->view('usuarios/create');
    }

    public function store(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('usuarios.create')) {
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $validator = new Validator($request->all());

        $rules = [
            'nombre' => 'required|min:3|max:255',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'rol' => 'required|in:admin_empresa,auditor,consultor'
        ];

        if (!$validator->validate($rules)) {
            $this->json(['success' => false, 'errors' => $validator->errors()], 400);
            return;
        }

        $usuarioModel = new Usuario();

        if ($usuarioModel->emailExists($request->post('email'))) {
            $this->json(['success' => false, 'error' => 'El email ya está registrado en esta empresa'], 400);
            return;
        }

        $data = [
            'nombre' => $request->post('nombre'),
            'email' => $request->post('email'),
            'password_hash' => password_hash($request->post('password'), PASSWORD_ARGON2ID),
            'rol' => $request->post('rol'),
            'estado' => 'activo'
        ];

        $usuarioId = $usuarioModel->create($data);

        if ($usuarioId) {
            $this->auditService->log(
                'INSERT',
                'usuarios',
                $usuarioId,
                null,
                [
                    'nombre' => $data['nombre'],
                    'email' => $data['email'],
                    'rol' => $data['rol']
                ]
            );

            $this->json(['success' => true, 'message' => 'Usuario creado exitosamente', 'redirect' => '/usuarios']);
        } else {
            $this->json(['success' => false, 'error' => 'Error al crear usuario'], 500);
        }
    }

    public function edit(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('usuarios.edit')) {
            $this->response->error('Acceso denegado', 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->find((int)$id);

        if (!$usuario) {
            $this->response->error('Usuario no encontrado', 404);
            return;
        }

        if ($usuario['id'] === $this->user()['id']) {
            $this->response->error('No puedes editar tu propio usuario desde aquí. Usa Mi Perfil.', 403);
            return;
        }

        $this->view('usuarios/edit', [
            'usuario' => $usuario
        ]);
    }

    public function update(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('usuarios.edit')) {
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        if ((int)$id === $this->user()['id']) {
            $this->json(['success' => false, 'error' => 'No puedes editar tu propio usuario desde aquí'], 403);
            return;
        }

        $validator = new Validator($request->all());

        $rules = [
            'nombre' => 'required|min:3|max:255',
            'email' => 'required|email',
            'rol' => 'required|in:admin_empresa,auditor,consultor',
            'estado' => 'required|in:activo,inactivo,bloqueado'
        ];

        if (!$validator->validate($rules)) {
            $this->json(['success' => false, 'errors' => $validator->errors()], 400);
            return;
        }

        $usuarioModel = new Usuario();
        $usuarioActual = $usuarioModel->find((int)$id);

        if (!$usuarioActual) {
            $this->json(['success' => false, 'error' => 'Usuario no encontrado'], 404);
            return;
        }

        $nuevoEmail = $request->post('email');

        if ($nuevoEmail !== $usuarioActual['email']) {
            if ($usuarioModel->emailExists($nuevoEmail, (int)$id)) {
                $this->json(['success' => false, 'error' => 'El email ya está en uso'], 400);
                return;
            }
        }

        $data = [
            'nombre' => $request->post('nombre'),
            'email' => $nuevoEmail,
            'rol' => $request->post('rol'),
            'estado' => $request->post('estado')
        ];

        $result = $usuarioModel->update((int)$id, $data);

        if ($result) {
            $this->auditService->log(
                'UPDATE',
                'usuarios',
                (int)$id,
                [
                    'nombre' => $usuarioActual['nombre'],
                    'email' => $usuarioActual['email'],
                    'rol' => $usuarioActual['rol'],
                    'estado' => $usuarioActual['estado']
                ],
                $data
            );

            $this->json(['success' => true, 'message' => 'Usuario actualizado exitosamente']);
        } else {
            $this->json(['success' => false, 'error' => 'Error al actualizar usuario'], 500);
        }
    }

    public function delete(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('usuarios.delete')) {
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        if ((int)$id === $this->user()['id']) {
            $this->json(['success' => false, 'error' => 'No puedes eliminar tu propio usuario'], 403);
            return;
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->find((int)$id);

        if (!$usuario) {
            $this->json(['success' => false, 'error' => 'Usuario no encontrado'], 404);
            return;
        }

        $result = $usuarioModel->delete((int)$id);

        if ($result) {
            $this->auditService->log(
                'DELETE',
                'usuarios',
                (int)$id,
                [
                    'nombre' => $usuario['nombre'],
                    'email' => $usuario['email'],
                    'rol' => $usuario['rol']
                ],
                null
            );

            $this->json(['success' => true, 'message' => 'Usuario desactivado exitosamente']);
        } else {
            $this->json(['success' => false, 'error' => 'Error al desactivar usuario'], 500);
        }
    }
}
