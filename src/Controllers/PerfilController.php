<?php

namespace App\Controllers;

use App\Controllers\Base\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\TenantContext;
use App\Core\Validator;
use App\Models\Usuario;
use App\Repositories\UsuarioRepository;
use App\Services\AuditService;
use App\Services\PasswordPolicyService;

class PerfilController extends Controller
{
    private AuditService $auditService;
    private UsuarioRepository $usuarioRepo;
    private PasswordPolicyService $passwordPolicy;

    public function __construct()
    {
        parent::__construct();
        $this->auditService = new AuditService();
        $this->usuarioRepo = new UsuarioRepository();
        $this->passwordPolicy = new PasswordPolicyService();
    }

    public function index(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $userId = $this->user()['id'];
        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->find($userId);

        if (!$usuario) {
            $this->response->error('Usuario no encontrado', 404);
            return;
        }

        $this->view('perfil/index', [
            'usuario' => $usuario
        ]);
    }

    public function updateDatos(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $userId = $this->user()['id'];
        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $validator = new Validator($request->all());

        $rules = [
            'nombre' => 'required|min:3|max:255',
            'email' => 'required|email'
        ];

        if (!$validator->validate($rules)) {
            $this->json(['success' => false, 'errors' => $validator->errors()], 400);
            return;
        }

        $usuarioModel = new Usuario();
        $usuarioActual = $usuarioModel->find($userId);

        if (!$usuarioActual) {
            $this->json(['success' => false, 'error' => 'Usuario no encontrado'], 404);
            return;
        }

        $nuevoEmail = $request->post('email');

        if ($nuevoEmail !== $usuarioActual['email']) {
            if ($usuarioModel->emailExists($nuevoEmail, $userId)) {
                $this->json(['success' => false, 'error' => 'El email ya está en uso'], 400);
                return;
            }
        }

        $data = [
            'nombre' => $request->post('nombre'),
            'email' => $nuevoEmail
        ];

        $result = $usuarioModel->update($userId, $data);

        if ($result) {
            $this->session->set('user_nombre', $data['nombre']);
            $this->session->set('user_email', $data['email']);

            $this->auditService->log(
                'UPDATE',
                'usuarios',
                $userId,
                [
                    'nombre' => $usuarioActual['nombre'],
                    'email' => $usuarioActual['email']
                ],
                [
                    'nombre' => $data['nombre'],
                    'email' => $data['email']
                ]
            );

            $this->json(['success' => true, 'message' => 'Datos actualizados correctamente']);
        } else {
            $this->json(['success' => false, 'error' => 'Error al actualizar datos'], 500);
        }
    }

    public function updatePassword(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $userId = $this->user()['id'];
        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $validator = new Validator($request->all());

        $rules = [
            'password_actual' => 'required',
            'password_nueva' => 'required|min:8',
            'password_confirmar' => 'required'
        ];

        if (!$validator->validate($rules)) {
            $this->json(['success' => false, 'errors' => $validator->errors()], 400);
            return;
        }

        if ($request->post('password_nueva') !== $request->post('password_confirmar')) {
            $this->json(['success' => false, 'error' => 'Las contraseñas nuevas no coinciden'], 400);
            return;
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->find($userId);

        if (!$usuario) {
            $this->json(['success' => false, 'error' => 'Usuario no encontrado'], 404);
            return;
        }

        if (!password_verify($request->post('password_actual'), $usuario['password_hash'])) {
            $this->json(['success' => false, 'error' => 'La contraseña actual es incorrecta'], 400);
            return;
        }

        $nuevaPassword = $request->post('password_nueva');

        // Validar políticas de contraseña
        $policyValidation = $this->passwordPolicy->validateNewPassword($userId, $nuevaPassword);
        if (!$policyValidation['valid']) {
            $this->json([
                'success' => false, 
                'error' => implode('. ', $policyValidation['errors'])
            ], 400);
            return;
        }

        $nuevaPasswordHash = password_hash($nuevaPassword, PASSWORD_ARGON2ID);

        $result = $usuarioModel->update($userId, [
            'password_hash' => $nuevaPasswordHash,
            'password_updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($result) {
            // Guardar en historial
            $this->passwordPolicy->saveToHistory($userId, $nuevaPasswordHash);

            $this->auditService->log(
                'UPDATE',
                'usuarios',
                $userId,
                ['accion' => 'cambio_password'],
                ['accion' => 'password_actualizado']
            );

            $this->json(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
        } else {
            $this->json(['success' => false, 'error' => 'Error al actualizar contraseña'], 500);
        }
    }

    public function cambiarPasswordObligatorio(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        
        // Validar sesion manualmente (sin pasar por requireAuth que bloquea)
        if (!$this->isAuthenticated()) {
            $this->json(['success' => false, 'error' => 'No autenticado'], 401);
            return;
        }

        $userId = $this->user()['id'];
        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $validator = new Validator($request->all());

        $rules = [
            'password_nueva' => 'required|min:8',
            'password_confirmar' => 'required'
        ];

        if (!$validator->validate($rules)) {
            $this->json(['success' => false, 'errors' => $validator->errors()], 400);
            return;
        }

        if ($request->post('password_nueva') !== $request->post('password_confirmar')) {
            $this->json(['success' => false, 'error' => 'Las contraseñas no coinciden'], 400);
            return;
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->find($userId);

        if (!$usuario) {
            $this->json(['success' => false, 'error' => 'Usuario no encontrado'], 404);
            return;
        }

        $nuevaPassword = $request->post('password_nueva');

        // Validar políticas de contraseña
        $policyValidation = $this->passwordPolicy->validateNewPassword($userId, $nuevaPassword);
        if (!$policyValidation['valid']) {
            $this->json([
                'success' => false, 
                'error' => implode('. ', $policyValidation['errors'])
            ], 400);
            return;
        }

        $nuevaPasswordHash = password_hash($nuevaPassword, PASSWORD_ARGON2ID);

        $result = $usuarioModel->update($userId, [
            'password_hash' => $nuevaPasswordHash,
            'password_updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($result) {
            // Guardar en historial
            $this->passwordPolicy->saveToHistory($userId, $nuevaPasswordHash);

            // Quitar el flag de debe cambiar password
            $this->usuarioRepo->clearPasswordFlag($userId);
            $this->session->set('debe_cambiar_password', false);

            $this->auditService->log(
                'UPDATE',
                'usuarios',
                $userId,
                ['accion' => 'cambio_password_obligatorio'],
                ['accion' => 'password_actualizado_post_reset']
            );

            $this->json(['success' => true, 'message' => 'Contraseña actualizada correctamente', 'redirect' => '/dashboard']);
        } else {
            $this->json(['success' => false, 'error' => 'Error al actualizar contraseña'], 500);
        }
    }
}
