<?php
// Obtener actividad reciente del log de auditoría
$auditRepo = new \App\Repositories\AuditLogRepository();
$actividadReciente = $auditRepo->getRecent(10); // Últimas 10 actividades

// Función helper para iconos según acción
function getAccionIcon($accion) {
    switch ($accion) {
        case 'INSERT': return '<svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/></svg>';
        case 'UPDATE': return '<svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>';
        case 'DELETE': return '<svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>';
        default: return '<svg class="h-5 w-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>';
    }
}

function getNombreTabla($tabla) {
    $nombres = [
        'soa_entries' => 'Controles',
        'gap_items' => 'GAPs',
        'evidencias' => 'Evidencias',
        'empresa_requerimientos' => 'Requerimientos',
        'usuarios' => 'Usuarios'
    ];
    return $nombres[$tabla] ?? $tabla;
}
?>

<div class="mt-8">
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Actividad Reciente</h3>
        </div>
        <div class="p-6">
            <?php if (empty($actividadReciente)): ?>
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No hay actividad reciente</p>
                </div>
            <?php else: ?>
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        <?php foreach ($actividadReciente as $index => $actividad): ?>
                            <li>
                                <div class="relative pb-8">
                                    <?php if ($index < count($actividadReciente) - 1): ?>
                                        <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    <?php endif; ?>
                                    
                                    <div class="relative flex items-start space-x-3">
                                        <div class="relative">
                                            <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center ring-8 ring-white">
                                                <?= getAccionIcon($actividad['accion']) ?>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div>
                                                <div class="text-sm">
                                                    <span class="font-medium text-gray-900"><?= htmlspecialchars($actividad['usuario_nombre'] ?? 'Sistema') ?></span>
                                                    <span class="text-gray-500">
                                                        <?php
                                                        $accionTexto = strtolower($actividad['accion']);
                                                        if ($accionTexto === 'insert') echo 'creó';
                                                        elseif ($accionTexto === 'update') echo 'actualizó';
                                                        elseif ($accionTexto === 'delete') echo 'eliminó';
                                                        else echo $accionTexto;
                                                        ?>
                                                    </span>
                                                    <span class="font-medium text-gray-900"><?= getNombreTabla($actividad['tabla']) ?></span>
                                                    <?php if ($actividad['registro_id']): ?>
                                                        <span class="text-gray-500">#<?= $actividad['registro_id'] ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="mt-0.5 text-xs text-gray-500">
                                                    <?php
                                                    $timestamp = strtotime($actividad['created_at']);
                                                    $diff = time() - $timestamp;
                                                    
                                                    if ($diff < 60) echo 'hace unos segundos';
                                                    elseif ($diff < 3600) echo 'hace ' . floor($diff / 60) . ' min';
                                                    elseif ($diff < 86400) echo 'hace ' . floor($diff / 3600) . ' h';
                                                    else echo date('d/m/Y H:i', $timestamp);
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <a href="/audit/<?= $actividad['id'] ?>" class="text-xs text-primary-600 hover:text-primary-700">
                                                Ver
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="mt-6 text-center">
                    <a href="/audit" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                        Ver todo el historial →
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
