<?php
/**
 * Modal Component
 * 
 * Uso:
 * include __DIR__ . '/components/modal.php';
 * echo renderModal('modalId', 'Título', 'Contenido aquí', 'Confirmar', 'Cancelar');
 * echo renderConfirmModal('deleteModal', '¿Eliminar?', 'Esta acción no se puede deshacer');
 * 
 * @param string $id - ID único del modal
 * @param string $title - Título del modal
 * @param string $content - Contenido HTML
 * @param string $confirmText - Texto del botón confirmar
 * @param string $cancelText - Texto del botón cancelar
 * @return string HTML del modal
 */

function renderModal(string $id, string $title, string $content, string $confirmText = 'Aceptar', string $cancelText = 'Cancelar', string $size = 'md'): string
{
    $sizes = [
        'sm' => 'max-w-md',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
        'full' => 'max-w-full mx-4',
    ];
    
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    
    return sprintf(
        '<div id="%s" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="%s-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal(\'%s\')"></div>
                
                <!-- Center modal -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle w-full %s">
                    <!-- Header -->
                    <div class="bg-white px-6 py-5 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900" id="%s-title">%s</h3>
                            <button type="button" onclick="closeModal(\'%s\')" class="text-gray-400 hover:text-gray-500">
                                <span class="sr-only">Cerrar</span>
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Body -->
                    <div class="bg-white px-6 py-5">
                        %s
                    </div>
                    
                    <!-- Footer -->
                    <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse">
                        <button type="button" onclick="confirmModal(\'%s\')" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm transition">
                            %s
                        </button>
                        <button type="button" onclick="closeModal(\'%s\')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:w-auto sm:text-sm transition">
                            %s
                        </button>
                    </div>
                </div>
            </div>
        </div>',
        $id,
        $id,
        $id,
        $sizeClass,
        $id,
        htmlspecialchars($title),
        $id,
        $content,
        $id,
        htmlspecialchars($confirmText),
        $id,
        htmlspecialchars($cancelText)
    );
}

function renderConfirmModal(string $id, string $title, string $message, string $variant = 'primary'): string
{
    $variants = [
        'primary' => [
            'icon' => '<svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            'bg' => 'bg-primary-100',
            'button' => 'bg-primary-600 hover:bg-primary-700 focus:ring-primary-500',
        ],
        'danger' => [
            'icon' => '<svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
            'bg' => 'bg-red-100',
            'button' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
        ],
        'warning' => [
            'icon' => '<svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
            'bg' => 'bg-yellow-100',
            'button' => 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500',
        ],
        'success' => [
            'icon' => '<svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            'bg' => 'bg-green-100',
            'button' => 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
        ],
    ];
    
    $config = $variants[$variant] ?? $variants['primary'];
    
    $content = sprintf(
        '<div class="sm:flex sm:items-start">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full %s sm:mx-0 sm:h-10 sm:w-10">
                %s
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <p class="text-sm text-gray-500">%s</p>
            </div>
        </div>',
        $config['bg'],
        $config['icon'],
        htmlspecialchars($message)
    );
    
    return sprintf(
        '<div id="%s" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="%s-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal(\'%s\')"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full %s sm:mx-0 sm:h-10 sm:w-10">
                                %s
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-lg font-medium text-gray-900" id="%s-title">%s</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">%s</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" onclick="confirmModal(\'%s\')" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 %s text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm transition">
                            Confirmar
                        </button>
                        <button type="button" onclick="closeModal(\'%s\')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:w-auto sm:text-sm transition">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>',
        $id,
        $id,
        $id,
        $config['bg'],
        $config['icon'],
        $id,
        htmlspecialchars($title),
        htmlspecialchars($message),
        $id,
        $config['button'],
        $id
    );
}

function renderModalScript(): string
{
    return '<script>
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove("hidden");
        document.body.style.overflow = "hidden";
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add("hidden");
        document.body.style.overflow = "auto";
    }
}

function confirmModal(modalId) {
    const event = new CustomEvent("modal:confirm", { detail: { modalId } });
    window.dispatchEvent(event);
    closeModal(modalId);
}

document.addEventListener("keydown", function(e) {
    if (e.key === "Escape") {
        const openModals = document.querySelectorAll("[id][role=\'dialog\']:not(.hidden)");
        openModals.forEach(modal => closeModal(modal.id));
    }
});
</script>';
}
