<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();

include __DIR__ . '/../components/badge.php';
include __DIR__ . '/../components/alert.php';
include __DIR__ . '/../components/card.php';
include __DIR__ . '/../components/progress-bar.php';
include __DIR__ . '/../components/modal.php';
include __DIR__ . '/../components/form/input.php';
include __DIR__ . '/../components/form/select.php';
include __DIR__ . '/../components/form/textarea.php';

// Función helper para estado de certificación
function getEstadoCertificacionBadge(string $estado, float $avance): array
{
    $estados = [
        'cerrado' => [
            'icon' => '<svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
            'text' => 'GAP Cerrado',
            'description' => 'Acciones completadas y evidencia aprobada',
            'bgColor' => 'bg-green-50',
            'borderColor' => 'border-green-200',
            'textColor' => 'text-green-800',
            'iconColor' => 'text-green-600'
        ],
        'en_validacion' => [
            'icon' => '<svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>',
            'text' => 'En Validación',
            'description' => 'Acciones completadas - Pendiente de evidencia aprobada',
            'bgColor' => 'bg-yellow-50',
            'borderColor' => 'border-yellow-200',
            'textColor' => 'text-yellow-800',
            'iconColor' => 'text-yellow-600'
        ],
        'rechazado' => [
            'icon' => '<svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
            'text' => 'Evidencia Rechazada',
            'description' => 'Se requiere nueva evidencia para cerrar el GAP',
            'bgColor' => 'bg-red-50',
            'borderColor' => 'border-red-200',
            'textColor' => 'text-red-800',
            'iconColor' => 'text-red-600'
        ],
        'abierto' => [
            'icon' => '<svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>',
            'text' => 'Abierto',
            'description' => 'Acciones en progreso - ' . number_format($avance, 0) . '% completado',
            'bgColor' => 'bg-gray-50',
            'borderColor' => 'border-gray-200',
            'textColor' => 'text-gray-800',
            'iconColor' => 'text-gray-600'
        ]
    ];
    
    return $estados[$estado] ?? $estados['abierto'];
}
?>

