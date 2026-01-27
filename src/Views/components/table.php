<?php
/**
 * Table Component
 * 
 * Uso:
 * include __DIR__ . '/components/table.php';
 * echo renderTable($headers, $rows, ['striped' => true, 'hoverable' => true]);
 * 
 * @param array $headers - Array de headers ['key' => 'Label', ...]
 * @param array $rows - Array de filas de datos
 * @param array $options - Opciones de configuración
 * @return string HTML de la tabla
 */

function renderTable(array $headers, array $rows, array $options = []): string
{
    $defaults = [
        'striped' => false,
        'hoverable' => true,
        'bordered' => false,
        'compact' => false,
        'actions' => [],
        'empty_message' => 'No hay datos disponibles',
    ];
    
    $config = array_merge($defaults, $options);
    
    $tableClasses = 'min-w-full divide-y divide-gray-200';
    $theadClasses = 'bg-gray-50';
    $thClasses = 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider';
    $tbodyClasses = 'bg-white divide-y divide-gray-200';
    $tdClasses = $config['compact'] ? 'px-6 py-3 whitespace-nowrap text-sm' : 'px-6 py-4 whitespace-nowrap text-sm';
    
    if ($config['bordered']) {
        $tableClasses .= ' border border-gray-200';
    }
    
    $headerHtml = '';
    foreach ($headers as $key => $label) {
        $headerHtml .= sprintf('<th scope="col" class="%s">%s</th>', $thClasses, htmlspecialchars($label));
    }
    
    if (!empty($config['actions'])) {
        $headerHtml .= sprintf('<th scope="col" class="%s text-right">Acciones</th>', $thClasses);
    }
    
    $rowsHtml = '';
    if (empty($rows)) {
        $colspan = count($headers) + (!empty($config['actions']) ? 1 : 0);
        $rowsHtml = sprintf(
            '<tr><td colspan="%d" class="px-6 py-8 text-center text-sm text-gray-500">%s</td></tr>',
            $colspan,
            htmlspecialchars($config['empty_message'])
        );
    } else {
        foreach ($rows as $index => $row) {
            $trClasses = '';
            if ($config['striped'] && $index % 2 === 1) {
                $trClasses .= ' bg-gray-50';
            }
            if ($config['hoverable']) {
                $trClasses .= ' hover:bg-gray-100 transition';
            }
            
            $cellsHtml = '';
            foreach (array_keys($headers) as $key) {
                $value = $row[$key] ?? '';
                $cellsHtml .= sprintf('<td class="%s text-gray-900">%s</td>', $tdClasses, $value);
            }
            
            if (!empty($config['actions'])) {
                $actionsHtml = '<div class="flex items-center justify-end space-x-2">';
                foreach ($config['actions'] as $action) {
                    $url = is_callable($action['url']) ? $action['url']($row) : $action['url'];
                    $label = $action['label'] ?? '';
                    $icon = $action['icon'] ?? '';
                    $class = $action['class'] ?? 'text-primary-600 hover:text-primary-900';
                    
                    $actionsHtml .= sprintf(
                        '<a href="%s" class="%s transition" title="%s">%s</a>',
                        htmlspecialchars($url),
                        $class,
                        htmlspecialchars($label),
                        $icon
                    );
                }
                $actionsHtml .= '</div>';
                $cellsHtml .= sprintf('<td class="%s">%s</td>', $tdClasses, $actionsHtml);
            }
            
            $rowsHtml .= sprintf('<tr class="%s">%s</tr>', $trClasses, $cellsHtml);
        }
    }
    
    return sprintf(
        '<div class="overflow-x-auto">
            <table class="%s">
                <thead class="%s">
                    <tr>%s</tr>
                </thead>
                <tbody class="%s">%s</tbody>
            </table>
        </div>',
        $tableClasses,
        $theadClasses,
        $headerHtml,
        $tbodyClasses,
        $rowsHtml
    );
}

