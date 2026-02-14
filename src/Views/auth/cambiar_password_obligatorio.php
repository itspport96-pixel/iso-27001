<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contrasena - ISO 27001 Platform</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 420px;
            width: 100%;
        }
        .icon {
            width: 60px;
            height: 60px;
            background: #e74c3c;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .icon svg {
            width: 30px;
            height: 30px;
            fill: white;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 0.9rem;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input[type="password"]:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        .requirements {
            font-size: 0.8rem;
            color: #95a5a6;
            margin-top: 8px;
        }
        button {
            width: 100%;
            padding: 14px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }
        button:hover {
            background: #219a52;
        }
        button:active {
            transform: scale(0.98);
        }
        button:disabled {
            background: #95a5a6;
            cursor: not-allowed;
            transform: none;
        }
        .message {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .message.error {
            background: #fdecea;
            color: #c0392b;
            border: 1px solid #e74c3c;
        }
        .message.success {
            background: #eafaf1;
            color: #27ae60;
            border: 1px solid #27ae60;
        }
        .logout-link {
            text-align: center;
            margin-top: 20px;
        }
        .logout-link a {
            color: #7f8c8d;
            text-decoration: none;
            font-size: 0.85rem;
        }
        .logout-link a:hover {
            color: #e74c3c;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 1C8.676 1 6 3.676 6 7v2H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V11c0-1.1-.9-2-2-2h-2V7c0-3.324-2.676-6-6-6zm0 2c2.276 0 4 1.724 4 4v2H8V7c0-2.276 1.724-4 4-4zm0 10c1.1 0 2 .9 2 2 0 .74-.4 1.38-1 1.72V19h-2v-2.28c-.6-.34-1-.98-1-1.72 0-1.1.9-2 2-2z"/>
            </svg>
        </div>
        
        <h1>Cambio de Contrasena Obligatorio</h1>
        <p class="subtitle">
            Tu contrasena fue reseteada por el administrador.<br>
            Debes crear una nueva contrasena para continuar.
        </p>
        
        <div id="message" class="message" style="display: none;"></div>
        
        <form id="formCambiarPassword">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            
            <div class="form-group">
                <label for="password_nueva">Nueva Contrasena</label>
                <input type="password" id="password_nueva" name="password_nueva" required minlength="8" placeholder="Ingresa tu nueva contrasena">
                <p class="requirements">Minimo 8 caracteres</p>
            </div>
            
            <div class="form-group">
                <label for="password_confirmar">Confirmar Contrasena</label>
                <input type="password" id="password_confirmar" name="password_confirmar" required minlength="8" placeholder="Repite la contrasena">
            </div>
            
            <button type="submit" id="btnSubmit">Cambiar Contrasena</button>
        </form>
        
        <div class="logout-link">
            <a href="/logout">Cerrar sesion</a>
        </div>
    </div>
    
    <script>
    document.getElementById('formCambiarPassword').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const btn = document.getElementById('btnSubmit');
        const messageDiv = document.getElementById('message');
        
        const passNueva = formData.get('password_nueva');
        const passConfirmar = formData.get('password_confirmar');
        
        messageDiv.style.display = 'none';
        
        if (passNueva !== passConfirmar) {
            messageDiv.className = 'message error';
            messageDiv.textContent = 'Las contrasenas no coinciden';
            messageDiv.style.display = 'block';
            return;
        }
        
        if (passNueva.length < 8) {
            messageDiv.className = 'message error';
            messageDiv.textContent = 'La contrasena debe tener al menos 8 caracteres';
            messageDiv.style.display = 'block';
            return;
        }
        
        btn.disabled = true;
        btn.textContent = 'Guardando...';
        
        fetch('/perfil/cambiar-password-obligatorio', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams(formData)
        })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                messageDiv.className = 'message success';
                messageDiv.textContent = 'Contrasena actualizada. Redirigiendo al dashboard...';
                messageDiv.style.display = 'block';
                btn.textContent = 'Redirigiendo...';
                setTimeout(() => {
                    window.location.href = result.redirect || '/dashboard';
                }, 1500);
            } else {
                messageDiv.className = 'message error';
                messageDiv.textContent = result.error || 'Error al cambiar contrasena';
                messageDiv.style.display = 'block';
                btn.disabled = false;
                btn.textContent = 'Cambiar Contrasena';
            }
        })
        .catch(err => {
            messageDiv.className = 'message error';
            messageDiv.textContent = 'Error de conexion. Intenta de nuevo.';
            messageDiv.style.display = 'block';
            btn.disabled = false;
            btn.textContent = 'Cambiar Contrasena';
        });
    });
    </script>
</body>
</html>
