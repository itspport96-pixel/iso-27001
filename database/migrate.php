<?php
declare(strict_types=1);

use App\Core\Migration;

require_once __DIR__ . '/../vendor/autoload.php';

$migration = new Migration();

echo "[1/4] Creando esquema...\n";
$migration->run(__DIR__ . '/schema.sql');

echo "[2/4] Insertando controles ISO...\n";
$migration->seed(__DIR__ . '/seeds/01_controles_iso27001_2022.sql');

echo "[3/4] Insertando requerimientos base...\n";
$migration->seed(__DIR__ . '/seeds/02_requerimientos_base.sql');

echo "[4/4] Insertando super admin...\n";
$migration->seed(__DIR__ . '/seeds/03_super_admin.sql');

echo "[5/4] Aplicando triggers de avance...\n";
$migration->run(__DIR__ . '/migrations/2026_01_22_000001_create_triggers_gap_avance.sql');

echo "[6/4] Aplicando triggers de completitud...\n";
$migration->run(__DIR__ . '/migrations/2026_01_22_000002_create_triggers_requerimientos_completitud.sql');

echo "Migración finalizada.\n";
