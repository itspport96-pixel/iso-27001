<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
?>

<h2>Controles ISO 27001</h2>

<h3>Estadísticas Generales</h3>
<p>Total de controles: <?= $estadisticas['total'] ?></p>
<p>Controles NO aplicables: <?= $estadisticas['no_aplicables'] ?></p>
<p>Controles aplicables: <?= $estadisticas['aplicables'] ?></p>
<p>Implementados: <?= $estadisticas['implementados'] ?> (<?= $estadisticas['porcentaje'] ?>%)</p>
<p>Parciales: <?= $estadisticas['parciales'] ?></p>
<p>No implementados: <?= $estadisticas['no_implementados'] ?></p>

<hr>

<h3>Búsqueda en tiempo real</h3>
<input type="text" id="searchInput" placeholder="Buscar por código, nombre o descripción..." autocomplete="off">

<hr>

<h3>Filtros</h3>
<label>Dominio:</label>
<select id="filterDominio">
    <option value="">Todos</option>
    <?php foreach ($dominios as $dominio): ?>
        <option value="<?= $dominio['id'] ?>"><?= htmlspecialchars($dominio['codigo']) ?> - <?= htmlspecialchars($dominio['nombre']) ?></option>
    <?php endforeach; ?>
</select>

<label>Estado:</label>
<select id="filterEstado">
    <option value="">Todos</option>
    <option value="no_implementado">No Implementado</option>
    <option value="parcial">Parcial</option>
    <option value="implementado">Implementado</option>
</select>

<label>Aplicabilidad:</label>
<select id="filterAplicable">
    <option value="">Todos</option>
    <option value="1">Aplicables</option>
    <option value="0">No Aplicables</option>
</select>

<hr>

<div id="loadingSpinner" style="display: none;">Cargando...</div>

<div id="controlesContainer">
    <?php if (empty($soas)): ?>
        <p>No hay controles disponibles</p>
    <?php else: ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Dominio</th>
                    <th>Aplicable</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="controlesTableBody">
                <?php foreach ($soas as $soa): ?>
                    <tr>
                        <td><?= htmlspecialchars($soa['codigo']) ?></td>
                        <td><?= htmlspecialchars($soa['control_nombre']) ?></td>
                        <td><?= htmlspecialchars($soa['dominio_nombre']) ?></td>
                        <td><?= $soa['aplicable'] ? 'Sí' : 'No' ?></td>
                        <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $soa['estado']))) ?></td>
                        <td><a href="/controles/<?= $soa['id'] ?>">Ver/Editar</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div id="paginationContainer"></div>

<br>
<p><a href="/dashboard">Volver al Dashboard</a></p>

<script>
(function() {
    'use strict';
    
    const state = {
        currentPage: 1,
        perPage: 10,
        searchQuery: '',
        filters: { dominio: '', estado: '', aplicable: '' },
        searchTimeout: null,
        isSearching: false
    };

    const el = {
        searchInput: document.getElementById('searchInput'),
        filterDominio: document.getElementById('filterDominio'),
        filterEstado: document.getElementById('filterEstado'),
        filterAplicable: document.getElementById('filterAplicable'),
        container: document.getElementById('controlesContainer'),
        tbody: document.getElementById('controlesTableBody'),
        pagination: document.getElementById('paginationContainer'),
        loading: document.getElementById('loadingSpinner')
    };

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function setLoading(isLoading) {
        if (el.loading) el.loading.style.display = isLoading ? 'block' : 'none';
    }

    async function loadControles() {
        if (state.isSearching) return;
        
        state.isSearching = true;
        setLoading(true);

        try {
            const params = new URLSearchParams({
                page: state.currentPage,
                per_page: state.perPage,
                search: state.searchQuery,
                dominio: state.filters.dominio,
                estado: state.filters.estado,
                aplicable: state.filters.aplicable
            });

            const response = await fetch('/controles/search?' + params.toString(), {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }

            const data = await response.json();

            if (data.success) {
                renderControles(data.data);
                renderPagination(data.pagination);
            } else {
                showError(data.error || 'Error al cargar controles');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('Error de conexión. Por favor, intenta nuevamente.');
        } finally {
            state.isSearching = false;
            setLoading(false);
        }
    }

    function renderControles(controles) {
        if (!el.container) return;

        if (controles.length === 0) {
            el.container.innerHTML = '<p>No se encontraron controles</p>';
            return;
        }

        const rows = controles.map(soa => {
            const aplicable = soa.aplicable ? 'Sí' : 'No';
            const estado = soa.estado.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());

            return `
                <tr>
                    <td>${escapeHtml(soa.codigo)}</td>
                    <td>${escapeHtml(soa.control_nombre)}</td>
                    <td>${escapeHtml(soa.dominio_nombre)}</td>
                    <td>${aplicable}</td>
                    <td>${estado}</td>
                    <td><a href="/controles/${soa.id}">Ver/Editar</a></td>
                </tr>
            `;
        }).join('');

        if (el.tbody) {
            el.tbody.innerHTML = rows;
        } else {
            el.container.innerHTML = `
                <table border="1" cellpadding="5" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Dominio</th>
                            <th>Aplicable</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="controlesTableBody">${rows}</tbody>
                </table>
            `;
            el.tbody = document.getElementById('controlesTableBody');
        }
    }

    function renderPagination(pagination) {
        if (!el.pagination) return;
        if (pagination.last_page <= 1) {
            el.pagination.innerHTML = '';
            return;
        }

        const pages = [];
        const current = pagination.page;
        const last = pagination.last_page;

        pages.push(`<button onclick="window.controlesApp.goToPage(${current - 1})" ${current === 1 ? 'disabled' : ''}>← Anterior</button>`);

        if (current > 3) {
            pages.push(`<button onclick="window.controlesApp.goToPage(1)">1</button>`);
            if (current > 4) pages.push('<span>...</span>');
        }

        for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
            const isActive = i === current;
            pages.push(`<button onclick="window.controlesApp.goToPage(${i})" ${isActive ? 'disabled' : ''}>${i}</button>`);
        }

        if (current < last - 2) {
            if (current < last - 3) pages.push('<span>...</span>');
            pages.push(`<button onclick="window.controlesApp.goToPage(${last})">${last}</button>`);
        }

        pages.push(`<button onclick="window.controlesApp.goToPage(${current + 1})" ${current === last ? 'disabled' : ''}>Siguiente →</button>`);

        el.pagination.innerHTML = pages.join('') + `<p>Mostrando ${(current - 1) * pagination.per_page + 1} - ${Math.min(current * pagination.per_page, pagination.total)} de ${pagination.total} controles</p>`;
    }

    function showError(message) {
        if (el.container) {
            el.container.innerHTML = '<p><strong>Error:</strong> ' + escapeHtml(message) + '</p>';
        }
    }

    if (el.searchInput) {
        el.searchInput.addEventListener('input', function(e) {
            clearTimeout(state.searchTimeout);
            state.searchTimeout = setTimeout(() => {
                state.searchQuery = e.target.value.trim();
                state.currentPage = 1;
                loadControles();
            }, 300);
        });
    }

    [el.filterDominio, el.filterEstado, el.filterAplicable].forEach((element, index) => {
        if (element) {
            const names = ['dominio', 'estado', 'aplicable'];
            element.addEventListener('change', function(e) {
                state.filters[names[index]] = e.target.value;
                state.currentPage = 1;
                loadControles();
            });
        }
    });

    window.controlesApp = {
        goToPage: function(page) {
            if (page < 1) return;
            state.currentPage = page;
            loadControles();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    };
})();
</script>
