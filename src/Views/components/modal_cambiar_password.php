<?php
use App\Core\Session;
use App\Middleware\CsrfMiddleware;

$session = new Session();
$debeCambiarPassword = $session->get('debe_cambiar_password', false);
$csrfTokenModal = CsrfMiddleware::getToken();
?>

<?php if ($debeCambiarPassword): ?>
<style>
    .modal-overlay-cp {
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
    .modal-content-cp {
        background: white;
        padding: 30px;
        border-radius: 8px;
        max-width: 400px;
        width: 90%;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    }
    .modal-content-cp h2 {
        margin: 0 0 10px 0;
        color: #c0392b;
        font-size: 1.4rem;
    }
    .modal-content-cp p {
        margin: 0 0 20px 0;
        color: #666;
        font-size: 0.9rem;
    }
    .modal-content-cp label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #333;
        font-size: 0.9rem;
    }
    .modal-content-cp input[type="password"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 1rem;
    }
    .modal-content-cp input[type="password"]:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
    }
    .modal-content-cp button {
        width: 100%;
        padding: 12px;
        background: #27ae60;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1rem;
        font-weight: bold;
    }
    .modal-content-cp button:hover {
        background: #219a52;
    }
    .modal-content-cp button:disabled {
        background: #95a5a6;
        cursor: not-allowed;
    }
    .modal-message-cp {
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 4px;
        font-size: 0.9rem;
    }
    .modal-message-cp.error {
        background: #fee;
        color: #c0392b;
        border: 1px solid #c0392b;
    }
    .modal-message-cp.success {
        background: #efe;
        color: #27ae60;
        border: 1px solid #27ae60;
    }
    .password-requirements {
        font-size: 0.8rem;
        color: #888;
        margin-bottom: 15px;
    }
</style>

<div class="modal-overlay-cp" id="modalCambiarPassword">
    <div class="modal-content-cp">
        <h2>Cambio de Contrasena Obligatorio</h2>
        <p>Tu contrasena fue reseteada por el administrador. Debes crear una nueva contrasena para continuar trabajando.</p>
        
        <div id="modalMessageCp" class="modal-message-cp" style="display: none;"></div>
        
        <form id="formCambiarPasswordCp">
            <input type="hidden" name="csrf_token" value="<?= $csrfTokenModal ?>">
            
            <label for="password_nueva_cp">Nueva Contrasena</label>
            <input type="password" id="password_nueva_cp" name="password_nueva" required minlength="8" placeholder="Minimo 8 caracteres">
            
            <label for="password_confirmar_cp">Confirmar Contrasena</label>
            <input type="password" id="password_confirmar_cp" name="password_confirmar" required minlength="8" placeholder="Repite la contrasena">
            
            <div class="password-requirements">
                La contrasena debe tener al menos 8 caracteres.
            </div>
            
            <button type="submit" id="btnCambiarPasswordCp">Cambiar Contrasena</button>
        </form>
    </div>
</div>

<script>
document.getElementById('formCambiarPasswordCp').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const btn = document.getElementById('btnCambiarPasswordCp');
    const messageDiv = document.getElementById('modalMessageCp');
    
    const passNueva = formData.get('password_nueva');
    const passConfirmar = formData.get('password_confirmar');
    
    if (passNueva !== passConfirmar) {
        messageDiv.className = 'modal-message-cp error';
        messageDiv.textContent = 'Las contrasenas no coinciden';
        messageDiv.style.display = 'block';
        return;
    }
    
    if (passNueva.length < 8) {
        messageDiv.className = 'modal-message-cp error';
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
            messageDiv.className = 'modal-message-cp success';
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
            messageDiv.className = 'modal-message-cp error';
            messageDiv.textContent = result.error || 'Error al cambiar contrasena';
            messageDiv.style.display = 'block';
            btn.disabled = false;
            btn.textContent = 'Cambiar Contrasena';
        }
    })
    .catch(err => {
        messageDiv.className = 'modal-message-cp error';
        messageDiv.textContent = 'Error de conexion. Intenta de nuevo.';
        messageDiv.style.display = 'block';
        btn.disabled = false;
        btn.textContent = 'Cambiar Contrasena';
    });
});
</script>
<?php endif; ?>
