<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
?>

<h2>Centro de Notificaciones</h2>

<div id="loadingNotificaciones">Cargando...</div>

<div id="resumenNotificaciones" style="display: none;">
    
    <h3>Resumen de Pendientes</h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
        <div style="background: #fff3cd; padding: 20px; border-radius: 8px; border-left: 4px solid #ffc107;">
            <h4 style="margin: 0 0 10px 0;">Acciones Proximas</h4>
            <p style="font-size: 2em; margin: 0; font-weight: bold;" id="contadorAccionesProximas">0</p>
            <small>Vencen en 7 dias</small>
        </div>
        
        <div style="background: #f8d7da; padding: 20px; border-radius: 8px; border-left: 4px solid #dc3545;">
            <h4 style="margin: 0 0 10px 0;">Acciones Vencidas</h4>
            <p style="font-size: 2em; margin: 0; font-weight: bold; color: #dc3545;" id="contadorAccionesVencidas">0</p>
            <small>Requieren atencion</small>
        </div>
        
        <div style="background: #d1ecf1; padding: 20px; border-radius: 8px; border-left: 4px solid #17a2b8;">
            <h4 style="margin: 0 0 10px 0;">Evidencias Pendientes</h4>
            <p style="font-size: 2em; margin: 0; font-weight: bold;" id="contadorEvidenciasPendientes">0</p>
            <small>Por validar</small>
        </div>
        
        <div style="background: #e2e3e5; padding: 20px; border-radius: 8px; border-left: 4px solid #6c757d;">
            <h4 style="margin: 0 0 10px 0;">Passwords por Expirar</h4>
            <p style="font-size: 2em; margin: 0; font-weight: bold;" id="contadorPasswordsExpirar">0</p>
            <small>En 14 dias</small>
        </div>
    </div>
    
    <hr>
    
    <h3>Enviar Notificaciones</h3>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <p>Enviar notificaciones por email a los responsables y administradores.</p>
        
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button onclick="enviarNotificaciones('acciones')" class="btn-notif" style="background: #ffc107; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                Notificar Acciones
            </button>
            <button onclick="enviarNotificaciones('evidencias')" class="btn-notif" style="background: #17a2b8; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                Notificar Evidencias
            </button>
            <button onclick="enviarNotificaciones('todas')" class="btn-notif" style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                Enviar Todas
            </button>
        </div>
        
        <div id="resultadoEnvio" style="margin-top: 15px;"></div>
    </div>
    
    <hr>
    
    <h3>Detalle de Acciones Proximas a Vencer</h3>
    <div id="tablaAccionesProximas"></div>
    
    <h3>Detalle de Acciones Vencidas</h3>
    <div id="tablaAccionesVencidas"></div>
    
    <h3>Detalle de Evidencias Pendientes</h3>
    <div id="tablaEvidenciasPendientes"></div>

</div>

<hr>
<p><a href="/dashboard">Volver al Dashboard</a> | <a href="/configuracion">Configuracion</a></p>

<script>
const csrfToken = '<?= $csrfToken ?>';

document.addEventListener('DOMContentLoaded', function() {
    cargarResumen();
});

function cargarResumen() {
    fetch('/notificaciones/resumen')
        .then(res => res.json())
        .then(result => {
            document.getElementById('loadingNotificaciones').style.display = 'none';
            document.getElementById('resumenNotificaciones').style.display = 'block';
            
            if (result.success) {
                document.getElementById('contadorAccionesProximas').textContent = result.contadores.acciones_proximas;
                document.getElementById('contadorAccionesVencidas').textContent = result.contadores.acciones_vencidas;
                document.getElementById('contadorEvidenciasPendientes').textContent = result.contadores.evidencias_pendientes;
                document.getElementById('contadorPasswordsExpirar').textContent = result.contadores.passwords_por_expirar;
                
                renderTablaAcciones(result.data.acciones_proximas, 'tablaAccionesProximas', 'dias_restantes');
                renderTablaAcciones(result.data.acciones_vencidas, 'tablaAccionesVencidas', 'dias_vencido');
                renderTablaEvidencias(result.data.evidencias_pendientes);
            }
        })
        .catch(err => {
            document.getElementById('loadingNotificaciones').innerHTML = '<p style="color: red;">Error al cargar datos</p>';
        });
}

