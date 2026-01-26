<?php
/**
 * Card Component
 * 
 * Uso:
 * include __DIR__ . '/components/card.php';
 * echo renderCard('Título', 'Contenido aquí');
 * echo renderCardWithHeader('Título', 'Contenido', '/link', 'Ver más');
 * 
 * @param string $title - Título de la card
 * @param string $content - Contenido HTML
 * @param string $footer - Footer opcional
 * @return string HTML de la card
 */

function renderCard(string $content, string $classes = ''): string
{
    return sprintf(
        '<div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 %s">
            <div class="p-6">%s</div>
        </div>',
        $classes,
        $content
    );
}

function renderCardWithHeader(string $title, string $content, string $actionUrl = '', string $actionText = '', string $icon = ''): string
{
    $iconHtml = '';
    if ($icon) {
        $iconHtml = sprintf(
            '<div class="flex-shrink-0">
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                    %s
                </div>
            </div>',
            $icon
        );
    }
    
    $actionHtml = '';
    if ($actionUrl && $actionText) {
        $actionHtml = sprintf(
            '<a href="%s" class="text-sm font-medium text-primary-600 hover:text-primary-700">%s →</a>',
            htmlspecialchars($actionUrl),
            htmlspecialchars($actionText)
        );
    }
    
    return sprintf(
        '<div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-5 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        %s
                        <h3 class="%s text-lg font-semibold text-gray-900">%s</h3>
                    </div>
                    %s
                </div>
            </div>
            <div class="px-6 py-5">%s</div>
        </div>',
        $iconHtml,
        $iconHtml ? 'ml-3' : '',
        htmlspecialchars($title),
        $actionHtml,
        $content
    );
}

function renderCardWithFooter(string $title, string $content, string $footer, string $icon = ''): string
{
    $iconHtml = '';
    if ($icon) {
        $iconHtml = sprintf(
            '<div class="flex-shrink-0">
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                    %s
                </div>
            </div>',
            $icon
        );
    }
    
    return sprintf(
        '<div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-5 border-b border-gray-200">
                <div class="flex items-center">
                    %s
                    <h3 class="%s text-lg font-semibold text-gray-900">%s</h3>
                </div>
            </div>
            <div class="px-6 py-5">%s</div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">%s</div>
        </div>',
        $iconHtml,
        $iconHtml ? 'ml-3' : '',
        htmlspecialchars($title),
        $content,
        $footer
    );
}

function renderStatCard(string $title, string $value, string $icon, string $variant = 'primary', string $change = '', string $changeType = ''): string
{
    $variants = [
        'primary' => 'bg-primary-100 text-primary-600',
        'success' => 'bg-green-100 text-green-600',
        'error' => 'bg-red-100 text-red-600',
        'warning' => 'bg-yellow-100 text-yellow-600',
        'info' => 'bg-blue-100 text-blue-600',
        'purple' => 'bg-purple-100 text-purple-600',
    ];
    
    $variantClass = $variants[$variant] ?? $variants['primary'];
    
    $changeHtml = '';
    if ($change) {
        $changeColor = $changeType === 'increase' ? 'text-green-600' : 'text-red-600';
        $changeIcon = $changeType === 'increase' ? '↑' : '↓';
        $changeHtml = sprintf(
            '<div class="mt-2 flex items-center text-sm %s">
                <span class="font-medium">%s %s</span>
            </div>',
            $changeColor,
            $changeIcon,
            htmlspecialchars($change)
        );
    }
    
    return sprintf(
        '<div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-lg %s flex items-center justify-center">
                        %s
                    </div>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-600">%s</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">%s</p>
                    %s
                </div>
            </div>
        </div>',
        $variantClass,
        $icon,
        htmlspecialchars($title),
        htmlspecialchars($value),
        $changeHtml
    );
}

function renderEmptyStateCard(string $title, string $message, string $actionUrl = '', string $actionText = '', string $icon = ''): string
{
    $defaultIcon = '<svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
    </svg>';
    
    $iconToUse = $icon ?: $defaultIcon;
    
    $actionHtml = '';
    if ($actionUrl && $actionText) {
        $actionHtml = sprintf(
            '<div class="mt-6">
                <a href="%s" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 transition">
                    %s
                </a>
            </div>',
            htmlspecialchars($actionUrl),
            htmlspecialchars($actionText)
        );
    }
    
    return sprintf(
        '<div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-12 text-center">
                <div class="flex justify-center">%s</div>
                <h3 class="mt-4 text-lg font-medium text-gray-900">%s</h3>
                <p class="mt-2 text-sm text-gray-500">%s</p>
                %s
            </div>
        </div>',
        $iconToUse,
        htmlspecialchars($title),
        htmlspecialchars($message),
        $actionHtml
    );
}

function renderListCard(string $title, array $items, string $actionUrl = '', string $actionText = ''): string
{
    $itemsHtml = '';
    foreach ($items as $item) {
        $itemsHtml .= sprintf(
            '<li class="py-3 flex items-center justify-between">
                <div class="flex items-center">
                    %s
                    <span class="ml-3 text-sm text-gray-900">%s</span>
                </div>
                %s
            </li>',
            $item['icon'] ?? '',
            htmlspecialchars($item['text']),
            $item['badge'] ?? ''
        );
    }
    
    $actionHtml = '';
    if ($actionUrl && $actionText) {
        $actionHtml = sprintf(
            '<a href="%s" class="text-sm font-medium text-primary-600 hover:text-primary-700">%s →</a>',
            htmlspecialchars($actionUrl),
            htmlspecialchars($actionText)
        );
    }
    
    return sprintf(
        '<div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-5 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">%s</h3>
                    %s
                </div>
            </div>
            <div class="px-6">
                <ul class="divide-y divide-gray-200">%s</ul>
            </div>
        </div>',
        htmlspecialchars($title),
        $actionHtml,
        $itemsHtml
    );
}
