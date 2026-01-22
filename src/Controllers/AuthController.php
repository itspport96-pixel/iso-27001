<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Request, Response, Session};
use App\Services\{AuthService, RateLimitService};
use App\Repositories\{EmpresaRepository, UsuarioRepository};
use App\Models\{Empresa, Usuario};
use PDO;

final class AuthController
{
    private AuthService $auth;
    private RateLimitService $rate;
    private EmpresaRepository $empresaRepo;
    private UsuarioRepository $userRepo;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->rate = new RateLimitService();
        $this->empresaRepo = new EmpresaRepository();
        $this->userRepo = new UsuarioRepository();
    }

    public function showRegister(Request $req): void
    {
        (new Response())->view('auth/register');
    }

    public function register(Request $req): void
    {
	file_put_contents('/tmp/debug.txt', json_encode($req->all()) . PHP_EOL, FILE_APPEND);
        $errors = (new \App\Core\Validator($req->all()))
            ->required('ruc', 'razon_social', 'email', 'password', 'password_confirmation')
            ->email('email')
            ->min('password', 8)
            ->confirmed('password')
            ->errors();

        if ($errors) {
            (new Response())->json(['errors' => $errors], 422);
        }

        if ($this->empresaRepo->existsByRuc($req->input('ruc'))) {
            (new Response())->json(['errors' => ['ruc' => 'RUC ya registrado']], 422);
        }

        $db = \App\Core\Database::getInstance()->getConnection();
        $db->beginTransaction();

        try {
            $empresaId = Empresa::create([
                'ruc'           => $req->input('ruc'),
                'razon_social'  => $req->input('razon_social'),
                'contacto'      => json_encode(['email' => $req->input('email')]),
            ]);

            Usuario::create([
                'empresa_id'    => $empresaId,
                'nombre'        => 'Administrador',
                'email'         => $req->input('email'),
                'password_hash' => $this->auth->hashPassword($req->input('password')),
                'rol'           => 'admin_empresa',
            ]);

            // Trigger automático ya creó 93 SOA + 7 reqs
            $db->commit();
            (new Response())->json(['message' => 'Empresa creada. Inicie sesión.']);
        } catch (\Throwable $e) {
            $db->rollBack();
            (new Response())->status(500)->json(['error' => 'No se pudo completar el registro']);
        }
    }

    public function showLogin(Request $req): void
    {
        (new Response())->view('auth/login');
    }

    file_put_contents("/tmp/csrf.log", date("Y-m-d H:i:s") . " POST=" . json_encode($_POST) . " SESSION=" . json_encode($_SESSION) . PHP_EOL, FILE_APPEND);
    public function login(Request $req): void
    {
        $key = 'login:' . $req->ip() . ':' . $req->input('email');
        if ($this->rate->tooManyAttempts($key, 5, 15)) {
            (new Response())->json(['error' => 'Demasiados intentos. Espere 15 min.'], 429);
        }

        $user = $this->auth->login(
            $req->input('email'),
            $req->input('password'),
            (int)$req->input('empresa_id')
        );

        if (!$user) {
            (new Response())->json(['error' => 'Credenciales incorrectas'], 401);
        }

        $this->rate->clear($key);
        (new Response())->json(['message' => 'Autenticado']);
    }

    public function logout(Request $req): void
    {
        $this->auth->logout();
        (new Response())->redirect('/login');
    }
}
