<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();

// Incluir componentes
include __DIR__ . '/../components/badge.php';
include __DIR__ . '/../components/alert.php';
include __DIR__ . '/../components/card.php';
include __DIR__ . '/../components/table.php';

// Función helper para estado de certificación en lista
function getEstadoCertificacionBadgeCompacto(string $estado): array
{
    $estados = [
        'cerrado' => ['text' => 'Cerrado', 'variant' => 'success'],
        'en_validacion' => ['text' => 'En Validación', 'variant' => 'warning'],
        'rechazado' => ['text' => 'Rechazado', 'variant' => 'error'],
        'abierto' => ['text' => 'Abierto', 'variant' => 'gray']
    ];
    
    return $estados[$estado] ?? $estados['abierto'];
}
?>

<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis de Brechas - ISO 27001 Platform</title>
    
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
                
                <!-- Header -->
                <div class="mb-8">
                    <div class="sm:flex sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Análisis de Brechas (GAP Analysis)</h1>
                            <p class="mt-2 text-sm text-gray-600">Gestión de brechas de cumplimiento y planes de acción</p>
                        </div>
                        <div class="mt-4 sm:mt-0">
                            <a href="/gaps/create" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 transition">
                                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Nuevo GAP
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total GAPs</p>
                                <p class="text-2xl font-semibold text-gray-900"><?= $estadisticas['total'] ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Alta Prioridad</p>
                                <p class="text-2xl font-semibold text-gray-900"><?= $estadisticas['prioridad_alta'] ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Completados</p>
                                <p class="text-2xl font-semibold text-gray-900"><?= $estadisticas['completados'] ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Avance Promedio</p>
                                <p class="text-2xl font-semibold text-gray-900"><?= $estadisticas['avance_promedio'] ?>%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($estadisticas['vencidos'] > 0): ?>
                    <?= renderAlert(
                        "Tienes {$estadisticas['vencidos']} GAP(s) vencido(s) que requieren atención inmediata.",
                        'warning',
                        'Atención:',
                        true
                    ) ?>
                    <div class="mb-6"></div>
                <?php endif; ?>

                <!-- Filtros -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
                    <form method="GET" action="/gaps" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label for="filter_prioridad" class="block text-sm font-medium text-gray-700 mb-1">Prioridad</label>
                            <select name="prioridad" id="filter_prioridad" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                <option value="">Todas</option>
                                <option value="alta" <?= $filtro_prioridad === 'alta' ? 'selected' : '' ?>>Alta</option>
                                <option value="media" <?= $filtro_prioridad === 'media' ? 'selected' : '' ?>>Media</option>
                                <option value="baja" <?= $filtro_prioridad === 'baja' ? 'selected' : '' ?>>Baja</option>
                            </select>
                        </div>

                        <div class="flex items-end space-x-2">
                            <button type="submit" class="flex-1 px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 transition">
                                Filtrar
                            </button>
                            <a href="/gaps" class="px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                                Limpiar
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Tabla de GAPs -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Control / Brecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Impacto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioridad</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Objetivo</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Opciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($gaps)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay GAPs registrados</h3>
                                        <p class="mt-1 text-sm text-gray-500">Comienza creando un nuevo análisis de brecha.</p>
                                        <div class="mt-6">
                                            <a href="/gaps/create" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                                                Crear GAP
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($gaps as $gap): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($gap['codigo']) ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars(substr($gap['brecha'], 0, 60)) ?>...</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $impactoVariant = $gap['impacto'] === 'alto' ? 'error' : 
                                                            ($gap['impacto'] === 'medio' ? 'warning' : 'info');
                                            echo renderBadge(ucfirst($gap['impacto']), $impactoVariant);
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?= getPrioridadBadge($gap['prioridad']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $estadoCert = getEstadoCertificacionBadgeCompacto($gap['estado_certificacion']);
                                            echo renderBadge($estadoCert['text'], $estadoCert['variant']);
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= $gap['acciones_completadas'] ?> / <?= $gap['total_acciones'] ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= $gap['fecha_objetivo'] ? date('d/m/Y', strtotime($gap['fecha_objetivo'])) : 'No definida' ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="/gaps/<?= $gap['id'] ?>" class="text-primary-600 hover:text-primary-900 mr-3" title="Ver detalles">
                                                <svg class="h-5 w-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <button onclick="eliminarGap(<?= $gap['id'] ?>)" class="text-red-600 hover:text-red-900" title="Eliminar">
                                                <svg class="h-5 w-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>
        </div>
    </div>

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
            window.location.reload();
        } else {
            alert('Error: ' + (result.error || 'No se pudo eliminar el GAP'));
        }
    })
    .catch(err => {
        alert('Error de conexión con el servidor');
    });
}
</script>

</body>
</html>
