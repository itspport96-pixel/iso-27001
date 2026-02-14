<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
?>

<h2>Gestion de Usuarios</h2>

<p>Usuarios registrados en tu empresa</p>

<p>
    <a href="/usuarios/create">Crear Nuevo Usuario</a>
</p>

<hr>

<h3>Busqueda en tiempo real</h3>
<input type="text" id="searchInput" placeholder="Buscar por nombre o email..." autocomplete="off">

<hr>

<div id="loadingSpinner" style="display: none;">Cargando...</div>

<div id="usuariosContainer">
    <p>Cargando usuarios...</p>
</div>

<div id="paginationContainer"></div>

<br>
<p><a href="/dashboard">Volver al Dashboard</a></p>

<script>
(function() {
    var currentUserId = <?= $user_actual['id'] ?>;
    var csrfToken = '<?= $csrfToken ?>';

    var state = {
        currentPage: 1,
        perPage: 10,
        searchQuery: '',
        searchTimeout: null,
        isSearching: false
    };

    var el = {
        searchInput: document.getElementById('searchInput'),
        container: document.getElementById('usuariosContainer'),
        pagination: document.getElementById('paginationContainer'),
        loading: document.getElementById('loadingSpinner')
    };

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function setLoading(isLoading) {
        if (el.loading) el.loading.style.display = isLoading ? 'block' : 'none';
    }

    function loadUsuarios() {
        if (state.isSearching) return;
        
        state.isSearching = true;
        setLoading(true);

        var params = new URLSearchParams({
            page: state.currentPage,
            per_page: state.perPage,
            search: state.searchQuery
        });

        var xhr = new XMLHttpRequest();
        xhr.open('GET', '/usuarios/search?' + params.toString(), true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onload = function() {
            state.isSearching = false;
            setLoading(false);

            if (xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        renderUsuarios(data.data);
                        renderPagination(data.pagination);
                    } else {
                        showError(data.error || 'Error al cargar usuarios');
                    }
                } catch (e) {
                    showError('Error al procesar la respuesta');
                }
            } else {
                showError('Error de conexion. Por favor, intenta nuevamente.');
            }
        };

        xhr.onerror = function() {
            state.isSearching = false;
            setLoading(false);
            showError('Error de conexion. Por favor, intenta nuevamente.');
        };

        xhr.send();
    }

    function renderUsuarios(usuarios) {
        if (!el.container) return;

        if (usuarios.length === 0) {
            el.container.innerHTML = '<p>No se encontraron usuarios</p>';
            return;
        }

        var rows = '';
        for (var i = 0; i < usuarios.length; i++) {
            var usuario = usuarios[i];
            var rol = usuario.rol.replace(/_/g, ' ');
            rol = rol.charAt(0).toUpperCase() + rol.slice(1);

            var estadoColor = '';
            var estadoTexto = '';
            if (usuario.estado === 'activo') {
                estadoColor = 'green';
                estadoTexto = 'Activo';
            } else if (usuario.estado === 'inactivo') {
                estadoColor = 'orange';
                estadoTexto = 'Inactivo';
            } else {
                estadoColor = 'red';
                estadoTexto = 'Bloqueado';
            }

            var acciones = '';
            if (usuario.id === currentUserId) {
                acciones = '<span style="color: #999;">Tu mismo</span>';
            } else {
                acciones = '<a href="/usuarios/' + usuario.id + '/edit">Editar</a> | ';
                
                acciones += '<a href="#" onclick="resetPasswordUsuario(' + usuario.id + ', \'' + escapeHtml(usuario.nombre).replace(/'/g, "\\'") + '\', \'' + escapeHtml(usuario.email).replace(/'/g, "\\'") + '\'); return false;" style="color: #17a2b8;">Reset Pass</a> | ';
                
                if (usuario.estado === 'activo') {
                    acciones += '<a href="#" onclick="cambiarEstadoUsuario(' + usuario.id + ', \'inactivo\', \'' + escapeHtml(usuario.nombre).replace(/'/g, "\\'") + '\'); return false;">Desactivar</a> | ';
                } else {
                    acciones += '<a href="#" onclick="cambiarEstadoUsuario(' + usuario.id + ', \'activo\', \'' + escapeHtml(usuario.nombre).replace(/'/g, "\\'") + '\'); return false;">Activar</a> | ';
                }
                
                acciones += '<a href="#" onclick="borrarUsuario(' + usuario.id + ', \'' + escapeHtml(usuario.nombre).replace(/'/g, "\\'") + '\'); return false;" style="color: #dc3545;">Borrar</a>';
            }

            rows += '<tr>' +
                '<td>' + escapeHtml(usuario.nombre) + '</td>' +
                '<td>' + escapeHtml(usuario.email) + '</td>' +
                '<td>' + rol + '</td>' +
                '<td><span style="color: ' + estadoColor + ';">' + estadoTexto + '</span></td>' +
                '<td>' + escapeHtml(usuario.ultimo_acceso || 'Nunca') + '</td>' +
                '<td>' + acciones + '</td>' +
                '</tr>';
        }

        el.container.innerHTML = '<table border="1" cellpadding="5" cellspacing="0">' +
            '<thead>' +
            '<tr>' +
            '<th>Nombre</th>' +
            '<th>Email</th>' +
            '<th>Rol</th>' +
            '<th>Estado</th>' +
            '<th>Ultimo Acceso</th>' +
            '<th>Acciones</th>' +
            '</tr>' +
            '</thead>' +
            '<tbody>' + rows + '</tbody>' +
            '</table>';
    }

    function renderPagination(pagination) {
        if (!el.pagination) return;
        if (pagination.last_page <= 1) {
            el.pagination.innerHTML = '';
            return;
        }

        var pages = [];
        var current = pagination.page;
        var last = pagination.last_page;

        pages.push('<button onclick="window.usuariosApp.goToPage(' + (current - 1) + ')" ' + (current === 1 ? 'disabled' : '') + '>Anterior</button>');

        if (current > 3) {
            pages.push('<button onclick="window.usuariosApp.goToPage(1)">1</button>');
            if (current > 4) pages.push('<span>...</span>');
        }

        for (var i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
            var isActive = i === current;
            pages.push('<button onclick="window.usuariosApp.goToPage(' + i + ')" ' + (isActive ? 'disabled' : '') + '>' + i + '</button>');
        }

        if (current < last - 2) {
            if (current < last - 3) pages.push('<span>...</span>');
            pages.push('<button onclick="window.usuariosApp.goToPage(' + last + ')">' + last + '</button>');
        }

        pages.push('<button onclick="window.usuariosApp.goToPage(' + (current + 1) + ')" ' + (current === last ? 'disabled' : '') + '>Siguiente</button>');

        el.pagination.innerHTML = pages.join('') + '<p>Mostrando ' + ((current - 1) * pagination.per_page + 1) + ' - ' + Math.min(current * pagination.per_page, pagination.total) + ' de ' + pagination.total + ' usuarios</p>';
    }

    function showError(message) {
        if (el.container) {
            el.container.innerHTML = '<p><strong>Error:</strong> ' + escapeHtml(message) + '</p>';
        }
    }

    if (el.searchInput) {
        el.searchInput.addEventListener('input', function(e) {
            clearTimeout(state.searchTimeout);
            state.searchTimeout = setTimeout(function() {
                state.searchQuery = e.target.value.trim();
                state.currentPage = 1;
                loadUsuarios();
            }, 300);
        });
    }

    window.usuariosApp = {
        goToPage: function(page) {
            if (page < 1) return;
            state.currentPage = page;
            loadUsuarios();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    };

    window.resetPasswordUsuario = function(id, nombre, email) {
        var mensaje = 'Resetear la contrasena del usuario "' + nombre + '"?\n\nSe generara una nueva contrasena aleatoria y se enviara al email: ' + email;
        
        if (!confirm(mensaje)) {
            return;
        }
        
        fetch('/usuarios/' + id + '/reset-password', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                csrf_token: csrfToken
            })
        })
        .then(function(res) { return res.json(); })
        .then(function(result) {
            if (result.success) {
                if (result.password) {
                    alert(result.message + '\n\nNueva contrasena: ' + result.password);
                } else {
                    alert(result.message);
                }
            } else {
                alert('Error: ' + result.error);
            }
        })
        .catch(function() {
            alert('Error de conexion');
        });
    };

    window.cambiarEstadoUsuario = function(id, nuevoEstado, nombre) {
        var accion = nuevoEstado === 'activo' ? 'activar' : 'desactivar';
        var mensaje = nuevoEstado === 'activo' 
            ? 'Activar el usuario "' + nombre + '"? El usuario podra iniciar sesion nuevamente.'
            : 'Desactivar el usuario "' + nombre + '"? El usuario no podra iniciar sesion pero sus datos se conservaran.';
        
        if (!confirm(mensaje)) {
            return;
        }
        
        fetch('/usuarios/' + id + '/cambiar-estado', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                csrf_token: csrfToken,
                estado: nuevoEstado
            })
        })
        .then(function(res) { return res.json(); })
        .then(function(result) {
            if (result.success) {
                alert(result.message);
                loadUsuarios();
            } else {
                alert('Error: ' + result.error);
            }
        })
        .catch(function() {
            alert('Error de conexion');
        });
    };

    window.borrarUsuario = function(id, nombre) {
        if (!confirm('BORRAR PERMANENTEMENTE el usuario "' + nombre + '"? Esta accion NO se puede deshacer.')) {
            return;
        }
        
        fetch('/usuarios/' + id + '/delete', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({csrf_token: csrfToken})
        })
        .then(function(res) { return res.json(); })
        .then(function(result) {
            if (result.success) {
                alert(result.message);
                loadUsuarios();
            } else {
                alert('Error: ' + result.error);
            }
        })
        .catch(function() {
            alert('Error de conexion');
        });
    };

    loadUsuarios();
})();
</script>
