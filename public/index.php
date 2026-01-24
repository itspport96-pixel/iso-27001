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
use App\Controllers\ControlController;
use App\Controllers\GapController;
use App\Controllers\EvidenciaController;
use App\Middleware\CsrfMiddleware;
use App\Middleware\AuthMiddleware;

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
$request = new Request();
$response = new Response();
$router = new Router();
$log = new LogService();

// Registrar inicio de request
$log->info('Request received', [
    'method' => $request->method(),
    'uri' => $request->uri(),
    'ip' => $request->ip()
]);

// Rutas públicas
$router->get('/', function($request, $response) {
    $response->html('<h1>ISO 27001 Compliance Platform v2.0</h1><p>Sistema iniciado correctamente</p><a href="/register">Registrarse</a> | <a href="/login">Login</a>');
});

// Rutas de autenticación
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register'], [CsrfMiddleware::class]);

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);

$router->get('/logout', [AuthController::class, 'logout']);

// Dashboard
$router->get('/dashboard', function($request, $response) {
    $session = new Session();
    if (!$session->has('user_id')) {
        $response->redirect('/login');
        return;
    }
    
    $user = [
        'nombre' => $session->get('user_nombre'),
        'email' => $session->get('user_email'),
        'rol' => $session->get('user_rol'),
        'empresa_id' => $session->get('empresa_id')
    ];
    
    $html = '<h1>Dashboard</h1>';
    $html .= '<p>Bienvenido: ' . htmlspecialchars($user['nombre']) . '</p>';
    $html .= '<p>Email: ' . htmlspecialchars($user['email']) . '</p>';
    $html .= '<p>Rol: ' . htmlspecialchars($user['rol']) . '</p>';
    $html .= '<p>Empresa ID: ' . htmlspecialchars($user['empresa_id']) . '</p>';
    $html .= '<hr>';
    $html .= '<h3>Menú</h3>';
    $html .= '<ul>';
    $html .= '<li><a href="/controles">Controles ISO 27001</a></li>';
    $html .= '<li><a href="/gaps">Análisis de Brechas (GAP)</a></li>';
    $html .= '<li><a href="/evidencias">Evidencias</a></li>';
    $html .= '<li><a href="/logout">Cerrar Sesión</a></li>';
    $html .= '</ul>';
    
    $response->html($html);
});

// Rutas de controles
$router->get('/controles', [ControlController::class, 'index']);
$router->get('/controles/{id}', [ControlController::class, 'show']);
$router->post('/controles/{id}/update', [ControlController::class, 'update'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->get('/controles/estadisticas', [ControlController::class, 'estadisticas']);

// Rutas de GAPs
$router->get('/gaps', [GapController::class, 'index']);
$router->get('/gaps/create', [GapController::class, 'create']);
$router->post('/gaps/store', [GapController::class, 'store'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->get('/gaps/{id}', [GapController::class, 'show']);
$router->post('/gaps/{id}/update', [GapController::class, 'update'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/gaps/{id}/delete', [GapController::class, 'delete'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/gaps/accion/{id}/update', [GapController::class, 'updateAccion'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/gaps/accion/{id}/completar', [GapController::class, 'completarAccion'], [CsrfMiddleware::class, AuthMiddleware::class]);

// Rutas de Evidencias
$router->get('/evidencias', [EvidenciaController::class, 'index']);
$router->get('/evidencias/create', [EvidenciaController::class, 'create']);
$router->post('/evidencias/store', [EvidenciaController::class, 'store'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->get('/evidencias/{id}', [EvidenciaController::class, 'show']);
$router->post('/evidencias/{id}/validar', [EvidenciaController::class, 'validar'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/evidencias/{id}/delete', [EvidenciaController::class, 'delete'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->get('/evidencias/{id}/download', [EvidenciaController::class, 'download']);

// Manejo de errores global
try {
    $router->dispatch($request, $response);
} catch (\Exception $e) {
    $log->critical('Unhandled exception', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    $response->error('Error interno del servidor', 500);
}
