<?php
// Incluir componentes
include __DIR__ . '/../components/badge.php';
include __DIR__ . '/../components/alert.php';
include __DIR__ . '/../components/card.php';
include __DIR__ . '/../components/table.php';
?>

<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría - ISO 27001 Platform</title>
    
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
                    <h1 class="text-3xl font-bold text-gray-900">Registro de Auditoría</h1>
                    <p class="mt-2 text-sm text-gray-600">Trazabilidad completa de cambios en el sistema</p>
                </div>

                <!-- Estadísticas -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Registros</p>
                                <p class="text-2xl font-semibold text-gray-900"><?= number_format($estadisticas['total']) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Usuarios Activos</p>
                                <p class="text-2xl font-semibold text-gray-900"><?= $estadisticas['usuarios_activos'] ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Tablas Afectadas</p>
                                <p class="text-2xl font-semibold text-gray-900"><?= $estadisticas['tablas_afectadas'] ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
                    <form method="GET" action="/audit" class="grid grid-cols-1 gap-4 sm:grid-cols-5">
                        <div>
                            <label for="filter_accion" class="block text-sm font-medium text-gray-700 mb-1">Acción</label>
                            <select name="accion" id="filter_accion" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                <option value="">Todas</option>
                                <option value="INSERT" <?= ($filtros['accion'] === 'INSERT') ? 'selected' : '' ?>>INSERT</option>
                                <option value="UPDATE" <?= ($filtros['accion'] === 'UPDATE') ? 'selected' : '' ?>>UPDATE</option>
                                <option value="DELETE" <?= ($filtros['accion'] === 'DELETE') ? 'selected' : '' ?>>DELETE</option>
                            </select>
                        </div>

                        <div>
                            <label for="filter_tabla" class="block text-sm font-medium text-gray-700 mb-1">Tabla</label>
                            <select name="tabla" id="filter_tabla" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                <option value="">Todas</option>
                                <option value="soa_entries" <?= ($filtros['tabla'] === 'soa_entries') ? 'selected' : '' ?>>Controles</option>
                                <option value="gap_items" <?= ($filtros['tabla'] === 'gap_items') ? 'selected' : '' ?>>GAPs</option>
                                <option value="evidencias" <?= ($filtros['tabla'] === 'evidencias') ? 'selected' : '' ?>>Evidencias</option>
                                <option value="empresa_requerimientos" <?= ($filtros['tabla'] === 'empresa_requerimientos') ? 'selected' : '' ?>>Requerimientos</option>
                            </select>
                        </div>

                        <div>
                            <label for="filter_desde" class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                            <input type="date" name="fecha_desde" id="filter_desde" value="<?= htmlspecialchars($filtros['fecha_desde'] ?? '') ?>" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                        </div>

                        <div>
                            <label for="filter_hasta" class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                            <input type="date" name="fecha_hasta" id="filter_hasta" value="<?= htmlspecialchars($filtros['fecha_hasta'] ?? '') ?>" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                        </div>

                        <div class="flex items-end space-x-2">
                            <button type="submit" class="flex-1 px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 transition">
                                Filtrar
                            </button>
                            <a href="/audit" class="px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                                Limpiar
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Tabla de Logs -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha/Hora</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tabla</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay registros</h3>
                                        <p class="mt-1 text-sm text-gray-500">No se encontraron logs con los filtros aplicados.</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $log): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($log['usuario_nombre'] ?? 'Sistema') ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $accionVariant = $log['accion'] === 'INSERT' ? 'success' :
                                                           ($log['accion'] === 'DELETE' ? 'error' : 'info');
                                            echo renderBadge($log['accion'], $accionVariant, 'sm');
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($log['tabla']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($log['registro_id'] ?? '-') ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($log['ip']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="/audit/<?= $log['id'] ?>" class="text-primary-600 hover:text-primary-900" title="Ver detalle">
                                                <svg class="h-5 w-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
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

</body>
</html>
