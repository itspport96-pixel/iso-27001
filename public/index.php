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
use App\Models\Empresa;
use App\Models\Usuario;
use App\Models\Control;
use App\Models\SOA;
use App\Controllers\AuthController;
use App\Middleware\CsrfMiddleware;

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

// Dashboard temporal
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
    $html .= '<br><a href="/logout">Cerrar Sesión</a>';
    
    $response->html($html);
});

// Rutas de prueba
$router->get('/test-db', function($request, $response) use ($log) {
    try {
        $db = Database::getInstance();
        $connection = $db->getConnection();
        $response->json(['status' => 'success', 'message' => 'Conexión a base de datos exitosa']);
    } catch (\Exception $e) {
        $log->error('Database connection failed', ['error' => $e->getMessage()]);
        $response->json(['status' => 'error', 'message' => 'Error de conexión'], 500);
    }
});

$router->get('/test-session', function($request, $response) {
    $session = new Session();
    $session->set('test_key', 'test_value_' . time());
    $value = $session->get('test_key');
    $response->json(['status' => 'success', 'session_value' => $value]);
});

$router->get('/test-cache', function($request, $response) {
    $cache = new \App\Services\CacheService();
    $cache->put('test_key', 'cached_value_' . time(), 60);
    $value = $cache->get('test_key');
    $response->json(['status' => 'success', 'cache_value' => $value]);
});

$router->get('/test-schema', function($request, $response) use ($log) {
    try {
        $db = Database::getInstance();
        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        $counts = [];
        foreach($tables as $table) {
            $count = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            $counts[$table] = $count;
        }
        
        $response->json(['status' => 'success', 'tables' => $counts]);
    } catch (\Exception $e) {
        $log->error('Schema test failed', ['error' => $e->getMessage()]);
        $response->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
});

$router->get('/test-tenant', function($request, $response) use ($log) {
    try {
        $empresaModel = new Empresa();
        $empresaId = $empresaModel->create([
            'nombre' => 'Empresa Test',
            'ruc' => 'TEST' . time(),
            'contacto' => 'Test Contact',
            'email' => 'test@test.com'
        ]);
        
        TenantContext::getInstance()->setTenant($empresaId);
        
        $usuarioModel = new Usuario();
        $usuarioId = $usuarioModel->create([
            'nombre' => 'Usuario Test',
            'email' => 'usuario@test.com',
            'password_hash' => password_hash('test123', PASSWORD_ARGON2ID),
            'rol' => 'admin_empresa',
            'estado' => 'activo'
        ]);
        
        $controlModel = new Control();
        $controles = $controlModel->findAll([], 5, 0);
        
        $soaModel = new SOA();
        $soaCreated = 0;
        
        foreach ($controles as $control) {
            $soaModel->create([
                'control_id' => $control['id'],
                'aplicable' => 1,
                'estado' => 'no_implementado',
                'justificacion' => 'Control aplicable para la organización'
            ]);
            $soaCreated++;
        }
        
        TenantContext::getInstance()->clearTenant();
        $usuarioSinTenant = $usuarioModel->find($usuarioId);
        
        TenantContext::getInstance()->setTenant($empresaId);
        $usuarioConTenant = $usuarioModel->find($usuarioId);
        
        $progreso = $soaModel->calculateProgress();
        
        $response->json([
            'status' => 'success',
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'soa_created' => $soaCreated,
            'usuario_sin_tenant' => $usuarioSinTenant === null ? 'CORRECTO: No accesible' : 'ERROR: Accesible',
            'usuario_con_tenant' => $usuarioConTenant !== null ? 'CORRECTO: Accesible' : 'ERROR: No accesible',
            'progreso_soa' => $progreso
        ]);
        
    } catch (\Exception $e) {
        $log->error('Tenant test failed', ['error' => $e->getMessage()]);
        $response->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
});

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