function renderTableWithCard(string $title, array $headers, array $rows, array $options = []): string
{
    $actionHtml = '';
    if (isset($options['card_action_url']) && isset($options['card_action_text'])) {
        $actionHtml = sprintf(
            '<a href="%s" class="text-sm font-medium text-primary-600 hover:text-primary-700">%s →</a>',
            htmlspecialchars($options['card_action_url']),
            htmlspecialchars($options['card_action_text'])
        );
    }
    
    $tableHtml = renderTable($headers, $rows, $options);
    
    return sprintf(
        '<div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-5 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">%s</h3>
                    %s
                </div>
            </div>
            <div>%s</div>
        </div>',
        htmlspecialchars($title),
        $actionHtml,
        $tableHtml
    );
}

function renderSortableTable(array $headers, array $rows, string $currentSort = '', string $sortDirection = 'asc'): string
{
    $headerHtml = '';
    foreach ($headers as $key => $label) {
        $sortable = isset($label['sortable']) ? $label['sortable'] : true;
        $labelText = is_array($label) ? $label['label'] : $label;
        
        if ($sortable) {
            $isActive = $currentSort === $key;
            $nextDirection = $isActive && $sortDirection === 'asc' ? 'desc' : 'asc';
            $sortIcon = '';
            
            if ($isActive) {
                $sortIcon = $sortDirection === 'asc' 
                    ? '<svg class="w-4 h-4 ml-1 inline" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>'
                    : '<svg class="w-4 h-4 ml-1 inline" fill="currentColor" viewBox="0 0 20 20"><path d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"/></svg>';
            } else {
                $sortIcon = '<svg class="w-4 h-4 ml-1 inline opacity-0 group-hover:opacity-50" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>';
            }
            
            $headerHtml .= sprintf(
                '<th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="?sort=%s&direction=%s" class="group inline-flex items-center hover:text-gray-700">
                        %s %s
                    </a>
                </th>',
                htmlspecialchars($key),
                $nextDirection,
                htmlspecialchars($labelText),
                $sortIcon
            );
        } else {
            $headerHtml .= sprintf(
                '<th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">%s</th>',
                htmlspecialchars($labelText)
            );
        }
    }
    
    $rowsHtml = '';
    foreach ($rows as $row) {
        $cellsHtml = '';
        foreach (array_keys($headers) as $key) {
            $value = $row[$key] ?? '';
            $cellsHtml .= sprintf('<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">%s</td>', $value);
        }
        $rowsHtml .= sprintf('<tr class="hover:bg-gray-50 transition">%s</tr>', $cellsHtml);
    }
    
    return sprintf(
        '<div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>%s</tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">%s</tbody>
            </table>
        </div>',
        $headerHtml,
        $rowsHtml
    );
}

function renderPagination(int $currentPage, int $totalPages, string $baseUrl): string
{
    if ($totalPages <= 1) {
        return '';
    }
    
    $links = '';
    
    $prevDisabled = $currentPage <= 1 ? 'pointer-events-none opacity-50' : '';
    $links .= sprintf(
        '<a href="%s" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 %s">
            <span class="sr-only">Anterior</span>
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
        </a>',
        htmlspecialchars($baseUrl . '?page=' . max(1, $currentPage - 1)),
        $prevDisabled
    );
    
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $currentPage 
            ? 'z-10 bg-primary-50 border-primary-500 text-primary-600' 
            : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50';
        
        $links .= sprintf(
            '<a href="%s" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium %s">%d</a>',
            htmlspecialchars($baseUrl . '?page=' . $i),
            $active,
            $i
        );
    }
    
    $nextDisabled = $currentPage >= $totalPages ? 'pointer-events-none opacity-50' : '';
    $links .= sprintf(
        '<a href="%s" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 %s">
            <span class="sr-only">Siguiente</span>
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
        </a>',
        htmlspecialchars($baseUrl . '?page=' . min($totalPages, $currentPage + 1)),
        $nextDisabled
    );
    
    return sprintf(
        '<div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
                <a href="%s" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 %s">Anterior</a>
                <a href="%s" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 %s">Siguiente</a>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Página <span class="font-medium">%d</span> de <span class="font-medium">%d</span>
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        %s
                    </nav>
                </div>
            </div>
        </div>',
        htmlspecialchars($baseUrl . '?page=' . max(1, $currentPage - 1)),
        $prevDisabled,
        htmlspecialchars($baseUrl . '?page=' . min($totalPages, $currentPage + 1)),
        $nextDisabled,
        $currentPage,
        $totalPages,
        $links
    );
}
