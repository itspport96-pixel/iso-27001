<?php
declare(strict_types=1);

use App\Services\AuthService;
use App\Core\{Session, Database};

require_once __DIR__ . '/../vendor/autoload.php';

Session::start();
$auth = new AuthService();

// 1. hash
$hash = $auth->hashPassword('Test1234');
echo 'Hash generado: ' . substr($hash, 0, 20) . "...\n";

// 2. login fallido
$user = $auth->login('no@existe.com', 'wrong', 1);
echo 'Login wrong: ' . ($user ? 'OK ❌' : 'FAIL ✅') . "\n";

// 3. login correcto (usa usuario seed admin)
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare('UPDATE usuarios SET password_hash = :hash WHERE email = :email');
$stmt->execute(['hash' => $hash, 'email' => 'admin@entropic.pe']);

$ok = $auth->login('admin@entropic.pe', 'Test1234', 1);
echo 'Login ok: ' . ($ok ? 'OK ✅' : 'FAIL ❌') . "\n";

// 4. session
echo 'Sesión user_id: ' . Session::get('user_id') . "\n";
echo 'Rol: ' . Session::get('rol') . "\n";
