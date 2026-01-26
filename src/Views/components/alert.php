<?php
/**
 * Alert Component
 * 
 * Uso:
 * include __DIR__ . '/components/alert.php';
 * echo renderAlert('Operación exitosa', 'success');
 * echo renderAlert('Error al procesar', 'error', 'Error:', true);
 * 
 * @param string $message - Mensaje del alert
 * @param string $variant - success, error, warning, info
 * @param string $title - Título opcional
 * @param bool $dismissible - Si puede cerrarse
 * @return string HTML del alert
 */

function renderAlert(string $message, string $variant = 'info', string $title = '', bool $dismissible = false): string
{
    $variants = [
        'success' => [
            'bg' => 'bg-green-50',
            'border' => 'border-green-200',
            'text' => 'text-green-800',
            'icon' => '<svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
        ],
        'error' => [
            'bg' => 'bg-red-50',
            'border' => 'border-red-200',
            'text' => 'text-red-800',
            'icon' => '<svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>'
        ],
        'warning' => [
            'bg' => 'bg-yellow-50',
            'border' => 'border-yellow-200',
            'text' => 'text-yellow-800',
            'icon' => '<svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>'
        ],
        'info' => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-200',
            'text' => 'text-blue-800',
            'icon' => '<svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>'
        ],
    ];
    
    $config = $variants[$variant] ?? $variants['info'];
    
    $dismissButton = '';
    if ($dismissible) {
        $dismissButton = sprintf(
            '<button type="button" onclick="this.parentElement.remove()" class="ml-auto -mx-1.5 -my-1.5 rounded-lg p-1.5 inline-flex h-8 w-8 %s hover:bg-opacity-20 hover:bg-gray-900">
                <span class="sr-only">Cerrar</span>
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>',
            $config['text']
        );
    }
    
    $titleHtml = '';
    if ($title) {
        $titleHtml = sprintf(
            '<h3 class="text-sm font-semibold %s mb-1">%s</h3>',
            $config['text'],
            htmlspecialchars($title)
        );
    }
    
    return sprintf(
        '<div class="rounded-lg border p-4 %s %s %s" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">%s</div>
                <div class="ml-3 flex-1">
                    %s
                    <div class="text-sm %s">%s</div>
                </div>
                %s
            </div>
        </div>',
        $config['bg'],
        $config['border'],
        $config['text'],
        $config['icon'],
        $titleHtml,
        $config['text'],
        htmlspecialchars($message),
        $dismissButton
    );
}

function renderAlertWithAction(string $message, string $variant, string $actionText, string $actionUrl): string
{
    $variants = [
        'success' => [
            'bg' => 'bg-green-50',
            'border' => 'border-green-200',
            'text' => 'text-green-800',
            'button' => 'text-green-700 hover:bg-green-100',
            'icon' => '<svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
        ],
        'error' => [
            'bg' => 'bg-red-50',
            'border' => 'border-red-200',
            'text' => 'text-red-800',
            'button' => 'text-red-700 hover:bg-red-100',
            'icon' => '<svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>'
        ],
        'warning' => [
            'bg' => 'bg-yellow-50',
            'border' => 'border-yellow-200',
            'text' => 'text-yellow-800',
            'button' => 'text-yellow-700 hover:bg-yellow-100',
            'icon' => '<svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>'
        ],
        'info' => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-200',
            'text' => 'text-blue-800',
            'button' => 'text-blue-700 hover:bg-blue-100',
            'icon' => '<svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>'
        ],
    ];
    
    $config = $variants[$variant] ?? $variants['info'];
    
    return sprintf(
        '<div class="rounded-lg border p-4 %s %s %s" role="alert">
            <div class="flex items-start">
                <div class="flex-shrink-0">%s</div>
                <div class="ml-3 flex-1">
                    <p class="text-sm %s">%s</p>
                </div>
                <div class="ml-3 flex-shrink-0">
                    <a href="%s" class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md %s transition">
                        %s →
                    </a>
                </div>
            </div>
        </div>',
        $config['bg'],
        $config['border'],
        $config['text'],
        $config['icon'],
        $config['text'],
        htmlspecialchars($message),
        htmlspecialchars($actionUrl),
        $config['button'],
        htmlspecialchars($actionText)
    );
}
