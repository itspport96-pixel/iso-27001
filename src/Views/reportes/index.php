<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
?>

<h2>Centro de Reportes</h2>

<p>Genera y descarga reportes de cumplimiento ISO 27001.</p>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 30px 0;">

    <!-- Reporte Ejecutivo -->
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #3498db;">
        <h3 style="margin-top: 0; color: #2c3e50;">Reporte Ejecutivo</h3>
        <p>Resumen de alto nivel del estado de cumplimiento ISO 27001. Ideal para presentar a la dirección.</p>
        <ul style="font-size: 0.9em; color: #666;">
            <li>KPIs de cumplimiento</li>
            <li>Estado de implementación</li>
            <li>GAPs y acciones pendientes</li>
        </ul>
        <div style="margin-top: 15px;">
            <a href="/reportes/preview?tipo=ejecutivo" target="_blank" style="margin-right: 10px;">Vista Previa</a>
            <a href="/reportes/descargar/ejecutivo" style="background: #3498db; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none;">Descargar HTML</a>
        </div>
    </div>

    <!-- SOA -->
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #27ae60;">
        <h3 style="margin-top: 0; color: #2c3e50;">Declaración de Aplicabilidad (SOA)</h3>
        <p>Documento completo con todos los controles ISO 27001 y su estado de aplicabilidad.</p>
        <ul style="font-size: 0.9em; color: #666;">
            <li>93 controles del Anexo A</li>
            <li>Justificación de aplicabilidad</li>
            <li>Estado de implementación</li>
        </ul>
        <div style="margin-top: 15px;">
            <a href="/reportes/preview?tipo=soa" target="_blank" style="margin-right: 10px;">Vista Previa</a>
            <a href="/reportes/descargar/soa" style="background: #27ae60; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; margin-right: 5px;">HTML</a>
            <a href="/reportes/exportar/soa" style="background: #2ecc71; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none;">Excel/CSV</a>
        </div>
    </div>

    <!-- GAPs y Acciones -->
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #e67e22;">
        <h3 style="margin-top: 0; color: #2c3e50;">GAPs y Plan de Acción</h3>
        <p>Listado de brechas identificadas y acciones correctivas asociadas.</p>
        <ul style="font-size: 0.9em; color: #666;">
            <li>Brechas por control</li>
            <li>Acciones correctivas</li>
            <li>Responsables y fechas</li>
        </ul>
        <div style="margin-top: 15px;">
            <a href="/reportes/preview?tipo=gaps" target="_blank" style="margin-right: 10px;">Vista Previa</a>
            <a href="/reportes/descargar/gaps" style="background: #e67e22; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; margin-right: 5px;">HTML</a>
            <a href="/reportes/exportar/gaps" style="background: #f39c12; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none;">Excel/CSV</a>
        </div>
    </div>

    <!-- Evidencias -->
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #9b59b6;">
        <h3 style="margin-top: 0; color: #2c3e50;">Inventario de Evidencias</h3>
        <p>Listado completo de evidencias subidas al sistema con su estado de validación.</p>
        <ul style="font-size: 0.9em; color: #666;">
            <li>Evidencias por control</li>
            <li>Estado de validación</li>
            <li>Fechas y responsables</li>
        </ul>
        <div style="margin-top: 15px;">
            <a href="/reportes/exportar/evidencias" style="background: #9b59b6; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none;">Exportar Excel/CSV</a>
        </div>
    </div>

</div>

<hr>

<h3>Instrucciones para generar PDF</h3>
<p style="color: #666;">Los reportes se generan en formato HTML optimizado para impresión. Para convertir a PDF:</p>
<ol style="color: #666;">
    <li>Haz clic en "Vista Previa" para abrir el reporte en una nueva ventana</li>
    <li>Usa la función de imprimir del navegador (Ctrl+P / Cmd+P)</li>
    <li>Selecciona "Guardar como PDF" como destino</li>
    <li>Ajusta los márgenes si es necesario y guarda</li>
</ol>

<hr>
<p><a href="/dashboard">Volver al Dashboard</a></p>
