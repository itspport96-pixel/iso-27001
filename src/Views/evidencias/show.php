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
    <title>Evidencia - <?= htmlspecialchars($evidencia['nombre_archivo']) ?> - ISO 27001 Platform</title>
    
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
                                <span class="ml-1 text-gray-700 font-medium"><?= htmlspecialchars(substr($evidencia['nombre_archivo'], 0, 30)) ?></span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <!-- Header -->
                <div class="mb-8">
                    <div class="sm:flex sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Detalle de Evidencia</h1>
                            <p class="mt-2 text-sm text-gray-600"><?= htmlspecialchars($evidencia['nombre_archivo']) ?></p>
                        </div>
                        <div class="mt-4 sm:mt-0 flex space-x-3">
                            <a href="/evidencias/<?= $evidencia['id'] ?>/download" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Descargar
                            </a>
                            <?php if ($evidencia['estado_validacion'] !== 'aprobada'): ?>
                            <button onclick="eliminarEvidencia(<?= $evidencia['id'] ?>)" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition">
                                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Eliminar
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Alerta de Estado Aprobada -->
                <?php if ($evidencia['estado_validacion'] === 'aprobada'): ?>
                    <?= renderAlert(
                        'Esta evidencia ha sido aprobada y es inmutable por cumplimiento normativo ISO 27001. No puede ser editada ni eliminada. Para actualizar, suba una nueva versión.',
                        'success',
                        'Evidencia Aprobada',
                        false
                    ) ?>
                    <div class="mb-6"></div>
                <?php elseif ($evidencia['estado_validacion'] === 'rechazada'): ?>
                    <?= renderAlert(
                        'Esta evidencia ha sido rechazada. Puede eliminarse y subir una nueva versión corregida.',
                        'error',
                        'Evidencia Rechazada',
                        false
                    ) ?>
                    <div class="mb-6"></div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Columna Principal -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Información del Control -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Control Asociado</h3>
                            </div>
                            <div class="px-6 py-5">
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Código</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($evidencia['codigo']) ?></dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($evidencia['control_nombre']) ?></dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Dominio</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($evidencia['dominio_nombre']) ?></dd>
                                    </div>
                                    <?php if ($evidencia['descripcion']): ?>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?= nl2br(htmlspecialchars($evidencia['descripcion'])) ?></dd>
                                    </div>
                                    <?php endif; ?>
                                </dl>
                            </div>
                        </div>

                        <!-- Información del Archivo -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Información del Archivo</h3>
                            </div>
                            <div class="px-6 py-5">
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Nombre del archivo</dt>
                                        <dd class="mt-1 text-sm text-gray-900 font-mono"><?= htmlspecialchars($evidencia['nombre_archivo']) ?></dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Tipo de archivo</dt>
                                        <dd class="mt-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <?= htmlspecialchars($evidencia['tipo_mime']) ?>
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Tamaño</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?= round($evidencia['tamano'] / 1024, 2) ?> KB</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Hash SHA-256</dt>
                                        <dd class="mt-1 text-xs text-gray-900 font-mono break-all bg-gray-50 p-2 rounded"><?= htmlspecialchars($evidencia['hash_sha256']) ?></dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Estado de Validación -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Estado de Validación</h3>
                            </div>
                            <div class="px-6 py-5">
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Estado actual</dt>
                                        <dd class="mt-1">
                                            <?php
                                            $estadoVariant = $evidencia['estado_validacion'] === 'aprobada' ? 'success' :
                                                           ($evidencia['estado_validacion'] === 'rechazada' ? 'error' : 'warning');
                                            echo renderBadge(ucfirst($evidencia['estado_validacion']), $estadoVariant);
                                            ?>
                                        </dd>
                                    </div>
                                    
                                    <?php if ($evidencia['validado_por']): ?>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Validado por</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            <?= htmlspecialchars($evidencia['validado_por_nombre']) ?>
                                            <span class="text-gray-500">(<?= htmlspecialchars($evidencia['validado_por_email']) ?>)</span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Fecha de validación</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?= date('d/m/Y H:i', strtotime($evidencia['fecha_validacion'])) ?></dd>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($evidencia['comentarios']): ?>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Comentarios</dt>
                                        <dd class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded"><?= nl2br(htmlspecialchars($evidencia['comentarios'])) ?></dd>
                                    </div>
                                    <?php endif; ?>
                                </dl>
                            </div>
                        </div>

                        <!-- Formulario de Validación -->
                        <?php if ($evidencia['estado_validacion'] === 'pendiente'): ?>
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Validar Evidencia</h3>
                                <p class="mt-1 text-sm text-gray-500">Revise la evidencia y decida aprobarla o rechazarla</p>
                            </div>
                            <div class="px-6 py-5">
                                <form id="validarForm" class="space-y-4">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    
                                    <?php
                                    echo renderSelect(
                                        'estado_validacion',
                                        'Decisión',
                                        [
                                            '' => 'Seleccione una opción',
                                            'aprobada' => 'Aprobar (la evidencia será inmutable)',
                                            'rechazada' => 'Rechazar (podrá eliminarse y subir nueva)'
                                        ],
                                        '',
                                        ['required' => true]
                                    );
                                    ?>
                                    
                                    <?php
                                    echo renderTextarea(
                                        'comentarios',
                                        'Comentarios',
                                        '',
                                        [
                                            'rows' => 4,
                                            'placeholder' => 'Agregue comentarios sobre la validación (opcional)...'
                                        ]
                                    );
                                    ?>
                                    
                                    <div class="flex justify-end space-x-3">
                                        <a href="/evidencias" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                            Cancelar
                                        </a>
                                        <button type="submit" class="px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                                            Validar Evidencia
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        
                        <!-- Información de Carga -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Información de Carga</h3>
                            </div>
                            <div class="px-6 py-5">
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Subido por</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            <?= htmlspecialchars($evidencia['subido_por_nombre']) ?>
                                            <div class="text-xs text-gray-500"><?= htmlspecialchars($evidencia['subido_por_email']) ?></div>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Fecha de carga</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?= date('d/m/Y H:i', strtotime($evidencia['created_at'])) ?></dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Acciones Rápidas -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Acciones</h3>
                            </div>
                            <div class="px-6 py-5 space-y-2">
                                <a href="/evidencias/<?= $evidencia['id'] ?>/download" class="flex items-center text-sm text-primary-600 hover:text-primary-700">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Descargar archivo
                                </a>
				<a href="/controles/<?= $evidencia['soa_id'] ?>" class="flex items-center text-sm text-primary-600 hover:text-primary-700">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                    Ver control completo
                                </a>
                                <a href="/evidencias" class="flex items-center text-sm text-gray-600 hover:text-gray-700">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                    </svg>
                                    Volver a la lista
                                </a>
                            </div>
                        </div>

                        <!-- Información sobre Inmutabilidad -->
                        <?php if ($evidencia['estado_validacion'] === 'aprobada'): ?>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-5">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">Evidencia Inmutable</h3>
                                    <div class="mt-2 text-xs text-green-700">
                                        <p>Esta evidencia cumple con los requisitos de ISO 27001 sobre trazabilidad y no repudio.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>

            </main>
        </div>
    </div>

