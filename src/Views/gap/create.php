<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();

// Incluir componentes
include __DIR__ . '/../components/badge.php';
include __DIR__ . '/../components/alert.php';
include __DIR__ . '/../components/form/input.php';
include __DIR__ . '/../components/form/select.php';
include __DIR__ . '/../components/form/textarea.php';
?>

<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear GAP - ISO 27001 Platform</title>
    
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
                                <a href="/gaps" class="ml-1 text-gray-500 hover:text-gray-700">GAPs</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                                <span class="ml-1 text-gray-700 font-medium">Nuevo</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Crear Nuevo GAP</h1>
                    <p class="mt-2 text-sm text-gray-600">Registra una nueva brecha de cumplimiento identificada</p>
                </div>

                <?php if ($flash = $this->session->get('error')): ?>
                    <?= renderAlert($flash, 'error', 'Error:', true) ?>
                    <div class="mb-6"></div>
                <?php endif; ?>

                <form method="POST" action="/gaps/store" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        
                        <!-- Columna Principal -->
                        <div class="lg:col-span-2 space-y-6">
                            
                            <!-- Información del GAP -->
                            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                                <div class="px-6 py-5 border-b border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-900">Información de la Brecha</h3>
                                </div>
                                <div class="px-6 py-5 space-y-5">
                                    
                                    <?php
                                    $controlesOptions = [];
                                    foreach ($controles as $control) {
                                        $controlesOptions[$control['soa_id']] = $control['codigo'] . ' - ' . substr($control['control_nombre'], 0, 60);
                                    }
                                    
                                    echo renderSelect(
                                        'soa_id',
                                        'Control Asociado',
                                        $controlesOptions,
                                        '',
                                        [
                                            'required' => true,
                                            'placeholder' => 'Seleccione un control...',
                                            'help' => 'Seleccione el control al que está asociada esta brecha'
                                        ]
                                    );
                                    ?>

                                    <?php
                                    echo renderTextarea(
                                        'brecha',
                                        'Descripción de la Brecha',
                                        '',
                                        [
                                            'required' => true,
                                            'rows' => 5,
                                            'placeholder' => 'Describa detalladamente la brecha identificada y su contexto...',
                                            'maxlength' => 1000,
                                            'show_counter' => true
                                        ]
                                    );
                                    ?>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                        <?php
                                        echo renderSelect(
                                            'impacto',
                                            'Impacto',
                                            [
                                                'critico' => 'Crítico',
                                                'alto' => 'Alto',
                                                'medio' => 'Medio',
                                                'bajo' => 'Bajo'
                                            ],
                                            '',
                                            [
                                                'required' => true,
                                                'placeholder' => 'Seleccionar...',
                                                'help' => 'Nivel de impacto en la organización'
                                            ]
                                        );
                                        ?>

                                        <?php
                                        echo renderSelect(
                                            'prioridad',
                                            'Prioridad',
                                            [
                                                'alta' => 'Alta',
                                                'media' => 'Media',
                                                'baja' => 'Baja'
                                            ],
                                            '',
                                            [
                                                'required' => true,
                                                'placeholder' => 'Seleccionar...',
                                                'help' => 'Prioridad de resolución'
                                            ]
                                        );
                                        ?>
                                    </div>

                                    <?php
                                    echo renderInput(
                                        'fecha_objetivo',
                                        'date',
                                        'Fecha Objetivo',
                                        '',
                                        [
                                            'help' => 'Fecha límite para cerrar esta brecha',
                                            'min' => date('Y-m-d')
                                        ]
                                    );
                                    ?>

                                </div>
                            </div>

                            <!-- Plan de Acción -->
                            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                                <div class="px-6 py-5 border-b border-gray-200">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">Plan de Acción</h3>
                                            <p class="mt-1 text-sm text-gray-500">Define las acciones necesarias para cerrar la brecha</p>
                                        </div>
                                        <button type="button" onclick="agregarAccion()" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700">
                                            <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Agregar Acción
                                        </button>
                                    </div>
                                </div>
                                <div class="px-6 py-5">
                                    <div id="acciones-container" class="space-y-4">
                                        <!-- Las acciones se agregarán dinámicamente aquí -->
                                        <div class="text-center py-8 text-gray-500">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                            <p class="mt-2 text-sm">No hay acciones definidas</p>
                                            <p class="text-xs">Haz clic en "Agregar Acción" para comenzar</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Sidebar -->
                        <div class="space-y-6">
                            
                            <!-- Ayuda -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-blue-800">Guía de Registro</h3>
                                        <div class="mt-2 text-sm text-blue-700">
                                            <ul class="list-disc pl-5 space-y-1">
                                                <li>Selecciona el control no implementado</li>
                                                <li>Describe claramente la brecha</li>
                                                <li>Define el impacto y prioridad</li>
                                                <li>Establece una fecha objetivo realista</li>
                                                <li>Agrega acciones específicas y medibles</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Impacto y Prioridad -->
                            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-5">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3">Criterios de Evaluación</h4>
                                <div class="space-y-3 text-xs text-gray-600">
                                    <div>
                                        <p class="font-medium text-gray-700">Impacto:</p>
                                        <ul class="mt-1 space-y-1 pl-3">
                                            <li><span class="font-medium">Crítico:</span> Afecta severamente las operaciones</li>
                                            <li><span class="font-medium">Alto:</span> Impacto significativo</li>
                                            <li><span class="font-medium">Medio:</span> Impacto moderado</li>
                                            <li><span class="font-medium">Bajo:</span> Impacto mínimo</li>
                                        </ul>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-700">Prioridad:</p>
                                        <ul class="mt-1 space-y-1 pl-3">
                                            <li><span class="font-medium">Alta:</span> Requiere atención inmediata</li>
                                            <li><span class="font-medium">Media:</span> Atención en corto plazo</li>
                                            <li><span class="font-medium">Baja:</span> Puede planificarse</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a href="/gaps" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 transition">
                            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Crear GAP
                        </button>
                    </div>

                </form>

            </main>
        </div>
    </div>