<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GAP - <?= htmlspecialchars($gap['codigo']) ?> - ISO 27001 Platform</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff', 100: '#e0f2fe', 200: '#bae6fd', 300: '#7dd3fc',
                            400: '#38bdf8', 500: '#0ea5e9', 600: '#0284c7', 700: '#0369a1',
                            800: '#075985', 900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-full">
    
    <div class="h-full flex flex-col">
        <?php include __DIR__ . '/../dashboard/header.php'; ?>
        
        <div class="flex flex-1 overflow-hidden">
            <?php include __DIR__ . '/../dashboard/sidebar.php'; ?>
            
            <main class="flex-1 overflow-y-auto p-6 lg:p-8">
                
                <nav class="flex mb-6" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="/dashboard" class="text-gray-500 hover:text-gray-700">Dashboard</a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                                <a href="/gaps" class="ml-1 text-gray-500 hover:text-gray-700">GAPs</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                                <span class="ml-1 text-gray-700 font-medium"><?= htmlspecialchars($gap['codigo']) ?></span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <div class="mb-8">
                    <div class="sm:flex sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">GAP: <?= htmlspecialchars($gap['codigo']) ?></h1>
                            <p class="mt-2 text-sm text-gray-600"><?= htmlspecialchars($gap['control_nombre']) ?></p>
                        </div>
                        <div class="mt-4 sm:mt-0 flex space-x-3">
                            <button onclick="openModal('editModal')" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Editar GAP
                            </button>
                            <button onclick="eliminarGap(<?= $gap['id'] ?>)" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition">
                                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Eliminar
                            </button>
                        </div>
                    </div>
                </div>

                <?php 
                $estadoCert = getEstadoCertificacionBadge($gap['estado_certificacion'], $gap['avance']);
                if ($gap['estado_certificacion'] === 'en_validacion' || $gap['estado_certificacion'] === 'rechazado'): 
                ?>
                    <div class="mb-6 border-l-4 <?= $gap['estado_certificacion'] === 'rechazado' ? 'border-red-400 bg-red-50' : 'border-yellow-400 bg-yellow-50' ?> p-4 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <?= $estadoCert['icon'] ?>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium <?= $estadoCert['textColor'] ?>"><?= $estadoCert['text'] ?></h3>
                                <div class="mt-2 text-sm <?= $estadoCert['textColor'] ?>">
                                    <p><?= $estadoCert['description'] ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <div class="lg:col-span-2 space-y-6">
                        
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Detalles de la Brecha</h3>
                            </div>
                            <div class="px-6 py-5">
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?= nl2br(htmlspecialchars($gap['brecha'])) ?></dd>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Impacto</dt>
                                            <dd class="mt-1">
                                                <?php
                                                $impactoVariant = $gap['impacto'] === 'critico' ? 'error' : 
                                                                ($gap['impacto'] === 'alto' ? 'error' :
                                                                ($gap['impacto'] === 'medio' ? 'warning' : 'info'));
                                                echo renderBadge(ucfirst($gap['impacto']), $impactoVariant);
                                                ?>
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Prioridad</dt>
                                            <dd class="mt-1"><?= getPrioridadBadge($gap['prioridad']) ?></dd>
                                        </div>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Fecha Objetivo</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            <?= $gap['fecha_objetivo'] ? date('d/m/Y', strtotime($gap['fecha_objetivo'])) : 'No definida' ?>
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 mb-3">Estado de Certificación</dt>
                                        <dd>
                                            <div class="border-2 <?= $estadoCert['borderColor'] ?> <?= $estadoCert['bgColor'] ?> rounded-lg p-4">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 <?= $estadoCert['iconColor'] ?>">
                                                        <?= $estadoCert['icon'] ?>
                                                    </div>
                                                    <div class="ml-3 flex-1">
                                                        <p class="text-sm font-semibold <?= $estadoCert['textColor'] ?>"><?= $estadoCert['text'] ?></p>
                                                        <p class="text-xs <?= $estadoCert['textColor'] ?> mt-1"><?= $estadoCert['description'] ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Plan de Acción</h3>
                            </div>
                            <div class="px-6 py-5">
                                <?php if (empty($acciones)): ?>
                                    <div class="text-center py-8">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay acciones definidas</h3>
                                        <p class="mt-1 text-sm text-gray-500">Las acciones se definen al crear el GAP.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="space-y-4">
                                        <?php foreach ($acciones as $accion): ?>
                                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center">
                                                        <h4 class="text-sm font-medium text-gray-900"><?= htmlspecialchars($accion['descripcion']) ?></h4>
                                                        <span class="ml-2">
                                                            <?php
                                                            $estadoVariant = $accion['estado'] === 'completada' ? 'success' :
                                                                           ($accion['estado'] === 'vencida' ? 'error' :
                                                                           ($accion['estado'] === 'en_proceso' ? 'info' : 'gray'));
                                                            echo renderBadge(ucfirst(str_replace('_', ' ', $accion['estado'])), $estadoVariant, 'sm');
                                                            ?>
                                                        </span>
                                                    </div>
                                                    <div class="mt-2 flex items-center text-sm text-gray-500 space-x-4">
                                                        <?php if ($accion['responsable']): ?>
                                                        <span class="flex items-center">
                                                            <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                            </svg>
                                                            <?= htmlspecialchars($accion['responsable']) ?>
                                                        </span>
                                                        <?php endif; ?>
                                                        <span class="flex items-center">
                                                            <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                            </svg>
                                                            <?= date('d/m/Y', strtotime($accion['fecha_compromiso'])) ?>
                                                        </span>
                                                    </div>
                                                    <?php if ($accion['notas']): ?>
                                                    <p class="mt-2 text-sm text-gray-600"><?= htmlspecialchars($accion['notas']) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="ml-4 flex-shrink-0 flex space-x-2">
                                                    <?php if ($accion['estado'] !== 'completada'): ?>
                                                    <button onclick="completarAccion(<?= $accion['id'] ?>)" class="text-green-600 hover:text-green-900" title="Completar">
                                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    </button>
                                                    <?php endif; ?>
                                                    <button onclick="openEditAccionModal(<?= $accion['id'] ?>, '<?= htmlspecialchars($accion['estado'], ENT_QUOTES) ?>', '<?= htmlspecialchars($accion['notas'] ?? '', ENT_QUOTES) ?>')" class="text-primary-600 hover:text-primary-900" title="Editar">
                                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Evidencias del Control</h3>
                            </div>
                            <div class="px-6 py-5">
                                <?php if (empty($evidencias)): ?>
                                    <div class="text-center py-8">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay evidencias</h3>
                                        <p class="mt-1 text-sm text-gray-500">Sube evidencias para demostrar el cumplimiento.</p>
                                        <div class="mt-4">
                                            <a href="/evidencias/create?control_id=<?= $gap['control_id'] ?>" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                                                Subir Evidencia
                                            </a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="space-y-3">
                                        <?php foreach ($evidencias as $evidencia): ?>
                                        <div class="flex items-center justify-between border border-gray-200 rounded-lg p-3">
                                            <div class="flex items-center">
                                                <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($evidencia['nombre_archivo']) ?></p>
                                                    <p class="text-xs text-gray-500"><?= date('d/m/Y', strtotime($evidencia['created_at'])) ?></p>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <?php
                                                $estadoVariant = $evidencia['estado_validacion'] === 'aprobada' ? 'success' :
                                                               ($evidencia['estado_validacion'] === 'rechazada' ? 'error' : 'warning');
                                                echo renderBadge(ucfirst($evidencia['estado_validacion']), $estadoVariant, 'sm');
                                                ?>
                                                <a href="/evidencias/<?= $evidencia['id'] ?>" class="text-primary-600 hover:text-primary-900">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>

                    <div class="space-y-6">
                        
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Control Asociado</h3>
                            </div>
                            <div class="px-6 py-5">
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Código</p>
                                        <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($gap['codigo']) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Nombre</p>
                                        <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($gap['control_nombre']) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Dominio</p>
                                        <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($gap['dominio_nombre']) ?></p>
                                    </div>
                                    <div class="pt-3">
                                        <a href="/controles/<?= $gap['soa_id'] ?>" class="text-sm font-medium text-primary-600 hover:text-primary-700">
                                            Ver control completo →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Resumen</h3>
                            </div>
                            <div class="px-6 py-5 space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Total acciones</span>
                                    <span class="text-sm font-semibold text-gray-900"><?= count($acciones) ?></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Completadas</span>
                                    <span class="text-sm font-semibold text-green-600">
                                        <?= count(array_filter($acciones, fn($a) => $a['estado'] === 'completada')) ?>
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Pendientes</span>
                                    <span class="text-sm font-semibold text-yellow-600">
                                        <?= count(array_filter($acciones, fn($a) => $a['estado'] === 'pendiente')) ?>
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Vencidas</span>
                                    <span class="text-sm font-semibold text-red-600">
                                        <?= count(array_filter($acciones, fn($a) => $a['estado'] === 'vencida')) ?>
                                    </span>
                                </div>
                                <div class="pt-3 border-t border-gray-200">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Evidencias</span>
                                        <span class="text-sm font-semibold text-gray-900"><?= count($evidencias) ?></span>
                                    </div>
                                    <div class="flex justify-between items-center mt-2">
                                        <span class="text-sm text-gray-600">Estado de evidencia</span>
                                        <?php if ($tiene_evidencia_aprobada): ?>
                                            <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        <?php else: ?>
                                            <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </main>
        </div>
    </div>

