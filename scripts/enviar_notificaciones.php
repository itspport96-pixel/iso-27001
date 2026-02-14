#!/usr/bin/env php
<?php
/**
 * Script para enviar notificaciones automáticas
 * Ejecutar con cron: 0 8 * * * php /var/www/html/scripts/enviar_notificaciones.php
 * 
 * Uso manual:
 *   php scripts/enviar_notificaciones.php              # Todas las empresas
 *   php scripts/enviar_notificaciones.php --empresa=1  # Solo empresa ID 1
 *   php scripts/enviar_notificaciones.php --test       # Modo prueba (no envía)
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Database;
use App\Services\NotificacionService;
use App\Services\LogService;

// Cargar variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$log = new LogService();

// Parsear argumentos
$options = getopt('', ['empresa:', 'test', 'help']);

if (isset($options['help'])) {
    echo "Uso: php enviar_notificaciones.php [opciones]\n";
    echo "Opciones:\n";
    echo "  --empresa=ID   Enviar solo para una empresa específica\n";
    echo "  --test         Modo prueba (muestra qué se enviaría sin enviar)\n";
    echo "  --help         Muestra esta ayuda\n";
    exit(0);
}

$empresaId = isset($options['empresa']) ? (int)$options['empresa'] : null;
$testMode = isset($options['test']);

echo "========================================\n";
echo "SISTEMA DE NOTIFICACIONES ISO 27001\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "Modo: " . ($testMode ? "PRUEBA" : "PRODUCCION") . "\n";
echo "========================================\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Obtener empresas a procesar
    if ($empresaId) {
        $sql = "SELECT id, nombre FROM empresas WHERE id = :id AND deleted_at IS NULL";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $empresaId, PDO::PARAM_INT);
    } else {
        $sql = "SELECT id, nombre FROM empresas WHERE deleted_at IS NULL ORDER BY id";
        $stmt = $db->prepare($sql);
    }
    $stmt->execute();
    $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($empresas)) {
        echo "No se encontraron empresas para procesar.\n";
        exit(1);
    }
    
    $notificacionService = new NotificacionService();
    $totalEnviados = 0;
    $totalErrores = 0;
    
    foreach ($empresas as $empresa) {
        echo "Procesando: {$empresa['nombre']} (ID: {$empresa['id']})\n";
        echo str_repeat('-', 40) . "\n";
        
        if ($testMode) {
            // Modo prueba: solo mostrar qué se enviaría
            echo "  [PRUEBA] Acciones próximas a vencer:\n";
            $acciones = $notificacionService->getAccionesProximasVencer($empresa['id'], 7);
            echo "    - " . count($acciones) . " accion(es) encontrada(s)\n";
            
            echo "  [PRUEBA] Acciones vencidas:\n";
            $vencidas = $notificacionService->getAccionesVencidas($empresa['id']);
            echo "    - " . count($vencidas) . " accion(es) vencida(s)\n";
            
            echo "  [PRUEBA] Evidencias pendientes:\n";
            $evidencias = $notificacionService->getEvidenciasPendientes($empresa['id']);
            echo "    - " . count($evidencias) . " evidencia(s) pendiente(s)\n";
            
            echo "  [PRUEBA] Contraseñas próximas a expirar:\n";
            $passwords = $notificacionService->getPasswordsProximasExpirar($empresa['id']);
            echo "    - " . count($passwords) . " usuario(s)\n";
        } else {
            // Modo producción: enviar notificaciones
            $resultado = $notificacionService->enviarTodasNotificaciones($empresa['id']);
            
            $enviados = ($resultado['acciones']['enviados'] ?? 0) + ($resultado['evidencias']['enviados'] ?? 0);
            $errores = ($resultado['acciones']['errores'] ?? 0) + ($resultado['evidencias']['errores'] ?? 0);
            
            $totalEnviados += $enviados;
            $totalErrores += $errores;
            
            echo "  Enviados: {$enviados}\n";
            echo "  Errores: {$errores}\n";
            
            if (!empty($resultado['acciones']['detalle'])) {
                foreach ($resultado['acciones']['detalle'] as $detalle) {
                    echo "    - {$detalle}\n";
                }
            }
        }
        
        echo "\n";
    }
    
    echo "========================================\n";
    echo "RESUMEN\n";
    echo "========================================\n";
    echo "Empresas procesadas: " . count($empresas) . "\n";
    
    if (!$testMode) {
        echo "Total enviados: {$totalEnviados}\n";
        echo "Total errores: {$totalErrores}\n";
    }
    
    echo "Finalizado: " . date('Y-m-d H:i:s') . "\n";
    
    $log->info('Script notificaciones ejecutado', [
        'empresas' => count($empresas),
        'enviados' => $totalEnviados,
        'errores' => $totalErrores,
        'modo' => $testMode ? 'test' : 'produccion'
    ]);
    
    exit(0);
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    $log->error('Error en script notificaciones', ['error' => $e->getMessage()]);
    exit(1);
}
