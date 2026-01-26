<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
?>

<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 text-center">
            Crear Nueva Cuenta
        </h2>
        <p class="mt-2 text-sm text-gray-600 text-center">
            Completa el registro en 2 pasos
        </p>
    </div>

    <!-- Stepper -->
    <div class="flex items-center justify-center space-x-4">
        <div class="flex items-center">
            <div id="step1-indicator" class="w-8 h-8 rounded-full bg-primary-600 text-white flex items-center justify-center font-semibold text-sm">
                1
            </div>
            <span id="step1-label" class="ml-2 text-sm font-medium text-gray-900">Datos Empresa</span>
        </div>
        <div class="w-16 h-0.5 bg-gray-300" id="step-connector"></div>
        <div class="flex items-center">
            <div id="step2-indicator" class="w-8 h-8 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-semibold text-sm">
                2
            </div>
            <span id="step2-label" class="ml-2 text-sm font-medium text-gray-500">Usuario Admin</span>
        </div>
    </div>

    <form id="registerForm" class="bg-white rounded-lg">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        
        <!-- PASO 1: Datos de la Empresa -->
        <div id="step1" class="step-content">
            <div class="space-y-5">
                <div>
                    <label for="empresa_nombre" class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre de la Empresa *
                    </label>
                    <input 
                        type="text" 
                        id="empresa_nombre" 
                        name="empresa_nombre" 
                        required
                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        placeholder="Mi Empresa S.A."
                    >
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <label for="empresa_ruc" class="block text-sm font-medium text-gray-700 mb-1">
                            RUC *
                        </label>
                        <input 
                            type="text" 
                            id="empresa_ruc" 
                            name="empresa_ruc" 
                            required
                            maxlength="20"
                            class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            placeholder="20123456789"
                        >
                    </div>

                    <div>
                        <label for="empresa_telefono" class="block text-sm font-medium text-gray-700 mb-1">
                            Teléfono
                        </label>
                        <input 
                            type="tel" 
                            id="empresa_telefono" 
                            name="empresa_telefono"
                            class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            placeholder="+51 999 999 999"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <label for="empresa_email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email de Contacto *
                        </label>
                        <input 
                            type="email" 
                            id="empresa_email" 
                            name="empresa_email" 
                            required
                            class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            placeholder="contacto@empresa.com"
                        >
                    </div>

                    <div>
                        <label for="empresa_contacto" class="block text-sm font-medium text-gray-700 mb-1">
                            Persona de Contacto
                        </label>
                        <input 
                            type="text" 
                            id="empresa_contacto" 
                            name="empresa_contacto"
                            class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            placeholder="Juan Pérez"
                        >
                    </div>
                </div>

                <div>
                    <label for="empresa_direccion" class="block text-sm font-medium text-gray-700 mb-1">
                        Dirección
                    </label>
                    <textarea 
                        id="empresa_direccion" 
                        name="empresa_direccion" 
                        rows="2"
                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 resize-none"
                        placeholder="Av. Principal 123, Lima"
                    ></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button 
                    type="button" 
                    id="btnNext"
                    class="px-6 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 font-medium"
                >
                    Continuar →
                </button>
            </div>
        </div>

        <!-- PASO 2: Usuario Administrador -->
        <div id="step2" class="step-content hidden">
            <div class="space-y-5">
                <div>
                    <label for="usuario_nombre" class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre Completo *
                    </label>
                    <input 
                        type="text" 
                        id="usuario_nombre" 
                        name="usuario_nombre" 
                        required
                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        placeholder="Juan Pérez García"
                    >
                </div>

                <div>
                    <label for="usuario_email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email *
                    </label>
                    <input 
                        type="email" 
                        id="usuario_email" 
                        name="usuario_email" 
                        required
                        autocomplete="username"
                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        placeholder="admin@empresa.com"
                    >
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Contraseña *
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            autocomplete="new-password"
                            minlength="8"
                            class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            placeholder="Mínimo 8 caracteres"
                        >
                    </div>

                    <div>
                        <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-1">
                            Confirmar Contraseña *
                        </label>
                        <input 
                            type="password" 
                            id="password_confirm" 
                            name="password_confirm" 
                            required
                            autocomplete="new-password"
                            minlength="8"
                            class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            placeholder="Repite la contraseña"
                        >
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <p class="text-xs text-blue-800">
                        <strong>Nota:</strong> La contraseña debe tener al menos 8 caracteres.
                    </p>
                </div>

                <div class="flex items-start">
                    <input 
                        id="terms" 
                        name="terms" 
                        type="checkbox" 
                        required
                        class="mt-1 h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                    >
                    <label for="terms" class="ml-2 block text-sm text-gray-700">
                        Acepto los términos y condiciones de uso de la plataforma
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <button 
                    type="button" 
                    id="btnBack"
                    class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 font-medium"
                >
                    ← Atrás
                </button>
                <button 
                    type="submit" 
                    class="px-6 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 font-medium flex items-center"
                >
                    <span id="buttonText">Crear Cuenta</span>
                    <svg id="buttonSpinner" class="hidden animate-spin ml-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </form>

    <!-- Mensaje de respuesta -->
    <div id="message" class="hidden"></div>

    <!-- Login -->
    <div class="text-center pt-4 border-t border-gray-200">
        <p class="text-sm text-gray-600">
            ¿Ya tienes cuenta? 
            <a href="/login" class="font-medium text-primary-600 hover:text-primary-500">
                Iniciar Sesión
            </a>
        </p>
    </div>
