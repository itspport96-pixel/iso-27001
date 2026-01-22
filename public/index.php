<?php
declare(strict_types=1);

use App\Core\{Request, Response, Session, Router};
use App\Middleware\{AuthMiddleware, RoleMiddleware, CsrfMiddleware, TenantMiddleware};
use App\Controllers\AuthController;

require_once __DIR__ . '/../vendor/autoload.php';

Session::start();

$router = new Router();
$csrf   = new CsrfMiddleware();

// Global middleware
$router->middleware('csrf', fn($req, $next) => $csrf->handle($req, $next));

// Auth routes (sin tenant ni auth)
$router->get('/login',    [AuthController::class, 'showLogin']);
$router->post('/login',   [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register',[AuthController::class, 'register']);
$router->post('/logout',  [AuthController::class, 'logout']);

// Protected routes (auth + tenant + roles)
$authMw = new AuthMiddleware();
$tenantMw = new TenantMiddleware();
$adminMw  = new RoleMiddleware(['super_admin','admin_empresa']);

$router->get('/dashboard', fn() => (new Response())->view('dashboard'), $authMw, $tenantMw);

// Health check público
$router->get('/health', fn() => (new Response())->json(['status' => 'ok']));

// Catch-all
$router->get('/', fn() => (new Response())->text('ISO 27001 Platform v2.0 – Fase 4 ready'));

$request = new Request();
$router->dispatch($request);
