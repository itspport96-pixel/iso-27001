<?php
declare(strict_types=1);

use App\Core\{Database, TenantContext};
use App\Repositories\SOARepository;

require_once __DIR__ . '/../vendor/autoload.php';

// Simula empresa 1 y 2
TenantContext::setTenant(1);
$repo = new SOARepository();
$lista1 = $repo->listByEmpresa(1, 5);

TenantContext::setTenant(2);
$lista2 = $repo->listByEmpresa(2, 5);

echo 'Empresa 1: ' . count($lista1) . " registros\n";
echo 'Empresa 2: ' . count($lista2) . " registros\n";

// Prueba IDOR
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare('SELECT id FROM soa_entries WHERE empresa_id = 1 LIMIT 1');
$stmt->execute();
$idEmpresa1 = $stmt->fetchColumn();

if ($idEmpresa1 === false) {
    echo "No hay datos de prueba aún; salto de IDOR omitido.\n";
    exit(0);
}

TenantContext::setTenant(2);
$mal = $repo->find($idEmpresa1, 2); // debe devolver null
echo 'Intento IDOR: ' . ($mal ? 'PERMITIDO ❌' : 'BLOQUEADO ✅') . "\n";
