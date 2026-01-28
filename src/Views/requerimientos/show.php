<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();

// Incluir componentes
include __DIR__ . '/../components/badge.php';
include __DIR__ . '/../components/alert.php';
include __DIR__ . '/../components/card.php';
include __DIR__ . '/../components/progress-bar.php';
include __DIR__ . '/../components/form/textarea.php';
?>

<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requerimiento <?= htmlspecialchars($requerimiento['identificador']) ?> - ISO 27001 Platform</title>
    
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
                                <a href="/requerimientos" class="ml-1 text-gray-500 hover:text-gray-700">Requerimientos</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                                <span class="ml-1 text-gray-700 font-medium"><?= htmlspecialchars($requerimiento['identificador']) ?></span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                            <span class="text-lg font-bold text-primary-700"><?= htmlspecialchars($requerimiento['numero']) ?></span>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($requerimiento['identificador']) ?></h1>
                            <p class="mt-1 text-sm text-gray-600"><?= htmlspecialchars($requerimiento['titulo']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Alert Estado Automático -->
                <?= renderAlert(
                    'El estado de este requerimiento se calcula automáticamente basado en el progreso de implementación de sus controles asociados.',
                    'info',
                    'Estado Automático',
                    false
                ) ?>
                <div class="mb-6"></div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Columna Principal -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Información del Requerimiento -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Información del Requerimiento</h3>
                            </div>
                            <div class="px-6 py-5">
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?= nl2br(htmlspecialchars($requerimiento['descripcion'])) ?></dd>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Estado</dt>
                                            <dd class="mt-1">
                                                <?php
                                                $estadoVariant = $requerimiento['estado'] === 'completado' ? 'success' :
                                                               ($requerimiento['estado'] === 'en_proceso' ? 'warning' : 'gray');
                                                echo renderBadge(ucfirst(str_replace('_', ' ', $requerimiento['estado'])), $estadoVariant);
                                                ?>
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Número</dt>
                                            <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($requerimiento['numero']) ?></dd>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Fecha Inicio</dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                <?= $requerimiento['fecha_inicio'] ? date('d/m/Y', strtotime($requerimiento['fecha_inicio'])) : 'No iniciado' ?>
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Fecha Completado</dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                <?= $requerimiento['fecha_completado'] ? date('d/m/Y', strtotime($requerimiento['fecha_completado'])) : 'No completado' ?>
                                            </dd>
                                        </div>
                                    </div>

                                    <?php if ($requerimiento['observaciones']): ?>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Observaciones</dt>
                                        <dd class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded"><?= nl2br(htmlspecialchars($requerimiento['observaciones'])) ?></dd>
                                    </div>
                                    <?php endif; ?>
                                </dl>
                            </div>
                        </div>

                        <!-- Actualizar Observaciones -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Actualizar Observaciones</h3>
                            </div>
                            <div class="px-6 py-5">
                                <form id="updateForm">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    
                                    <?php
                                    echo renderTextarea(
                                        'observaciones',
                                        'Observaciones',
                                        $requerimiento['observaciones'] ?? '',
                                        [
                                            'required' => true,
                                            'rows' => 4,
                                            'placeholder' => 'Agregue observaciones sobre el cumplimiento de este requerimiento...',
                                            'help' => 'Mínimo 10 caracteres'
                                        ]
                                    );
                                    ?>
                                    
                                    <div class="mt-4 flex justify-end">
                                        <button type="submit" class="px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 transition">
                                            Actualizar Observaciones
                                        </button>
                                    </div>
                                </form>
                                <div id="message" class="mt-3"></div>
                            </div>
                        </div>

                        <!-- Controles Asociados -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Controles Asociados</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aplicable</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php if (empty($controles)): ?>
                                        <tr>
                                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                                No hay controles asociados a este requerimiento.
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach ($controles as $control): ?>
                                            <tr class="hover:bg-gray-50 transition">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($control['codigo']) ?>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900">
                                                    <?= htmlspecialchars($control['nombre']) ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    <?= $control['aplicable'] ? renderBadge('Sí', 'success', 'sm') : renderBadge('No', 'gray', 'sm') ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    <?php
                                                    $estadoControl = $control['estado_implementacion'] ?? 'no_evaluado';
                                                    $variant = $estadoControl === 'implementado' ? 'success' :
                                                              ($estadoControl === 'parcial' ? 'warning' : 'gray');
                                                    echo renderBadge(ucfirst(str_replace('_', ' ', $estadoControl)), $variant, 'sm');
                                                    ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <?php if ($control['soa_id']): ?>
                                                    <a href="/controles/<?= $control['soa_id'] ?>" class="text-primary-600 hover:text-primary-900">
                                                        Ver Control
                                                    </a>
                                                    <?php else: ?>
                                                    <span class="text-gray-400">No disponible</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        
                        <!-- Progreso -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Progreso de Controles</h3>
                            </div>
                            <div class="px-6 py-5 space-y-4">
                                <div>
                                    <div class="text-sm text-gray-600 mb-2">
                                        <?= $progreso['controles_implementados'] ?> de <?= $progreso['controles_aplicables'] ?> implementados
                                    </div>
                                    <?= renderProgressBar((float)$progreso['porcentaje'], 'primary', 'md', true) ?>
                                </div>
                                
                                <div class="pt-3 border-t border-gray-200">
                                    <dl class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <dt class="text-gray-600">Total controles:</dt>
                                            <dd class="font-semibold text-gray-900"><?= $progreso['total_controles'] ?></dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-gray-600">Aplicables:</dt>
                                            <dd class="font-semibold text-gray-900"><?= $progreso['controles_aplicables'] ?></dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-gray-600">Implementados:</dt>
                                            <dd class="font-semibold text-green-600"><?= $progreso['controles_implementados'] ?></dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <!-- Navegación -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Navegación</h3>
                            </div>
                            <div class="px-6 py-5 space-y-2">
                                <a href="/requerimientos" class="flex items-center text-sm text-primary-600 hover:text-primary-700">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                    </svg>
                                    Volver a requerimientos
                                </a>
                                <a href="/dashboard" class="flex items-center text-sm text-gray-600 hover:text-gray-700">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                    Ir al dashboard
                                </a>
                            </div>
                        </div>

                    </div>
                </div>

            </main>
        </div>
    </div>

<script>
document.getElementById('updateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const observaciones = form.querySelector('[name="observaciones"]').value;
    
    const data = {
        observaciones: observaciones,
        csrf_token: '<?= $csrfToken ?>'
    };
    
    fetch('/requerimientos/<?= $requerimiento['id'] ?>/update', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    })
    .then(res => {
        if (!res.ok) {
            throw new Error('HTTP error ' + res.status);
        }
        return res.json();
    })
    .then(result => {
        const messageDiv = document.getElementById('message');
        if (result.success) {
            messageDiv.innerHTML = '<div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">' + result.message + '</div>';
            setTimeout(() => window.location.reload(), 1500);
        } else {
            messageDiv.innerHTML = '<div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">Error: ' + (result.error || 'Error desconocido') + '</div>';
        }
    })
    .catch(err => {
        console.error('Error:', err);
        document.getElementById('message').innerHTML = '<div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">Error de conexión con el servidor</div>';
    });
});
</script>

</body>
</html>
