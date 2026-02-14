<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
$mesActual = date('n');
$anioActual = date('Y');
$meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
          'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
?>

<h2>Calendario de Auditorias</h2>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <button onclick="mesAnterior()" style="padding: 8px 15px; cursor: pointer;">&lt; Anterior</button>
        <span id="mesAnioActual" style="font-size: 1.3em; font-weight: bold; margin: 0 20px;"></span>
        <button onclick="mesSiguiente()" style="padding: 8px 15px; cursor: pointer;">Siguiente &gt;</button>
    </div>
    <button onclick="mostrarFormAuditoria()" style="background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
        + Nueva Auditoria
    </button>
</div>

<div style="display: flex; gap: 10px; margin-bottom: 20px;">
    <span style="display: inline-flex; align-items: center; gap: 5px;">
        <span style="width: 15px; height: 15px; background: #3498db; border-radius: 3px;"></span> Interna
    </span>
    <span style="display: inline-flex; align-items: center; gap: 5px;">
        <span style="width: 15px; height: 15px; background: #e74c3c; border-radius: 3px;"></span> Externa
    </span>
    <span style="display: inline-flex; align-items: center; gap: 5px;">
        <span style="width: 15px; height: 15px; background: #f39c12; border-radius: 3px;"></span> Seguimiento
    </span>
    <span style="display: inline-flex; align-items: center; gap: 5px;">
        <span style="width: 15px; height: 15px; background: #27ae60; border-radius: 3px;"></span> Certificacion
    </span>
</div>

<div id="calendario" style="background: #f8f9fa; padding: 20px; border-radius: 8px; min-height: 400px;">
    <div id="loadingCalendario">Cargando...</div>
    <div id="gridCalendario" style="display: none;"></div>
</div>

<div id="listaEventos" style="margin-top: 30px;">
    <h3>Eventos del Mes</h3>
    <div id="tablaEventos"></div>
</div>

<!-- Modal Nueva Auditoria -->
<div id="modalAuditoria" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: white; max-width: 500px; margin: 50px auto; padding: 30px; border-radius: 8px; max-height: 90vh; overflow-y: auto;">
        <h3 id="modalTitulo">Nueva Auditoria</h3>
        <form id="formAuditoria">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="auditoria_id" id="auditoriaId" value="">
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Titulo *</label>
                <input type="text" name="titulo" id="audTitulo" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Tipo *</label>
                <select name="tipo" id="audTipo" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="interna">Auditoria Interna</option>
                    <option value="externa">Auditoria Externa</option>
                    <option value="seguimiento">Seguimiento</option>
                    <option value="certificacion">Certificacion</option>
                </select>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Auditor Responsable</label>
                <input type="text" name="auditor_responsable" id="audAuditor" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Fecha Inicio *</label>
                    <input type="date" name="fecha_inicio" id="audFechaInicio" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Fecha Fin</label>
                    <input type="date" name="fecha_fin" id="audFechaFin" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Descripcion</label>
                <textarea name="descripcion" id="audDescripcion" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
            </div>
            
            <div id="audMensaje" style="margin-bottom: 15px;"></div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="cerrarModal()" style="padding: 10px 20px; cursor: pointer;">Cancelar</button>
                <button type="submit" style="background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">Guardar</button>
            </div>
        </form>
    </div>
</div>

<hr>
<p><a href="/dashboard">Volver al Dashboard</a></p>

<script>
const csrfToken = '<?= $csrfToken ?>';
const meses = <?= json_encode($meses) ?>;
let mesActual = <?= $mesActual ?>;
let anioActual = <?= $anioActual ?>;

document.addEventListener('DOMContentLoaded', function() {
    cargarCalendario();
});

function cargarCalendario() {
    document.getElementById('loadingCalendario').style.display = 'block';
    document.getElementById('gridCalendario').style.display = 'none';
    document.getElementById('mesAnioActual').textContent = meses[mesActual] + ' ' + anioActual;
    
    fetch('/calendario/eventos?mes=' + mesActual + '&anio=' + anioActual)
        .then(res => res.json())
        .then(result => {
            document.getElementById('loadingCalendario').style.display = 'none';
            document.getElementById('gridCalendario').style.display = 'block';
            
            if (result.success) {
                renderCalendario(result.data);
                renderTablaEventos(result.data);
            }
        })
        .catch(err => {
            document.getElementById('loadingCalendario').innerHTML = '<p style="color: red;">Error al cargar</p>';
        });
}

