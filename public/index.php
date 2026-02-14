<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Database;
use App\Core\Router;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\TenantContext;
use App\Services\LogService;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ControlController;
use App\Controllers\GapController;
use App\Controllers\EvidenciaController;
use App\Controllers\RequerimientoController;
use App\Controllers\PerfilController;
use App\Controllers\UsuarioController;
use App\Controllers\AuditController;
use App\Controllers\ConfiguracionController;
use App\Controllers\NotificacionController;
use App\Controllers\ReporteController;
use App\Middleware\CsrfMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\RoleMiddleware;
use App\Middleware\TenantMiddleware;

// Cargar variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Configurar error reporting
if ($_ENV['APP_DEBUG'] === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Inicializar servicios
$request  = new Request();
$response = new Response();
$router   = new Router();
$log      = new LogService();

// Registrar inicio de request
$log->info('Request received', [
    'method' => $request->method(),
    'uri'    => $request->uri(),
    'ip'     => $request->ip()
]);

// Rutas publicas
$router->get('/', function($request, $response) {
    $response->html('<h1>ISO 27001 Compliance Platform v2.0</h1><p>Sistema iniciado correctamente</p><a href="/register">Registrarse</a> | <a href="/login">Login</a>');
});

// Rutas de autenticacion
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register'], [CsrfMiddleware::class]);

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class, RateLimitMiddleware::class]);

$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/cambiar-password', [AuthController::class, 'showCambiarPassword']);

// Dashboard
$router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class, TenantMiddleware::class]);

// Rutas de controles
$router->get('/controles', [ControlController::class, 'index'], [AuthMiddleware::class, TenantMiddleware::class]);
$router->get('/controles/search', [ControlController::class, 'search'], [AuthMiddleware::class, TenantMiddleware::class]);
$router->get('/controles/estadisticas', [ControlController::class, 'estadisticas'], [AuthMiddleware::class, TenantMiddleware::class]);
$router->get('/controles/{id}', [ControlController::class, 'show'], [AuthMiddleware::class, TenantMiddleware::class]);
$router->post('/controles/{id}/update', [ControlController::class, 'update'], [CsrfMiddleware::class, AuthMiddleware::class, TenantMiddleware::class]);

// Rutas de GAPs
$router->get('/gaps', [GapController::class, 'index'], [AuthMiddleware::class, TenantMiddleware::class]);
$router->get('/gaps/create', [GapController::class, 'create'], [AuthMiddleware::class, TenantMiddleware::class]);
$router->post('/gaps/store', [GapController::class, 'store'], [CsrfMiddleware::class, AuthMiddleware::class, TenantMiddleware::class]);
$router->get('/gaps/{id}', [GapController::class, 'show'], [AuthMiddleware::class, TenantMiddleware::class]);
$router->post('/gaps/{id}/update', [GapController::class, 'update'], [CsrfMiddleware::class, AuthMiddleware::class, TenantMiddleware::class]);
$router->post('/gaps/{id}/delete', [GapController::class, 'delete'], [CsrfMiddleware::class, AuthMiddleware::class, TenantMiddleware::class]);
$router->post('/gaps/accion/{id}/update', [GapController::class, 'updateAccion'], [CsrfMiddleware::class, AuthMiddleware::class, TenantMiddleware::class]);
$router->post('/gaps/accion/{id}/completar', [GapController::class, 'completarAccion'], [CsrfMiddleware::class, AuthMiddleware::class, TenantMiddleware::class]);

// Rutas de Evidencias
$router->get('/evidencias', [EvidenciaController::class, 'index'], [AuthMiddleware::class, TenantMiddleware::class]);
$router->get('/evidencias/create', [EvidenciaController::class, 'create'], [AuthMiddleware::class, TenantMiddleware::class]);
$router->post('/evidencias/store', [EvidenciaController::class, 'store'], [CsrfMiddleware::class, AuthMiddleware::class, TenantMiddleware::class, RateLimitMiddleware::class]);
$router->get('/evidencias/{id}', [EvidenciaController::class, 'show'], [AuthMiddleware::class, TenantMiddleware::class]);
$router->post('/evidencias/{id}/validar', [EvidenciaController::class, 'validar'], [CsrfMiddleware::class, AuthMiddleware::class, TenantMiddleware::class]);
$router->post('/evidencias/{id}/delete', [EvidenciaController::class, 'delete'], [CsrfMiddleware::class, AuthMiddleware::class, TenantMiddleware::class]);
$router->get('/evidencias/{id}/download', [EvidenciaController::class, 'download'], [AuthMiddleware::class, TenantMiddleware::class]);

