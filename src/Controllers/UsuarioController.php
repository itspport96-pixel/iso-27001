<?php

namespace App\Controllers;

use App\Controllers\Base\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\TenantContext;
use App\Core\Validator;
use App\Repositories\UsuarioRepository;
use App\Services\AuditService;
use App\Services\ConfiguracionService;
use App\Services\MailService;
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
            $this->auditService->log('ACCESS_DENIED', 'usuarios', null, null, ['accion' => 'index']);
            $this->response->error('Acceso denegado', 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $busqueda = $request->get('search', '');
        $page = max(1, (int)$request->get('page', 1));
        $perPage = 10;

        $usuarios = $this->usuarioRepo->searchWithPagination($busqueda, $page, $perPage);
        $total = $this->usuarioRepo->countWithBusqueda($busqueda);
        $totalPages = ceil($total / $perPage);

        $this->view('usuarios/index', [
            'usuarios' => $usuarios,
            'busqueda' => $busqueda,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'user_actual' => $this->user()
        ]);
    }

    public function search(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('usuarios.view')) {
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $busqueda = $request->get('search', '');
        $page = max(1, (int)$request->get('page', 1));
        $perPage = max(1, min(100, (int)$request->get('per_page', 10)));

        $usuarios = $this->usuarioRepo->searchWithPagination($busqueda, $page, $perPage);
        $total = $this->usuarioRepo->countWithBusqueda($busqueda);
        $totalPages = ceil($total / $perPage);

        $this->json([
            'success' => true,
            'data' => $usuarios,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $totalPages
            ]
        ]);
    }

    public function show(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('usuarios.view')) {
            $this->auditService->log('ACCESS_DENIED', 'usuarios', (int)$id, null, ['accion' => 'show']);
            $this->response->error('Acceso denegado', 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $usuario = $this->usuarioRepo->findWithEmpresa((int)$id);

        if (!$usuario) {
            $this->auditService->log('NOT_FOUND', 'usuarios', (int)$id, null, ['accion' => 'show']);
            $this->response->error('Usuario no encontrado', 404);
            return;
        }

        $this->view('usuarios/show', [
            'usuario' => $usuario,
            'user_actual' => $this->user()
        ]);
    }

    public function create(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('usuarios.create')) {
            $this->auditService->log('ACCESS_DENIED', 'usuarios', null, null, ['accion' => 'create']);
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
            $this->auditService->log('ACCESS_DENIED', 'usuarios', null, null, ['accion' => 'store']);
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
            $this->auditService->log('VALIDATION_ERROR', 'usuarios', null, null, [
                'errors' => $validator->errors()
            ]);
            $this->json(['success' => false, 'errors' => $validator->errors()], 400);
            return;
        }

        $usuarioModel = new Usuario();
        $emailIngresado = $request->post('email');

        if ($usuarioModel->emailExists($emailIngresado)) {
            $this->auditService->log('DUPLICATE_EMAIL', 'usuarios', null, null, [
                'email' => $emailIngresado
            ]);
            $this->json([
                'success' => false, 
                'error' => 'El email "' . htmlspecialchars($emailIngresado) . '" ya esta registrado en esta empresa. Por favor, utiliza otro email.'
            ], 400);
            return;
        }

        $data = [
            'nombre' => $request->post('nombre'),
            'email' => $emailIngresado,
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
                    'rol' => $data['rol'],
                    'estado' => $data['estado']
                ]
            );

            $this->json(['success' => true, 'message' => 'Usuario creado exitosamente', 'redirect' => '/usuarios']);
        } else {
            $this->auditService->log('CREATE_ERROR', 'usuarios', null, null, ['error' => 'Error al crear usuario']);
            $this->json(['success' => false, 'error' => 'Error al crear usuario'], 500);
        }
    }

    public function edit(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('usuarios.edit')) {
            $this->auditService->log('ACCESS_DENIED', 'usuarios', (int)$id, null, ['accion' => 'edit']);
            $this->response->error('Acceso denegado', 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->find((int)$id);

        if (!$usuario) {
            $this->auditService->log('NOT_FOUND', 'usuarios', (int)$id, null, ['accion' => 'edit']);
            $this->response->error('Usuario no encontrado', 404);
            return;
        }

        if ($usuario['id'] === $this->user()['id']) {
            $this->auditService->log('SELF_EDIT_ATTEMPT', 'usuarios', (int)$id, null, null);
            $this->response->error('No puedes editar tu propio usuario desde aqui. Usa Mi Perfil.', 403);
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
            $this->auditService->log('ACCESS_DENIED', 'usuarios', (int)$id, null, ['accion' => 'update']);
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        if ((int)$id === $this->user()['id']) {
            $this->auditService->log('SELF_EDIT_ATTEMPT', 'usuarios', (int)$id, null, null);
            $this->json(['success' => false, 'error' => 'No puedes editar tu propio usuario desde aqui'], 403);
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
            $this->auditService->log('VALIDATION_ERROR', 'usuarios', (int)$id, null, [
                'errors' => $validator->errors()
            ]);
            $this->json(['success' => false, 'errors' => $validator->errors()], 400);
            return;
        }

        $usuarioModel = new Usuario();
        $usuarioActual = $usuarioModel->find((int)$id);

        if (!$usuarioActual) {
            $this->auditService->log('NOT_FOUND', 'usuarios', (int)$id, null, ['accion' => 'update']);
            $this->json(['success' => false, 'error' => 'Usuario no encontrado'], 404);
            return;
        }

        $nuevoEmail = $request->post('email');

        if ($nuevoEmail !== $usuarioActual['email']) {
            if ($usuarioModel->emailExists($nuevoEmail, (int)$id)) {
                $this->auditService->log('DUPLICATE_EMAIL', 'usuarios', (int)$id, null, [
                    'email' => $nuevoEmail
                ]);
                $this->json([
                    'success' => false, 
                    'error' => 'El email "' . htmlspecialchars($nuevoEmail) . '" ya esta en uso por otro usuario de esta empresa.'
                ], 400);
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
            $this->auditService->log('UPDATE_ERROR', 'usuarios', (int)$id, null, ['error' => 'Error al actualizar']);
            $this->json(['success' => false, 'error' => 'Error al actualizar usuario'], 500);
        }
    }

    public function cambiarEstado(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('usuarios.edit')) {
            $this->auditService->log('ACCESS_DENIED', 'usuarios', (int)$id, null, ['accion' => 'cambiarEstado']);
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        if ((int)$id === $this->user()['id']) {
            $this->auditService->log('SELF_ESTADO_ATTEMPT', 'usuarios', (int)$id, null, null);
            $this->json(['success' => false, 'error' => 'No puedes cambiar tu propio estado'], 403);
            return;
        }

        $estado = $request->post('estado');

        if (!in_array($estado, ['activo', 'inactivo', 'bloqueado'])) {
            $this->auditService->log('INVALID_ESTADO', 'usuarios', (int)$id, null, ['estado' => $estado]);
            $this->json(['success' => false, 'error' => 'Estado invalido'], 400);
            return;
        }

        $usuarioModel = new Usuario();
        $usuarioActual = $usuarioModel->find((int)$id);

        if (!$usuarioActual) {
            $this->auditService->log('NOT_FOUND', 'usuarios', (int)$id, null, ['accion' => 'cambiarEstado']);
            $this->json(['success' => false, 'error' => 'Usuario no encontrado'], 404);
            return;
        }

        $result = $this->usuarioRepo->updateEstado((int)$id, $estado);

        if ($result) {
            $this->auditService->log(
                'UPDATE',
                'usuarios',
                (int)$id,
                [
                    'estado' => $usuarioActual['estado']
                ],
                [
                    'estado' => $estado
                ]
            );

            $this->json(['success' => true, 'message' => 'Estado actualizado exitosamente']);
        } else {
            $this->auditService->log('UPDATE_ERROR', 'usuarios', (int)$id, null, ['error' => 'Error al cambiar estado']);
            $this->json(['success' => false, 'error' => 'Error al cambiar estado'], 500);
        }
    }

    public function resetPassword(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('usuarios.edit')) {
            $this->auditService->log('ACCESS_DENIED', 'usuarios', (int)$id, null, ['accion' => 'resetPassword']);
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        if ((int)$id === $this->user()['id']) {
            $this->auditService->log('SELF_RESET_ATTEMPT', 'usuarios', (int)$id, null, null);
            $this->json(['success' => false, 'error' => 'No puedes resetear tu propia contrasena desde aqui. Usa Mi Perfil.'], 403);
            return;
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->find((int)$id);

        if (!$usuario) {
            $this->auditService->log('NOT_FOUND', 'usuarios', (int)$id, null, ['accion' => 'resetPassword']);
            $this->json(['success' => false, 'error' => 'Usuario no encontrado'], 404);
            return;
        }

        $nuevaPassword = $this->generarPasswordAleatoria(12);
        $passwordHash = password_hash($nuevaPassword, PASSWORD_ARGON2ID);

        $result = $this->usuarioRepo->updatePassword((int)$id, $passwordHash);

        if (!$result) {
            $this->auditService->log('RESET_PASSWORD_ERROR', 'usuarios', (int)$id, null, ['error' => 'Error al actualizar password']);
            $this->json(['success' => false, 'error' => 'Error al resetear la contrasena'], 500);
            return;
        }

        $emailEnviado = false;
        $mensajeEmail = '';

        try {
            $configService = new ConfiguracionService($empresaId);
            $smtpConfig = $configService->getSmtp();

            if (!empty($smtpConfig['smtp_activo']) && $smtpConfig['smtp_activo'] == '1' && !empty($smtpConfig['smtp_host'])) {
                $mailService = new MailService($smtpConfig);

                $empresaData = $configService->getEmpresa();
                $empresaNombre = $empresaData['nombre'] ?? 'ISO 27001 Platform';

                $asunto = 'Nueva contrasena - ' . $empresaNombre;
                $cuerpo = $this->generarEmailResetPassword($usuario['nombre'], $nuevaPassword, $empresaNombre);

                $emailEnviado = $mailService->enviar($usuario['email'], $asunto, $cuerpo);

                if (!$emailEnviado) {
                    $mensajeEmail = ' Sin embargo, no se pudo enviar el email. Comunica la contrasena manualmente: ' . $nuevaPassword;
                }
            } else {
                $mensajeEmail = ' SMTP no configurado. Comunica la contrasena manualmente: ' . $nuevaPassword;
            }
        } catch (\Exception $e) {
            $mensajeEmail = ' Error al enviar email. Comunica la contrasena manualmente: ' . $nuevaPassword;
        }

        $this->auditService->log(
            'UPDATE',
            'usuarios',
            (int)$id,
            ['password_reset' => true],
            ['password_reset' => true, 'email_enviado' => $emailEnviado]
        );

        if ($emailEnviado) {
            $this->json([
                'success' => true, 
                'message' => 'Contrasena reseteada exitosamente. Se envio la nueva contrasena al email: ' . htmlspecialchars($usuario['email'])
            ]);
        } else {
            $this->json([
                'success' => true, 
                'message' => 'Contrasena reseteada exitosamente.' . $mensajeEmail,
                'password' => $nuevaPassword
            ]);
        }
    }

    private function generarPasswordAleatoria(int $longitud = 12): string
    {
        $mayusculas = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $minusculas = 'abcdefghijklmnopqrstuvwxyz';
        $numeros = '0123456789';
        $especiales = '!@#$%&*';

        $password = '';
        $password .= $mayusculas[random_int(0, strlen($mayusculas) - 1)];
        $password .= $minusculas[random_int(0, strlen($minusculas) - 1)];
        $password .= $numeros[random_int(0, strlen($numeros) - 1)];
        $password .= $especiales[random_int(0, strlen($especiales) - 1)];

        $todos = $mayusculas . $minusculas . $numeros . $especiales;
        for ($i = 4; $i < $longitud; $i++) {
            $password .= $todos[random_int(0, strlen($todos) - 1)];
        }

        return str_shuffle($password);
    }

    private function generarEmailResetPassword(string $nombreUsuario, string $nuevaPassword, string $empresaNombre): string
    {
        return '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .password-box { background: #fff; border: 2px solid #3498db; padding: 15px; margin: 20px 0; text-align: center; }
                .password { font-size: 24px; font-weight: bold; color: #2c3e50; letter-spacing: 2px; }
                .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 15px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>' . htmlspecialchars($empresaNombre) . '</h1>
                </div>
                <div class="content">
                    <p>Hola <strong>' . htmlspecialchars($nombreUsuario) . '</strong>,</p>
                    <p>Tu contrasena ha sido reseteada por el administrador del sistema.</p>
                    <div class="password-box">
                        <p>Tu nueva contrasena es:</p>
                        <p class="password">' . htmlspecialchars($nuevaPassword) . '</p>
                    </div>
                    <div class="warning">
                        <strong>Importante:</strong> Por seguridad, te recomendamos cambiar esta contrasena inmediatamente despues de iniciar sesion.
                    </div>
                    <p>Si no solicitaste este cambio, contacta al administrador de tu empresa.</p>
                </div>
                <div class="footer">
                    <p>Este es un mensaje automatico del sistema ISO 27001 Compliance Platform.</p>
                    <p>Por favor, no respondas a este correo.</p>
                </div>
            </div>
        </body>
        </html>';
    }

    public function delete(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('usuarios.delete')) {
            $this->auditService->log('ACCESS_DENIED', 'usuarios', (int)$id, null, ['accion' => 'delete']);
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        if ((int)$id === $this->user()['id']) {
            $this->auditService->log('SELF_DELETE_ATTEMPT', 'usuarios', (int)$id, null, null);
            $this->json(['success' => false, 'error' => 'No puedes eliminar tu propio usuario'], 403);
            return;
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->find((int)$id);

        if (!$usuario) {
            $this->auditService->log('NOT_FOUND', 'usuarios', (int)$id, null, ['accion' => 'delete']);
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

            $this->json(['success' => true, 'message' => 'Usuario eliminado exitosamente']);
        } else {
            $this->auditService->log('DELETE_ERROR', 'usuarios', (int)$id, null, ['error' => 'Error al eliminar']);
            $this->json(['success' => false, 'error' => 'Error al eliminar usuario'], 500);
        }
    }
}