<div id="editModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal('editModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="editGapForm" onsubmit="updateGap(event)">
                <div class="bg-white px-6 py-5">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Editar GAP</h3>
                    
                    <?php
                    echo renderTextarea(
                        'brecha',
                        'Descripción de la Brecha',
                        $gap['brecha'],
                        ['required' => true, 'rows' => 4]
                    );
                    ?>
                    
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <?php
                        echo renderSelect(
                            'impacto',
                            'Impacto',
                            ['critico' => 'Crítico', 'alto' => 'Alto', 'medio' => 'Medio', 'bajo' => 'Bajo'],
                            $gap['impacto'],
                            ['required' => true]
                        );
                        
                        echo renderSelect(
                            'prioridad',
                            'Prioridad',
                            ['alta' => 'Alta', 'media' => 'Media', 'baja' => 'Baja'],
                            $gap['prioridad'],
                            ['required' => true]
                        );
                        ?>
                    </div>
                    
                    <div class="mt-4">
                        <?php
                        echo renderInput(
                            'fecha_objetivo',
                            'date',
                            'Fecha Objetivo',
                            $gap['fecha_objetivo'],
                            ['min' => date('Y-m-d')]
                        );
                        ?>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('editModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="editAccionModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal('editAccionModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="editAccionForm" onsubmit="updateAccion(event)">
                <input type="hidden" id="accion_id" name="accion_id">
                <div class="bg-white px-6 py-5">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Editar Acción</h3>
                    
                    <?php
                    echo renderSelect(
                        'estado',
                        'Estado',
                        ['pendiente' => 'Pendiente', 'en_proceso' => 'En Proceso', 'completada' => 'Completada', 'vencida' => 'Vencida'],
                        '',
                        ['required' => true]
                    );
                    ?>
                    
                    <div class="mt-4">
                        <?php
                        echo renderTextarea(
                            'notas',
                            'Notas',
                            '',
                            ['rows' => 3, 'placeholder' => 'Agregar notas sobre el estado de la acción...']
                        );
                        ?>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('editAccionModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                        Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= renderModalScript() ?>

<script>
function eliminarGap(id) {
    if (!confirm('¿Está seguro de eliminar este GAP? Esta acción marcará el GAP y sus acciones como eliminadas.')) {
        return;
    }
    
    fetch('/gaps/' + id + '/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({csrf_token: '<?= $csrfToken ?>'})
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            alert('GAP eliminado exitosamente');
            window.location.href = '/gaps';
        } else {
            alert('Error: ' + (result.error || 'No se pudo eliminar el GAP'));
        }
    })
    .catch(err => {
        alert('Error de conexión con el servidor');
    });
}

