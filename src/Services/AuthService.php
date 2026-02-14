<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\Empresa;
use App\Core\Session;
use App\Core\TenantContext;
use App\Core\Database;

class AuthService
{
    private Session $session;
    private LogService $log;
    private PasswordPolicyService $passwordPolicy;

    public function __construct()
    {
        $this->session = new Session();
        $this->log     = new LogService();
        $this->passwordPolicy = new PasswordPolicyService();
    }

    public function register(array $empresaData, array $usuarioData): array
    {
        $db = Database::getInstance()->getConnection();

        try {
            $db->beginTransaction();

            // Validar RUC unico
            $empresaModel = new Empresa();
            if ($empresaModel->rucExists($empresaData['ruc'])) {
                throw new \Exception('El RUC ya esta registrado');
            }

            // Crear empresa
            $empresaId = $empresaModel->create([
                'nombre'    => $empresaData['nombre'],
                'ruc'       => $empresaData['ruc'],
                'contacto'  => $empresaData['contacto'] ?? null,
                'telefono'  => $empresaData['telefono'] ?? null,
                'email'     => $empresaData['email'] ?? null,
                'direccion' => $empresaData['direccion'] ?? null,
            ]);

            if (!$empresaId) {
                throw new \Exception('Error al crear empresa');
            }

            // Establecer contexto de tenant
            TenantContext::getInstance()->setTenant($empresaId);

            // Crear usuario admin
            $usuarioModel = new Usuario();
            $usuarioId    = $usuarioModel->create([
                'nombre'        => $usuarioData['nombre'],
                'email'         => $usuarioData['email'],
                'password_hash' => password_hash($usuarioData['password'], PASSWORD_ARGON2ID),
                'rol'           => 'admin_empresa',
                'estado'        => 'activo',
            ]);

            if (!$usuarioId) {
                throw new \Exception('Error al crear usuario');
            }

            // Crear SOA entries iniciales
            $stmt = $db->prepare("
                INSERT INTO soa_entries (empresa_id, control_id, aplicable, estado, created_at, updated_at)
                SELECT :empresa_id, id, 1, 'no_implementado', NOW(), NOW()
                FROM controles
            ");
            $stmt->execute([':empresa_id' => $empresaId]);

            // Crear empresa_requerimientos iniciales
            $stmt = $db->prepare("
                INSERT INTO empresa_requerimientos (empresa_id, requerimiento_id, estado, created_at, updated_at)
                SELECT :empresa_id, id, 'pendiente', NOW(), NOW()
                FROM requerimientos_base
            ");
            $stmt->execute([':empresa_id' => $empresaId]);

            $db->commit();

            $this->log->info('Registro exitoso', [
                'empresa_id' => $empresaId,
                'usuario_id' => $usuarioId,
                'ruc'        => $empresaData['ruc'],
            ]);

            return [
                'success'    => true,
                'empresa_id' => $empresaId,
                'usuario_id' => $usuarioId,
            ];

        } catch (\Exception $e) {
            $db->rollBack();

            $this->log->error('Error en registro', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    public function loginByEmail(string $email, string $password): array
    {
        try {
            $db = Database::getInstance()->getConnection();

            // JOIN con empresas para obtener nombre en una sola query
            $sql = "SELECT u.*, e.nombre AS empresa_nombre
                    FROM usuarios u
                    INNER JOIN empresas e ON e.id = u.empresa_id
                    WHERE u.email = :email AND u.deleted_at IS NULL
                    LIMIT 1";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':email', $email);
            $stmt->execute();

            $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$usuario) {
                $this->log->security('Login fallido - usuario no existe', ['email' => $email]);
                return ['success' => false, 'error' => 'Credenciales invalidas'];
            }

            // Establecer contexto de tenant
            TenantContext::getInstance()->setTenant($usuario['empresa_id']);

            // Verificar si esta bloqueado
            if ($usuario['bloqueado_hasta'] && strtotime($usuario['bloqueado_hasta']) > time()) {
                $this->log->security('Login bloqueado', [
                    'usuario_id'      => $usuario['id'],
                    'bloqueado_hasta' => $usuario['bloqueado_hasta'],
                ]);
                return ['success' => false, 'error' => 'Usuario bloqueado temporalmente'];
            }

            // Verificar estado
            if ($usuario['estado'] !== 'activo') {
                return ['success' => false, 'error' => 'Usuario inactivo'];
            }

            // Verificar password
            if (!password_verify($password, $usuario['password_hash'])) {
                $usuarioModel = new Usuario();
                $usuarioModel->incrementLoginAttempts($usuario['id']);

                $intentos = $usuario['intentos_login'] + 1;

                if ($intentos >= 5) {
                    $usuarioModel->blockUser($usuario['id'], 15);
                    $this->log->security('Usuario bloqueado por intentos', [
                        'usuario_id' => $usuario['id'],
                    ]);
                    return ['success' => false, 'error' => 'Usuario bloqueado por 15 minutos'];
                }

                $this->log->security('Login fallido - password incorrecta', [
                    'usuario_id' => $usuario['id'],
                    'intentos'   => $intentos,
                ]);

                return ['success' => false, 'error' => 'Credenciales invalidas'];
            }

            // Login exitoso
            $usuarioModel = new Usuario();
            $usuarioModel->resetLoginAttempts($usuario['id']);
            $usuarioModel->updateLastAccess($usuario['id']);

            // Crear sesion
            $this->session->regenerate();
            $this->session->set('user_id',         $usuario['id']);
            $this->session->set('user_email',       $usuario['email']);
            $this->session->set('user_nombre',      $usuario['nombre']);
            $this->session->set('user_rol',         $usuario['rol']);
            $this->session->set('empresa_id',       $usuario['empresa_id']);
            $this->session->set('empresa_nombre',   $usuario['empresa_nombre']);
            $this->session->set('last_activity',    time());
            
            // Verificar si debe cambiar contraseña (flag manual o expiración)
            $debeCambiarPassword = isset($usuario['debe_cambiar_password']) && $usuario['debe_cambiar_password'] == 1;
            
            // Verificar expiración de contraseña
            $passwordExpired = $this->passwordPolicy->isPasswordExpired($usuario['password_updated_at'] ?? null);
            if ($passwordExpired) {
                $debeCambiarPassword = true;
            }
            
            $this->session->set('debe_cambiar_password', $debeCambiarPassword);
            
            // Verificar si debe advertir sobre expiración próxima
            $passwordWarning = false;
            $daysUntilExpiration = 0;
            if (!$debeCambiarPassword) {
                $passwordWarning = $this->passwordPolicy->shouldWarnExpiration($usuario['password_updated_at'] ?? null);
                if ($passwordWarning) {
                    $daysUntilExpiration = $this->passwordPolicy->getDaysUntilExpiration($usuario['password_updated_at'] ?? null);
                }
            }
            $this->session->set('password_expiration_warning', $passwordWarning);
            $this->session->set('password_days_remaining', $daysUntilExpiration);

            $this->log->info('Login exitoso', [
                'usuario_id' => $usuario['id'],
                'empresa_id' => $usuario['empresa_id'],
                'debe_cambiar_password' => $debeCambiarPassword,
                'password_expired' => $passwordExpired,
            ]);

            return ['success' => true, 'debe_cambiar_password' => $debeCambiarPassword];

        } catch (\Exception $e) {
            $this->log->error('Error en login', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Error interno'];
        }
    }

    public function logout(): void
    {
        $userId = $this->session->get('user_id');

        $this->log->info('Logout', ['usuario_id' => $userId]);

        $this->session->destroy();
        TenantContext::getInstance()->clearTenant();
    }

    public function check(): bool
    {
        return $this->session->has('user_id') && $this->session->has('empresa_id');
    }

    public function user(): ?array
    {
        if (!$this->check()) {
            return null;
        }

        return [
            'id'             => $this->session->get('user_id'),
            'email'          => $this->session->get('user_email'),
            'nombre'         => $this->session->get('user_nombre'),
            'rol'            => $this->session->get('user_rol'),
            'empresa_id'     => $this->session->get('empresa_id'),
            'empresa_nombre' => $this->session->get('empresa_nombre'),
        ];
    }
}