function renderTablaAcciones(acciones, containerId, campoDias) {
    const container = document.getElementById(containerId);
    
    if (!acciones || acciones.length === 0) {
        container.innerHTML = '<p style="color: green;">No hay acciones pendientes</p>';
        return;
    }
    
    let html = '<table style="width: 100%; border-collapse: collapse;">';
    html += '<thead><tr style="background: #f8f9fa;">';
    html += '<th style="padding: 8px; border: 1px solid #ddd;">Control</th>';
    html += '<th style="padding: 8px; border: 1px solid #ddd;">Descripcion</th>';
    html += '<th style="padding: 8px; border: 1px solid #ddd;">Responsable</th>';
    html += '<th style="padding: 8px; border: 1px solid #ddd;">Fecha Limite</th>';
    html += '<th style="padding: 8px; border: 1px solid #ddd;">Dias</th>';
    html += '</tr></thead><tbody>';
    
    acciones.forEach(a => {
        const diasStyle = a[campoDias] <= 1 ? 'color: #dc3545; font-weight: bold;' : 
                         (a[campoDias] <= 3 ? 'color: #ffc107;' : '');
        html += '<tr>';
        html += '<td style="padding: 8px; border: 1px solid #ddd;">' + (a.control_codigo || '-') + '</td>';
        html += '<td style="padding: 8px; border: 1px solid #ddd;">' + (a.descripcion || '').substring(0, 80) + '</td>';
        html += '<td style="padding: 8px; border: 1px solid #ddd;">' + (a.responsable_nombre || a.responsable || '-') + '</td>';
        html += '<td style="padding: 8px; border: 1px solid #ddd;">' + (a.fecha_compromiso || '-') + '</td>';
        html += '<td style="padding: 8px; border: 1px solid #ddd; ' + diasStyle + '">' + a[campoDias] + '</td>';
        html += '</tr>';
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

function renderTablaEvidencias(evidencias) {
    const container = document.getElementById('tablaEvidenciasPendientes');
    
    if (!evidencias || evidencias.length === 0) {
        container.innerHTML = '<p style="color: green;">No hay evidencias pendientes de validacion</p>';
        return;
    }
    
    let html = '<table style="width: 100%; border-collapse: collapse;">';
    html += '<thead><tr style="background: #f8f9fa;">';
    html += '<th style="padding: 8px; border: 1px solid #ddd;">Control</th>';
    html += '<th style="padding: 8px; border: 1px solid #ddd;">Archivo</th>';
    html += '<th style="padding: 8px; border: 1px solid #ddd;">Subido por</th>';
    html += '<th style="padding: 8px; border: 1px solid #ddd;">Fecha</th>';
    html += '<th style="padding: 8px; border: 1px solid #ddd;">Dias Pendiente</th>';
    html += '</tr></thead><tbody>';
    
    evidencias.forEach(e => {
        html += '<tr>';
        html += '<td style="padding: 8px; border: 1px solid #ddd;">' + (e.control_codigo || '-') + '</td>';
        html += '<td style="padding: 8px; border: 1px solid #ddd;">' + (e.nombre_archivo || '-') + '</td>';
        html += '<td style="padding: 8px; border: 1px solid #ddd;">' + (e.subido_por_nombre || '-') + '</td>';
        html += '<td style="padding: 8px; border: 1px solid #ddd;">' + (e.created_at || '-') + '</td>';
        html += '<td style="padding: 8px; border: 1px solid #ddd;">' + (e.dias_pendiente || '-') + '</td>';
        html += '</tr>';
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

function enviarNotificaciones(tipo) {
    const buttons = document.querySelectorAll('.btn-notif');
    buttons.forEach(btn => btn.disabled = true);
    
    document.getElementById('resultadoEnvio').innerHTML = '<p>Enviando notificaciones...</p>';
    
    fetch('/notificaciones/enviar', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({csrf_token: csrfToken, tipo: tipo})
    })
    .then(res => res.json())
    .then(result => {
        buttons.forEach(btn => btn.disabled = false);
        
        if (result.success) {
            let mensaje = '<p style="color: green;"><strong>Notificaciones enviadas correctamente</strong></p>';
            
            if (result.resultado) {
                if (result.resultado.acciones) {
                    mensaje += '<p>Acciones: ' + result.resultado.acciones.enviados + ' enviados, ' + result.resultado.acciones.errores + ' errores</p>';
                }
                if (result.resultado.evidencias) {
                    mensaje += '<p>Evidencias: ' + result.resultado.evidencias.enviados + ' enviados, ' + result.resultado.evidencias.errores + ' errores</p>';
                }
            }
            
            document.getElementById('resultadoEnvio').innerHTML = mensaje;
        } else {
            document.getElementById('resultadoEnvio').innerHTML = '<p style="color: red;">Error: ' + (result.error || 'Error desconocido') + '</p>';
        }
    })
    .catch(err => {
        buttons.forEach(btn => btn.disabled = false);
        document.getElementById('resultadoEnvio').innerHTML = '<p style="color: red;">Error de conexion</p>';
    });
}
</script>