function updateGap(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    formData.append('csrf_token', '<?= $csrfToken ?>');
    
    fetch('/gaps/<?= $gap['id'] ?>/update', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(formData)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            alert('GAP actualizado exitosamente');
            window.location.reload();
        } else {
            alert('Error: ' + (result.error || 'No se pudo actualizar'));
        }
    })
    .catch(err => {
        alert('Error de conexión con el servidor');
    });
}

function completarAccion(id) {
    if (!confirm('¿Marcar esta acción como completada?')) {
        return;
    }
    
    fetch('/gaps/accion/' + id + '/completar', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({csrf_token: '<?= $csrfToken ?>'})
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            alert('Acción completada');
            window.location.reload();
        } else {
            alert('Error: ' + (result.error || 'No se pudo completar'));
        }
    });
}

function openEditAccionModal(id, estado, notas) {
    document.getElementById('accion_id').value = id;
    document.getElementById('select_estado').value = estado;
    document.getElementById('textarea_notas').value = notas || '';
    openModal('editAccionModal');
}

function updateAccion(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const accionId = formData.get('accion_id');
    formData.append('csrf_token', '<?= $csrfToken ?>');
    
    fetch('/gaps/accion/' + accionId + '/update', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(formData)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            alert('Acción actualizada exitosamente');
            window.location.reload();
        } else {
            alert('Error: ' + (result.error || 'No se pudo actualizar'));
        }
    })
    .catch(err => {
        alert('Error de conexión con el servidor');
    });
}
</script>

</body>
</html>
