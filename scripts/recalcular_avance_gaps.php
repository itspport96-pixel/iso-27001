<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Database;

// Cargar variables de entorno (el .env está en la raíz del proyecto)
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Obtener conexión a la base de datos
$db = Database::getInstance()->getConnection();

echo "Recalculando avance de todos los GAPs...\n\n";

// Obtener todos los GAPs activos
$sql = "SELECT id FROM gap_items WHERE estado_gap = 'activo'";
$stmt = $db->query($sql);
$gaps = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = count($gaps);
$procesados = 0;

foreach ($gaps as $gap) {
    $gapId = $gap['id'];
    
    // Calcular estadísticas de acciones
    $sqlAcciones = "SELECT 
                    COUNT(*) as total_acciones,
                    SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) as acciones_completadas
                    FROM acciones
                    WHERE gap_id = :gap_id 
                    AND estado_accion = 'activo'";
    
    $stmtAcciones = $db->prepare($sqlAcciones);
    $stmtAcciones->bindValue(':gap_id', $gapId, PDO::PARAM_INT);
    $stmtAcciones->execute();
    
    $stats = $stmtAcciones->fetch(PDO::FETCH_ASSOC);
    
    $avance = 0;
    if ($stats && $stats['total_acciones'] > 0) {
        $avance = round(($stats['acciones_completadas'] / $stats['total_acciones']) * 100, 2);
    }
    
    // Actualizar el avance
    $updateSql = "UPDATE gap_items 
                  SET avance = :avance, 
                      updated_at = NOW() 
                  WHERE id = :gap_id";
    
    $updateStmt = $db->prepare($updateSql);
    $updateStmt->bindValue(':avance', $avance);
    $updateStmt->bindValue(':gap_id', $gapId, PDO::PARAM_INT);
    $updateStmt->execute();
    
    $procesados++;
    echo "GAP ID {$gapId}: {$stats['acciones_completadas']}/{$stats['total_acciones']} acciones completadas -> Avance: {$avance}%\n";
}

echo "\n✓ Procesados {$procesados} de {$total} GAPs\n";
echo "✓ Recálculo completado exitosamente\n";