<script>
let accionCount = 0;

function agregarAccion() {
    accionCount++;
    const container = document.getElementById('acciones-container');
    
    // Remover mensaje de "no hay acciones" si existe
    const emptyMessage = container.querySelector('.text-center');
    if (emptyMessage) {
        emptyMessage.remove();
    }
    
    const accionDiv = document.createElement('div');
    accionDiv.className = 'border border-gray-200 rounded-lg p-4 relative';
    accionDiv.id = 'accion-' + accionCount;
    
    accionDiv.innerHTML = `
        <button type="button" onclick="eliminarAccion(${accionCount})" class="absolute top-2 right-2 text-red-600 hover:text-red-900">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Descripción de la Acción <span class="text-red-500">*</span>
                </label>
                <textarea 
                    name="accion_descripcion[]" 
                    rows="2" 
                    required
                    placeholder="Describe la acción a realizar..."
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm"
                ></textarea>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Responsable</label>
                    <input 
                        type="text" 
                        name="accion_responsable[]"
                        placeholder="Nombre del responsable"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm"
                    >
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Fecha Compromiso <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="accion_fecha[]"
                        required
                        min="<?= date('Y-m-d') ?>"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm"
                    >
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(accionDiv);
}

function eliminarAccion(id) {
    const accionDiv = document.getElementById('accion-' + id);
    if (accionDiv) {
        accionDiv.remove();
    }
    
    // Si no quedan acciones, mostrar mensaje
    const container = document.getElementById('acciones-container');
    if (container.children.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8 text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="mt-2 text-sm">No hay acciones definidas</p>
                <p class="text-xs">Haz clic en "Agregar Acción" para comenzar</p>
            </div>
        `;
    }
}

// Agregar una acción por defecto al cargar la página
window.addEventListener('DOMContentLoaded', function() {
    agregarAccion();
});
</script>

</body>
</html>
