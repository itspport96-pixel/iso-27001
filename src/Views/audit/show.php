<?php
// Incluir componentes
include __DIR__ . '/../components/badge.php';
include __DIR__ . '/../components/card.php';
?>

<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log de Auditoría #<?= $log['id'] ?> - ISO 27001 Platform</title>
    
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
                
                <!-- Breadcrumb -->
                <nav class="flex mb-6" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="/dashboard" class="text-gray-500 hover:text-gray-700">Dashboard</a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                                <a href="/audit" class="ml-1 text-gray-500 hover:text-gray-700">Auditoría</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                                <span class="ml-1 text-gray-700 font-medium">#<?= $log['id'] ?></span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Detalle de Log #<?= $log['id'] ?></h1>
                    <p class="mt-2 text-sm text-gray-600">Información completa del registro de auditoría</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Columna Principal -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Información General -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Información General</h3>
                            </div>
                            <div class="px-6 py-5">
                                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">ID del Log</dt>
                                        <dd class="mt-1 text-sm text-gray-900">#<?= htmlspecialchars($log['id']) ?></dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Fecha y Hora</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Usuario</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($log['usuario_nombre'] ?? 'Sistema') ?></dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Acción</dt>
                                        <dd class="mt-1">
                                            <?php
                                            $accionVariant = $log['accion'] === 'INSERT' ? 'success' :
                                                           ($log['accion'] === 'DELETE' ? 'error' : 'info');
                                            echo renderBadge($log['accion'], $accionVariant);
                                            ?>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Tabla</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($log['tabla']) ?></dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">ID del Registro</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($log['registro_id'] ?? 'N/A') ?></dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Información de Red -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Información de Red</h3>
                            </div>
                            <div class="px-6 py-5">
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Dirección IP</dt>
                                        <dd class="mt-1 text-sm text-gray-900 font-mono"><?= htmlspecialchars($log['ip']) ?></dd>
                                    </div>
                                    <?php if (!empty($log['user_agent'])): ?>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">User Agent</dt>
                                        <dd class="mt-1 text-xs text-gray-900 bg-gray-50 p-2 rounded break-all"><?= htmlspecialchars($log['user_agent']) ?></dd>
                                    </div>
                                    <?php endif; ?>
                                </dl>
                            </div>
                        </div>

                        <?php if ($log['datos_previos']): ?>
                        <!-- Datos Previos -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Datos Previos</h3>
                            </div>
                            <div class="px-6 py-5">
                                <pre class="text-xs bg-gray-50 p-4 rounded overflow-x-auto"><?= htmlspecialchars(json_encode(json_decode($log['datos_previos']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($log['datos_nuevos']): ?>
                        <!-- Datos Nuevos -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Datos Nuevos</h3>
                            </div>
                            <div class="px-6 py-5">
                                <pre class="text-xs bg-gray-50 p-4 rounded overflow-x-auto"><?= htmlspecialchars(json_encode(json_decode($log['datos_nuevos']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        
                        <!-- Acciones Rápidas -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Navegación</h3>
                            </div>
                            <div class="px-6 py-5 space-y-2">
                                <a href="/audit" class="flex items-center text-sm text-primary-600 hover:text-primary-700">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                    </svg>
                                    Volver a lista de logs
                                </a>
                                <a href="/dashboard" class="flex items-center text-sm text-gray-600 hover:text-gray-700">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                    Ir al dashboard
                                </a>
                            </div>
                        </div>

                        <!-- Info ISO 27001 -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">Trazabilidad ISO 27001</h3>
                                    <div class="mt-2 text-xs text-blue-700">
                                        <p>Este registro forma parte del sistema de auditoría requerido por ISO 27001 para garantizar trazabilidad y no repudio.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </main>
        </div>
    </div>

</body>
</html>