</div>

<script>
// Wizard navigation
const step1 = document.getElementById('step1');
const step2 = document.getElementById('step2');
const btnNext = document.getElementById('btnNext');
const btnBack = document.getElementById('btnBack');
const step1Indicator = document.getElementById('step1-indicator');
const step2Indicator = document.getElementById('step2-indicator');
const step1Label = document.getElementById('step1-label');
const step2Label = document.getElementById('step2-label');
const stepConnector = document.getElementById('step-connector');

// Validar campos obligatorios del paso 1
function validateStep1() {
    const nombre = document.getElementById('empresa_nombre').value.trim();
    const ruc = document.getElementById('empresa_ruc').value.trim();
    const email = document.getElementById('empresa_email').value.trim();
    return nombre !== '' && ruc !== '' && email !== '';
}

// Actualizar estado del botón Continuar
function updateNextButton() {
    btnNext.disabled = !validateStep1();
    if (btnNext.disabled) {
        btnNext.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
        btnNext.classList.remove('opacity-50', 'cursor-not-allowed');
    }
}

// Listeners para validación en tiempo real
document.getElementById('empresa_nombre').addEventListener('input', updateNextButton);
document.getElementById('empresa_ruc').addEventListener('input', updateNextButton);
document.getElementById('empresa_email').addEventListener('input', updateNextButton);

// Ir al paso 2
btnNext.addEventListener('click', function() {
    if (validateStep1()) {
        step1.classList.add('hidden');
        step2.classList.remove('hidden');
        
        // Actualizar stepper
        step1Indicator.classList.remove('bg-primary-600', 'text-white');
        step1Indicator.classList.add('bg-green-500', 'text-white');
        step1Label.classList.remove('text-gray-900');
        step1Label.classList.add('text-green-600');
        
        step2Indicator.classList.remove('bg-gray-300', 'text-gray-600');
        step2Indicator.classList.add('bg-primary-600', 'text-white');
        step2Label.classList.remove('text-gray-500');
        step2Label.classList.add('text-gray-900');
        
        stepConnector.classList.remove('bg-gray-300');
        stepConnector.classList.add('bg-green-500');
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});

// Volver al paso 1
btnBack.addEventListener('click', function() {
    step2.classList.add('hidden');
    step1.classList.remove('hidden');
    
    // Restaurar stepper
    step1Indicator.classList.remove('bg-green-500');
    step1Indicator.classList.add('bg-primary-600');
    step1Label.classList.remove('text-green-600');
    step1Label.classList.add('text-gray-900');
    
    step2Indicator.classList.remove('bg-primary-600', 'text-white');
    step2Indicator.classList.add('bg-gray-300', 'text-gray-600');
    step2Label.classList.remove('text-gray-900');
    step2Label.classList.add('text-gray-500');
    
    stepConnector.classList.remove('bg-green-500');
    stepConnector.classList.add('bg-gray-300');
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

// Submit form
document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    const button = this.querySelector('button[type="submit"]');
    const buttonText = document.getElementById('buttonText');
    const buttonSpinner = document.getElementById('buttonSpinner');
    const messageDiv = document.getElementById('message');
    
    if (data.password !== data.password_confirm) {
        messageDiv.className = 'p-4 rounded-lg bg-red-50 border border-red-200';
        messageDiv.innerHTML = '<p class="text-sm text-red-800"><strong>Error:</strong> Las contraseñas no coinciden</p>';
        messageDiv.classList.remove('hidden');
        return;
    }
    
    button.disabled = true;
    buttonText.textContent = 'Creando cuenta...';
    buttonSpinner.classList.remove('hidden');
    messageDiv.classList.add('hidden');
    
    fetch('/register', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            messageDiv.className = 'p-4 rounded-lg bg-green-50 border border-green-200';
            messageDiv.innerHTML = '<p class="text-sm text-green-800 font-medium">Registro exitoso. Redirigiendo...</p>';
            messageDiv.classList.remove('hidden');
            setTimeout(() => window.location.href = result.redirect, 1500);
        } else {
            messageDiv.className = 'p-4 rounded-lg bg-red-50 border border-red-200';
            messageDiv.innerHTML = '<p class="text-sm text-red-800"><strong>Error:</strong> ' + (result.error || 'Error desconocido') + '</p>';
            messageDiv.classList.remove('hidden');
            
            button.disabled = false;
            buttonText.textContent = 'Crear Cuenta';
            buttonSpinner.classList.add('hidden');
        }
    })
    .catch(err => {
        messageDiv.className = 'p-4 rounded-lg bg-red-50 border border-red-200';
        messageDiv.innerHTML = '<p class="text-sm text-red-800"><strong>Error:</strong> No se pudo conectar con el servidor</p>';
        messageDiv.classList.remove('hidden');
        
        button.disabled = false;
        buttonText.textContent = 'Crear Cuenta';
        buttonSpinner.classList.add('hidden');
    });
});

// Inicializar estado del botón
updateNextButton();
</script>
