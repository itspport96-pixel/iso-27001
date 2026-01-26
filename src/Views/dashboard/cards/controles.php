<div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="ml-3 text-lg font-semibold text-gray-900">Controles ISO 27001</h3>
            </div>
            <a href="/controles" class="text-sm font-medium text-primary-600 hover:text-primary-700">Ver todos →</a>
        </div>
    </div>
    
    <div class="px-6 py-5 space-y-4">
        
        <!-- Total -->
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-600">Total de controles</span>
            <span class="text-sm font-semibold text-gray-900"><?= $controles['total'] ?></span>
        </div>

        <!-- NO aplicables -->
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-600">NO aplicables</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                <?= $controles['no_aplicables'] ?>
            </span>
        </div>

        <!-- Aplicables -->
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-600">Aplicables</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                <?= $controles['aplicables'] ?>
            </span>
        </div>

        <div class="border-t border-gray-200 pt-4"></div>

        <!-- Implementados -->
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-600">Implementados</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <?= $controles['implementados'] ?>
            </span>
        </div>

        <!-- Parciales -->
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-600">Parciales</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                <?= $controles['parciales'] ?>
            </span>
        </div>

        <!-- No implementados -->
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-600">No implementados</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                <?= $controles['no_implementados'] ?>
            </span>
        </div>

    </div>

    <!-- Progress bar -->
    <div class="px-6 pb-5">
        <div class="mt-2">
            <div class="flex items-center justify-between text-sm mb-2">
                <span class="font-medium text-gray-700">Progreso de implementación</span>
                <span class="font-semibold text-primary-600"><?= $controles['porcentaje'] ?>%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <div class="bg-gradient-to-r from-primary-500 to-primary-600 h-3 rounded-full transition-all duration-500" style="width: <?= $controles['porcentaje'] ?>%"></div>
            </div>
        </div>
    </div>
</div>