<script>
<?php if ($evidencia['estado_validacion'] === 'pendiente'): ?>
document.getElementById('validarForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const estado = form.querySelector('[name="estado_validacion"]').value;
    const comentarios = form.querySelector('[name="comentarios"]').value;
    
    if (!estado) {
        alert('Debe seleccionar una decisión de validación');
        return;
    }
    
    let confirmMsg = '';
    if (estado === 'aprobada') {
        confirmMsg = '¿Aprobar esta evidencia? Una vez aprobada, NO podrá ser eliminada ni modificada.';
    } else if (estado === 'rechazada') {
        confirmMsg = '¿Rechazar esta evidencia? Podrá ser eliminada y reemplazada por una nueva versión.';
    }
    
    if (!confirm(confirmMsg)) {
        return;
    }
    
    const data = {
        estado_validacion: estado,
        comentarios: comentarios,
        csrf_token: '<?= $csrfToken ?>'
    };
    
    fetch('/evidencias/<?= $evidencia['id'] ?>/validar', {
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
        if (result.success) {
            alert('Evidencia validada exitosamente');
            window.location.reload();
        } else {
            alert('Error: ' + (result.error || result.message || 'Error desconocido'));
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Error de conexión con el servidor: ' + err.message);
    });
});
<?php endif; ?>

function eliminarEvidencia(id) {
    if (!confirm('¿Está seguro de eliminar esta evidencia? El archivo será eliminado permanentemente.')) {
        return;
    }
    
    fetch('/evidencias/' + id + '/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({csrf_token: '<?= $csrfToken ?>'})
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            alert('Evidencia eliminada exitosamente');
            window.location.href = '/evidencias';
        } else {
            alert('Error: ' + (result.error || 'No se pudo eliminar'));
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Error de conexión con el servidor');
    });
}
</script>

</body>
</html>
