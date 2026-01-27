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
    <title>Subir Evidencia - ISO 27001 Platform</title>
    
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
                                <a href="/evidencias" class="ml-1 text-gray-500 hover:text-gray-700">Evidencias</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                                <span class="ml-1 text-gray-700 font-medium">Subir</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Subir Nueva Evidencia</h1>
                    <p class="mt-2 text-sm text-gray-600">Sube documentación que respalde el cumplimiento de un control</p>
                </div>

                <?php if ($flash = $this->session->get('error')): ?>
                    <?= renderAlert($flash, 'error', 'Error:', true) ?>
                    <div class="mb-6"></div>
                <?php endif; ?>

                <form method="POST" action="/evidencias/store" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        
                        <!-- Columna Principal -->
                        <div class="lg:col-span-2 space-y-6">
                            
                            <!-- Información de la Evidencia -->
                            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                                <div class="px-6 py-5 border-b border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-900">Información de la Evidencia</h3>
                                </div>
                                <div class="px-6 py-5 space-y-5">
                                    
                                    <?php
                                    $controlesOptions = [];
                                    foreach ($controles as $control) {
                                        $controlesOptions[$control['id']] = $control['codigo'] . ' - ' . substr($control['nombre'], 0, 60);
                                    }
                                    
                                    echo renderSelect(
                                        'control_id',
                                        'Control Asociado',
                                        $controlesOptions,
                                        '',
                                        [
                                            'required' => true,
                                            'placeholder' => 'Seleccione un control...',
                                            'help' => 'Seleccione el control que esta evidencia respalda'
                                        ]
                                    );
                                    ?>

                                    <div>
                                        <label for="archivo" class="block text-sm font-medium text-gray-700 mb-2">
                                            Archivo <span class="text-red-500">*</span>
                                        </label>
                                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition">
                                            <div class="space-y-1 text-center">
                                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                <div class="flex text-sm text-gray-600">
                                                    <label for="archivo" class="relative cursor-pointer rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                                        <span>Subir archivo</span>
                                                        <input id="archivo" name="archivo" type="file" class="sr-only" required onchange="mostrarNombreArchivo(this)">
                                                    </label>
                                                    <p class="pl-1">o arrastra y suelta</p>
                                                </div>
                                                <p class="text-xs text-gray-500">PDF, JPG, PNG, GIF, DOCX, XLSX, TXT hasta 10MB</p>
                                                <p id="nombre-archivo" class="text-sm text-gray-700 font-medium mt-2"></p>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>

                        <!-- Sidebar -->
                        <div class="space-y-6">
                            
                            <!-- Información -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-blue-800">Sobre las Evidencias</h3>
                                        <div class="mt-2 text-sm text-blue-700">
                                            <ul class="list-disc pl-5 space-y-1">
                                                <li>Las evidencias respaldan el cumplimiento de controles</li>
                                                <li>Una vez aprobadas, son <strong>inmutables</strong></li>
                                                <li>Se calcula automáticamente el hash SHA-256</li>
                                                <li>El archivo se almacena de forma segura</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Formatos Permitidos -->
                            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-5">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3">Formatos Permitidos</h4>
                                <div class="space-y-2 text-xs text-gray-600">
                                    <div class="flex items-center">
                                        <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>PDF - Documentos</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>JPG, PNG, GIF - Imágenes</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>DOCX, XLSX - Office</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>TXT - Texto plano</span>
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <p class="text-xs text-gray-500">
                                        <strong>Tamaño máximo:</strong> 10 MB
                                    </p>
                                </div>
                            </div>

                            <!-- Proceso de Validación -->
                            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-5">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3">Proceso de Validación</h4>
                                <div class="space-y-3">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 h-6 w-6 rounded-full bg-yellow-100 flex items-center justify-center">
                                            <span class="text-xs font-medium text-yellow-800">1</span>
                                        </div>
                                        <p class="ml-3 text-xs text-gray-600">Archivo subido - Estado <strong>Pendiente</strong></p>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center">
                                            <span class="text-xs font-medium text-blue-800">2</span>
                                        </div>
                                        <p class="ml-3 text-xs text-gray-600">Revisión por auditor o responsable</p>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 h-6 w-6 rounded-full bg-green-100 flex items-center justify-center">
                                            <span class="text-xs font-medium text-green-800">3</span>
                                        </div>
                                        <p class="ml-3 text-xs text-gray-600">Aprobación - Evidencia <strong>Inmutable</strong></p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a href="/evidencias" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 transition">
                            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            Subir Evidencia
                        </button>
                    </div>

                </form>

            </main>
        </div>
    </div>

<script>
function mostrarNombreArchivo(input) {
    const nombreArchivo = document.getElementById('nombre-archivo');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const sizeMB = (file.size / 1048576).toFixed(2);
        nombreArchivo.textContent = `📎 ${file.name} (${sizeMB} MB)`;
    } else {
        nombreArchivo.textContent = '';
    }
}
</script>

</body>
</html>
