<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();

// Incluir componentes
include __DIR__ . '/../components/badge.php';
include __DIR__ . '/../components/alert.php';
include __DIR__ . '/../components/card.php';
include __DIR__ . '/../components/form/select.php';
include __DIR__ . '/../components/form/textarea.php';
?>

<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control <?= htmlspecialchars($soa['codigo']) ?> - ISO 27001 Platform</title>
    
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
                                <a href="/controles" class="ml-1 text-gray-500 hover:text-gray-700">Controles</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                                <span class="ml-1 text-gray-700 font-medium"><?= htmlspecialchars($soa['codigo']) ?></span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                            <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($soa['codigo']) ?></h1>
                            <p class="mt-1 text-sm text-gray-600"><?= htmlspecialchars($soa['control_nombre']) ?></p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Columna Principal -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Información del Control -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Información del Control</h3>
                            </div>
                            <div class="px-6 py-5">
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Dominio</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($soa['dominio_nombre']) ?></dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($soa['descripcion']) ?></dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Objetivo</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($soa['objetivo']) ?></dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Formulario de Evaluación -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Evaluación del Control</h3>
                            </div>
                            <div class="px-6 py-5">
                                <form id="updateForm" class="space-y-5">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    
                                    <div class="flex items-center">
                                        <input type="checkbox" name="aplicable" value="1" <?= $soa['aplicable'] ? 'checked' : '' ?> 
                                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                        <label class="ml-2 block text-sm text-gray-900">
                                            Control Aplicable a la Organización
                                        </label>
                                    </div>
                                    
                                    <?php
                                    echo renderSelect(
                                        'estado',
                                        'Estado de Implementación',
                                        [
                                            'no_implementado' => 'No Implementado',
                                            'parcial' => 'Parcialmente Implementado',
                                            'implementado' => 'Totalmente Implementado'
                                        ],
                                        $soa['estado'],
                                        ['required' => true]
                                    );
                                    ?>
                                    
                                    <?php
                                    echo renderTextarea(
                                        'justificacion',
                                        'Justificación / Notas',
                                        $soa['justificacion'] ?? '',
                                        [
                                            'rows' => 5,
                                            'placeholder' => 'Describa el estado de implementación, justificación de no aplicabilidad, o notas relevantes...'
                                        ]
                                    );
                                    ?>
                                    
                                    <div class="flex justify-end">
                                        <button type="submit" class="px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 transition">
                                            Guardar Cambios
                                        </button>
                                    </div>
                                </form>
                                <div id="message" class="mt-3"></div>
                            </div>
                        </div>

                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        
                        <!-- Estado Actual -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Estado Actual</h3>
                            </div>
                            <div class="px-6 py-5 space-y-3">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Aplicabilidad</p>
                                    <?= $soa['aplicable'] ? renderBadge('Aplicable', 'success') : renderBadge('No Aplicable', 'gray') ?>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Estado</p>
                                    <?php
                                    $estadoVariant = $soa['estado'] === 'implementado' ? 'success' :
                                                   ($soa['estado'] === 'parcial' ? 'warning' : 'error');
                                    echo renderBadge(ucfirst(str_replace('_', ' ', $soa['estado'])), $estadoVariant);
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Información Adicional</h3>
                            </div>
                            <div class="px-6 py-5 space-y-3">
                                <div>
                                    <p class="text-sm text-gray-600">Responsable</p>
                                    <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($soa['responsable'] ?? 'No asignado') ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Fecha de Evaluación</p>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <?= $soa['fecha_evaluacion'] ? date('d/m/Y', strtotime($soa['fecha_evaluacion'])) : 'Nunca' ?>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Última Actualización</p>
                                    <p class="mt-1 text-sm text-gray-900"><?= date('d/m/Y H:i', strtotime($soa['updated_at'])) ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Navegación -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Navegación</h3>
                            </div>
                            <div class="px-6 py-5 space-y-2">
                                <a href="/controles" class="flex items-center text-sm text-primary-600 hover:text-primary-700">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                    </svg>
                                    Volver a controles
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
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    fetch('/controles/<?= $soa['id'] ?>/update', {
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