function renderCalendario(eventos) {
    const grid = document.getElementById('gridCalendario');
    const primerDia = new Date(anioActual, mesActual - 1, 1);
    const ultimoDia = new Date(anioActual, mesActual, 0);
    const diasEnMes = ultimoDia.getDate();
    const diaInicio = primerDia.getDay();
    
    let html = '<div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px;">';
    
    // Cabecera días
    const diasSemana = ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'];
    diasSemana.forEach(d => {
        html += '<div style="background: #2c3e50; color: white; padding: 8px; text-align: center; font-weight: bold;">' + d + '</div>';
    });
    
    // Días vacíos antes
    for (let i = 0; i < diaInicio; i++) {
        html += '<div style="background: #eee; padding: 10px; min-height: 80px;"></div>';
    }
    
    // Días del mes
    for (let dia = 1; dia <= diasEnMes; dia++) {
        const fecha = anioActual + '-' + String(mesActual).padStart(2, '0') + '-' + String(dia).padStart(2, '0');
        const eventosDelDia = eventos.filter(e => e.fecha_inicio === fecha || (e.fecha_fin && fecha >= e.fecha_inicio && fecha <= e.fecha_fin));
        
        const esHoy = (new Date().toISOString().split('T')[0] === fecha) ? 'border: 2px solid #3498db;' : '';
        
        html += '<div style="background: white; padding: 5px; min-height: 80px; ' + esHoy + '">';
        html += '<div style="font-weight: bold; margin-bottom: 5px;">' + dia + '</div>';
        
        eventosDelDia.forEach(e => {
            html += '<div style="background: ' + e.color + '; color: white; font-size: 10px; padding: 2px 4px; margin: 2px 0; border-radius: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="' + e.titulo + '">';
            html += e.titulo.substring(0, 15) + (e.titulo.length > 15 ? '...' : '');
            html += '</div>';
        });
        
        html += '</div>';
    }
    
    html += '</div>';
    grid.innerHTML = html;
}

function renderTablaEventos(eventos) {
    const container = document.getElementById('tablaEventos');
    
    if (!eventos || eventos.length === 0) {
        container.innerHTML = '<p style="color: #666;">No hay eventos programados este mes.</p>';
        return;
    }
    
    let html = '<table style="width: 100%; border-collapse: collapse;">';
    html += '<thead><tr style="background: #f8f9fa;">';
    html += '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Fecha</th>';
    html += '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Tipo</th>';
    html += '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Titulo</th>';
    html += '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Estado</th>';
    html += '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Responsable</th>';
    html += '</tr></thead><tbody>';
    
    eventos.sort((a, b) => a.fecha_inicio.localeCompare(b.fecha_inicio));
    
    eventos.forEach(e => {
        html += '<tr>';
        html += '<td style="padding: 8px; border: 1px solid #ddd;">' + e.fecha_inicio + '</td>';
        html += '<td style="padding: 8px; border: 1px solid #ddd;"><span style="background: ' + e.color + '; color: white; padding: 2px 8px; border-radius: 3px;">' + e.tipo + '</span></td>';
        html += '<td style="padding: 8px; border: 1px solid #ddd;">' + e.titulo + '</td>';
        html += '<td style="padding: 8px; border: 1px solid #ddd;">' + (e.estado || '-') + '</td>';
        html += '<td style="padding: 8px; border: 1px solid #ddd;">' + (e.responsable || '-') + '</td>';
        html += '</tr>';
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

function mesAnterior() {
    mesActual--;
    if (mesActual < 1) {
        mesActual = 12;
        anioActual--;
    }
    cargarCalendario();
}

function mesSiguiente() {
    mesActual++;
    if (mesActual > 12) {
        mesActual = 1;
        anioActual++;
    }
    cargarCalendario();
}

function mostrarFormAuditoria() {
    document.getElementById('modalTitulo').textContent = 'Nueva Auditoria';
    document.getElementById('formAuditoria').reset();
    document.getElementById('auditoriaId').value = '';
    document.getElementById('audMensaje').innerHTML = '';
    document.getElementById('modalAuditoria').style.display = 'block';
}

function cerrarModal() {
    document.getElementById('modalAuditoria').style.display = 'none';
}

document.getElementById('formAuditoria').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const auditoriaId = formData.get('auditoria_id');
    const url = auditoriaId ? '/calendario/auditoria/' + auditoriaId : '/calendario/auditoria';
    
    fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(formData)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            document.getElementById('audMensaje').innerHTML = '<p style="color: green;">Guardado correctamente</p>';
            setTimeout(() => {
                cerrarModal();
                cargarCalendario();
            }, 1000);
        } else {
            document.getElementById('audMensaje').innerHTML = '<p style="color: red;">' + (result.error || 'Error al guardar') + '</p>';
        }
    })
    .catch(err => {
        document.getElementById('audMensaje').innerHTML = '<p style="color: red;">Error de conexion</p>';
    });
});
</script>
