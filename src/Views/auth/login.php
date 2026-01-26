<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
?>

<div class="space-y-6">
    <!-- Stepper -->
    <div class="flex items-center justify-center space-x-4">
        <div class="flex items-center">
            <div id="step1-indicator" class="w-8 h-8 rounded-full bg-primary-600 text-white flex items-center justify-center font-semibold text-sm">
                1
            </div>
            <span id="step1-label" class="ml-2 text-sm font-medium text-gray-900">Email</span>
        </div>
        <div class="w-16 h-0.5 bg-gray-300" id="step-connector"></div>
        <div class="flex items-center">
            <div id="step2-indicator" class="w-8 h-8 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-semibold text-sm">
                2
            </div>
            <span id="step2-label" class="ml-2 text-sm font-medium text-gray-500">Contraseña</span>
        </div>
    </div>

    <div>
        <h2 class="text-2xl font-bold text-gray-900 text-center">
            Iniciar Sesión
        </h2>
        <p class="mt-2 text-sm text-gray-600 text-center">
            Accede a tu cuenta para continuar
        </p>
    </div>

    <form id="loginForm" class="space-y-5">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        
        <!-- PASO 1: Email -->
        <div id="step1" class="step-content">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Correo Electrónico
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                        </svg>
                    </div>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        autocomplete="email"
                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150"
                        placeholder="usuario@empresa.com"
                    >
                </div>
            </div>

            <div class="mt-6">
                <button 
                    type="button" 
                    id="btnNext"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition duration-150"
                >
                    Continuar →
                </button>
            </div>
        </div>

        <!-- PASO 2: Password -->
        <div id="step2" class="step-content hidden">
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Contraseña
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        autocomplete="current-password"
                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150"
                        placeholder="••••••••"
                    >
                </div>
            </div>

            <div class="flex items-center justify-between mt-4">
                <div class="flex items-center">
                    <input 
                        id="remember" 
                        name="remember" 
                        type="checkbox" 
                        class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                    >
                    <label for="remember" class="ml-2 block text-sm text-gray-700">
                        Recordarme
                    </label>
                </div>
                <div class="text-sm">
                    <a href="#" class="font-medium text-primary-600 hover:text-primary-500 transition">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
            </div>

            <div class="mt-6 flex space-x-3">
                <button 
                    type="button" 
                    id="btnBack"
                    class="flex-1 py-3 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition duration-150"
                >
                    ← Atrás
                </button>
                <button 
                    type="submit" 
                    class="flex-1 flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition duration-150"
                >
                    <span id="buttonText">Iniciar Sesión</span>
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

    <!-- Registro -->
    <div class="text-center pt-4 border-t border-gray-200">
        <p class="text-sm text-gray-600">
            ¿No tienes cuenta? 
            <a href="/register" class="font-medium text-primary-600 hover:text-primary-500 transition">
                Registrarse
            </a>
        </p>
    </div>
</div>

<script>
const step1 = document.getElementById('step1');
const step2 = document.getElementById('step2');
const btnNext = document.getElementById('btnNext');
const btnBack = document.getElementById('btnBack');
const step1Indicator = document.getElementById('step1-indicator');
const step2Indicator = document.getElementById('step2-indicator');
const step1Label = document.getElementById('step1-label');
const step2Label = document.getElementById('step2-label');
const stepConnector = document.getElementById('step-connector');
const emailInput = document.getElementById('email');

function validateEmail() {
    const email = emailInput.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return email !== '' && emailRegex.test(email);
}

function updateNextButton() {
    btnNext.disabled = !validateEmail();
    if (btnNext.disabled) {
        btnNext.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
        btnNext.classList.remove('opacity-50', 'cursor-not-allowed');
    }
}

emailInput.addEventListener('input', updateNextButton);

btnNext.addEventListener('click', function() {
    if (validateEmail()) {
        step1.classList.add('hidden');
        step2.classList.remove('hidden');
        
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
        
        document.getElementById('password').focus();
    }
});

btnBack.addEventListener('click', function() {
    step2.classList.add('hidden');
    step1.classList.remove('hidden');
    
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
    
    emailInput.focus();
});

document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    const button = this.querySelector('button[type="submit"]');
    const buttonText = document.getElementById('buttonText');
    const buttonSpinner = document.getElementById('buttonSpinner');
    const messageDiv = document.getElementById('message');
    
    button.disabled = true;
    buttonText.textContent = 'Iniciando sesión...';
    buttonSpinner.classList.remove('hidden');
    messageDiv.classList.add('hidden');
    
    fetch('/login', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            messageDiv.className = 'p-4 rounded-lg bg-green-50 border border-green-200';
            messageDiv.innerHTML = '<p class="text-sm text-green-800 font-medium">Login exitoso. Redirigiendo...</p>';
            messageDiv.classList.remove('hidden');
            setTimeout(() => window.location.href = result.redirect, 1000);
        } else {
            messageDiv.className = 'p-4 rounded-lg bg-red-50 border border-red-200';
            messageDiv.innerHTML = '<p class="text-sm text-red-800"><strong>Error:</strong> ' + (result.error || 'Credenciales inválidas') + '</p>';
            messageDiv.classList.remove('hidden');
            
            button.disabled = false;
            buttonText.textContent = 'Iniciar Sesión';
            buttonSpinner.classList.add('hidden');
        }
    })
    .catch(err => {
        messageDiv.className = 'p-4 rounded-lg bg-red-50 border border-red-200';
        messageDiv.innerHTML = '<p class="text-sm text-red-800"><strong>Error:</strong> No se pudo conectar con el servidor</p>';
        messageDiv.classList.remove('hidden');
        
        button.disabled = false;
        buttonText.textContent = 'Iniciar Sesión';
        buttonSpinner.classList.add('hidden');
    });
});

updateNextButton();
</script>
