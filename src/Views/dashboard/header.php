<header class="bg-white shadow-sm border-b border-gray-200">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between items-center">
            <!-- Logo y título -->
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center">
                    <div class="h-10 w-10 rounded-lg bg-primary-600 flex items-center justify-center">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h1 class="text-lg font-semibold text-gray-900">ISO 27001 Platform</h1>
                        <p class="text-xs text-gray-500">Sistema de Gestión de Cumplimiento</p>
                    </div>
                </div>
            </div>

            <!-- User menu -->
            <div class="flex items-center space-x-4">
                <!-- Notificaciones (placeholder) -->
                <button class="p-2 text-gray-400 hover:text-gray-500 relative">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </button>

                <!-- User dropdown -->
                <div class="relative">
                    <div class="flex items-center space-x-3">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['nombre']) ?></p>
                            <p class="text-xs text-gray-500"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $user['rol']))) ?></p>
                        </div>
                        <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                            <span class="text-sm font-medium text-primary-700">
                                <?= strtoupper(substr($user['nombre'], 0, 2)) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Logout -->
                <a href="/logout" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 transition">
                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Salir
                </a>
            </div>
        </div>
    </div>
</header>
