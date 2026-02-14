<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ISO 27001 Compliance Platform</title>
    <!-- Aquí irán los estilos CSS en futuras fases -->
    <style>
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .modal-content h2 {
            margin: 0 0 10px 0;
            color: #c0392b;
        }
        .modal-content p {
            margin: 0 0 20px 0;
            color: #666;
        }
        .modal-content label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .modal-content input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .modal-content button {
            width: 100%;
            padding: 12px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .modal-content button:hover {
            background: #219a52;
        }
        .modal-content button:disabled {
            background: #95a5a6;
            cursor: not-allowed;
        }
        .modal-message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .modal-message.error {
            background: #fee;
            color: #c0392b;
            border: 1px solid #c0392b;
        }
        .modal-message.success {
            background: #efe;
            color: #27ae60;
            border: 1px solid #27ae60;
        }
    </style>
</head>
<body>
    
    <?php include __DIR__ . '/../dashboard/header.php'; ?>
    
    <div class="container">
        
        <?php include __DIR__ . '/../dashboard/sidebar.php'; ?>
        
        <main class="main-content">
            
            <?php include __DIR__ . '/../dashboard/sections/resumen.php'; ?>
            
            <div class="cards-grid">
                <?php include __DIR__ . '/../dashboard/cards/controles.php'; ?>
                <?php include __DIR__ . '/../dashboard/cards/requerimientos.php'; ?>
                <?php include __DIR__ . '/../dashboard/cards/gaps.php'; ?>
                <?php include __DIR__ . '/../dashboard/cards/evidencias.php'; ?>
            </div>
            
        </main>
        
    </div>

    <?php
    use App\Core\Session;
    use App\Middleware\CsrfMiddleware;
    $session = new Session();
    $debeCambiarPassword = $session->get('debe_cambiar_password', false);
    $csrfTokenModal = CsrfMiddleware::getToken();
    ?>
    
    <?php if ($debeCambiarPassword): ?>
    <div class="modal-overlay" id="modalCambiarPassword">
        <div class="modal-content">
            <h2>Cambio de Contrasena Obligatorio</h2>
            <p>Tu contrasena fue reseteada por el administrador. Debes crear una nueva contrasena para continuar.</p>
            
            <div id="modalMessage" class="modal-message" style="display: none;"></div>
            
            <form id="formCambiarPassword">
                <input type="hidden" name="csrf_token" value="<?= $csrfTokenModal ?>">
                
                <label for="password_nueva">Nueva Contrasena</label>
                <input type="password" id="password_nueva" name="password_nueva" required minlength="8" placeholder="Minimo 8 caracteres">
                
                <label for="password_confirmar">Confirmar Contrasena</label>
                <input type="password" id="password_confirmar" name="password_confirmar" required minlength="8" placeholder="Repite la contrasena">
                
                <button type="submit" id="btnCambiarPassword">Cambiar Contrasena</button>
            </form>
        </div>
    </div>
    
    <script>
    document.getElementById('formCambiarPassword').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const btn = document.getElementById('btnCambiarPassword');
        const messageDiv = document.getElementById('modalMessage');
        
        const passNueva = formData.get('password_nueva');
        const passConfirmar = formData.get('password_confirmar');
        
        if (passNueva !== passConfirmar) {
            messageDiv.className = 'modal-message error';
            messageDiv.textContent = 'Las contrasenas no coinciden';
            messageDiv.style.display = 'block';
            return;
        }
        
        if (passNueva.length < 8) {
            messageDiv.className = 'modal-message error';
            messageDiv.textContent = 'La contrasena debe tener al menos 8 caracteres';
            messageDiv.style.display = 'block';
            return;
        }
        
        btn.disabled = true;
        btn.textContent = 'Guardando...';
        messageDiv.style.display = 'none';
        
        fetch('/perfil/cambiar-password-obligatorio', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams(formData)
        })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                messageDiv.className = 'modal-message success';
                messageDiv.textContent = 'Contrasena actualizada correctamente. Redirigiendo...';
                messageDiv.style.display = 'block';
                setTimeout(() => {
                    document.getElementById('modalCambiarPassword').style.display = 'none';
                    if (result.redirect) {
                        window.location.href = result.redirect;
                    } else {
                        window.location.reload();
                    }
                }, 1500);
            } else {
                messageDiv.className = 'modal-message error';
                messageDiv.textContent = result.error || 'Error al cambiar contrasena';
                messageDiv.style.display = 'block';
                btn.disabled = false;
                btn.textContent = 'Cambiar Contrasena';
            }
        })
        .catch(err => {
            messageDiv.className = 'modal-message error';
            messageDiv.textContent = 'Error de conexion. Intenta de nuevo.';
            messageDiv.style.display = 'block';
            btn.disabled = false;
            btn.textContent = 'Cambiar Contrasena';
        });
    });
    </script>
    <?php endif; ?>
    
</body>
</html>