// Rutas de Requerimientos
$router->get('/requerimientos', [RequerimientoController::class, 'index'], [AuthMiddleware::class, TenantMiddleware::class]);
$router->get('/requerimientos/{id}', [RequerimientoController::class, 'show'], [AuthMiddleware::class, TenantMiddleware::class]);
$router->post('/requerimientos/{id}/update', [RequerimientoController::class, 'update'], [CsrfMiddleware::class, AuthMiddleware::class, TenantMiddleware::class]);

// Rutas de Perfil
$router->get('/perfil', [PerfilController::class, 'index'], [AuthMiddleware::class, TenantMiddleware::class]);
$router->post('/perfil/update-datos', [PerfilController::class, 'updateDatos'], [CsrfMiddleware::class, AuthMiddleware::class, TenantMiddleware::class]);
$router->post('/perfil/update-password', [PerfilController::class, 'updatePassword'], [CsrfMiddleware::class, AuthMiddleware::class, TenantMiddleware::class]);
$router->post('/perfil/cambiar-password-obligatorio', [PerfilController::class, 'cambiarPasswordObligatorio'], [CsrfMiddleware::class]);

// Rutas de Usuarios
$router->get('/usuarios', [UsuarioController::class, 'index'], [AuthMiddleware::class, TenantMiddleware::class]);
$router->get('/usuarios/search', [UsuarioController::class, 'search'], [AuthMiddleware::class, TenantMiddleware::class]);
$router->get('/usuarios/create', [UsuarioController::class, 'create'], [AuthMiddleware::class, TenantMiddleware::class]);
$router->post('/usuarios/store', [UsuarioController::class, 'store'], [CsrfMiddleware::class, AuthMiddleware::class, TenantMiddleware::class]);
$router->get('/usuarios/{id}', [UsuarioController::class, 'show'], [AuthMiddleware::class, TenantMiddleware::class]);
$router->get('/usuarios/{id}/edit', [UsuarioController::class, 'edit'], [AuthMiddleware::class, TenantMiddleware::class]);
$router->post('/usuarios/{id}/update', [UsuarioController::class, 'update'], [CsrfMiddleware::class, AuthMiddleware::class, TenantMiddleware::class]);
$router->post('/usuarios/{id}/cambiar-estado', [UsuarioController::class, 'cambiarEstado'], [CsrfMiddleware::class, AuthMiddleware::class, TenantMiddleware::class]);
$router->post('/usuarios/{id}/reset-password', [UsuarioController::class, 'resetPassword'], [CsrfMiddleware::class, AuthMiddleware::class, TenantMiddleware::class]);
$router->post('/usuarios/{id}/delete', [UsuarioController::class, 'delete'], [CsrfMiddleware::class, AuthMiddleware::class, TenantMiddleware::class]);

// Rutas de Auditoria
$router->get('/audit', [AuditController::class, 'index'], [AuthMiddleware::class, TenantMiddleware::class]);
$router->get('/audit/{id}', [AuditController::class, 'show'], [AuthMiddleware::class, TenantMiddleware::class]);

// Rutas de Configuracion (solo admin_empresa)
$router->get('/configuracion', [ConfiguracionController::class, 'index'], [AuthMiddleware::class, TenantMiddleware::class]);
$router->post('/configuracion/empresa', [ConfiguracionController::class, 'saveEmpresa'], [CsrfMiddleware::class, AuthMiddleware::class, TenantMiddleware::class]);
$router->post('/configuracion/smtp', [ConfiguracionController::class, 'saveSmtp'], [CsrfMiddleware::class, AuthMiddleware::class, TenantMiddleware::class]);
$router->post('/configuracion/smtp/test', [ConfiguracionController::class, 'testSmtp'], [CsrfMiddleware::class, AuthMiddleware::class, TenantMiddleware::class]);

// Rutas de Notificaciones
$router->get('/notificaciones', [NotificacionController::class, 'index'], [AuthMiddleware::class, TenantMiddleware::class]);
$router->get('/notificaciones/resumen', [NotificacionController::class, 'getResumen'], [AuthMiddleware::class, TenantMiddleware::class]);
$router->post('/notificaciones/enviar', [NotificacionController::class, 'enviar'], [CsrfMiddleware::class, AuthMiddleware::class, TenantMiddleware::class]);
$router->get('/notificaciones/historial', [NotificacionController::class, 'getHistorial'], [AuthMiddleware::class, TenantMiddleware::class]);

// Manejo de errores global
try {
    $router->dispatch($request, $response);
} catch (\Exception $e) {
    $log->critical('Unhandled exception', [
        'message' => $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine()
    ]);
    $response->error('Error interno del servidor', 500);
}
