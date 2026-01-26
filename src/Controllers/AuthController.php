<?php

namespace App\Controllers;

use App\Controllers\Base\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Services\AuthService;
use App\Services\AuditService;
use App\Middleware\CsrfMiddleware;

class AuthController extends Controller
{
    private AuditService $auditService;

    public function __construct()
    {
        parent::__construct();
        $this->auditService = new AuditService();
    }

    public function showRegister(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
            return;
        }
        
        $this->view('auth/register', [], 'auth-wide');
    }

    public function register(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        
        $validator = new Validator($request->all());
        
        $rules = [
            'empresa_nombre' => 'required|min:3|max:255',
            'empresa_ruc' => 'required|min:11|max:20|alphanumeric',
            'empresa_email' => 'required|email',
            'usuario_nombre' => 'required|min:3|max:255',
            'usuario_email' => 'required|email',
            'password' => 'required|min:8',
            'password_confirm' => 'required'
        ];
        
        if (!$validator->validate($rules)) {
            $this->json(['success' => false, 'errors' => $validator->errors()], 400);
            return;
        }
        
        if ($request->post('password') !== $request->post('password_confirm')) {
            $this->json(['success' => false, 'error' => 'Las contraseñas no coinciden'], 400);
            return;
        }
        
        $empresaData = [
            'nombre' => $request->post('empresa_nombre'),
            'ruc' => $request->post('empresa_ruc'),
            'contacto' => $request->post('empresa_contacto'),
            'telefono' => $request->post('empresa_telefono'),
            'email' => $request->post('empresa_email'),
            'direccion' => $request->post('empresa_direccion')
        ];
        
        $usuarioData = [
            'nombre' => $request->post('usuario_nombre'),
            'email' => $request->post('usuario_email'),
            'password' => $request->post('password')
        ];
        
        $auth = new AuthService();
        $result = $auth->register($empresaData, $usuarioData);
        
        if ($result['success']) {
            $this->session->flash('success', 'Registro exitoso. Por favor inicia sesión.');
            $this->json(['success' => true, 'redirect' => '/login']);
        } else {
            $this->json(['success' => false, 'error' => $result['error']], 400);
        }
    }

    public function showLogin(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
            return;
        }
        
        $this->view('auth/login', [], 'auth');
    }

    public function login(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        
        $validator = new Validator($request->all());
        
        $rules = [
            'email' => 'required|email',
            'password' => 'required'
        ];
        
        if (!$validator->validate($rules)) {
            $this->json(['success' => false, 'errors' => $validator->errors()], 400);
            return;
        }
        
        $auth = new AuthService();
        $result = $auth->loginByEmail(
            $request->post('email'),
            $request->post('password')
        );
        
        if ($result['success']) {
            $userId = $this->session->get('user_id');
            $empresaId = $this->session->get('empresa_id');
            
            $this->auditService->logLogin($userId, $empresaId, true);
            
            $this->json(['success' => true, 'redirect' => '/dashboard']);
        } else {
            $this->json(['success' => false, 'error' => $result['error']], 401);
        }
    }

    public function logout(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        
        if ($this->session->has('user_id')) {
            $this->auditService->logLogout();
        }
        
        $auth = new AuthService();
        $auth->logout();
        
        $this->redirect('/login');
    }
}
