<?php
/**
 * Progress Bar Component
 * 
 * Uso:
 * include __DIR__ . '/components/progress-bar.php';
 * echo renderProgressBar(75, 'success');
 * echo renderProgressBar(45, 'warning', 'md', true);
 * 
 * @param float $percentage - Porcentaje de progreso (0-100)
 * @param string $variant - success, error, warning, info, primary
 * @param string $size - sm, md, lg
 * @param bool $showLabel - Mostrar el porcentaje
 * @return string HTML del progress bar
 */

function renderProgressBar(float $percentage, string $variant = 'primary', string $size = 'md', bool $showLabel = false): string
{
    $percentage = max(0, min(100, $percentage));
    
    $variants = [
        'success' => 'bg-gradient-to-r from-green-500 to-green-600',
        'error' => 'bg-gradient-to-r from-red-500 to-red-600',
        'warning' => 'bg-gradient-to-r from-yellow-500 to-yellow-600',
        'info' => 'bg-gradient-to-r from-blue-500 to-blue-600',
        'primary' => 'bg-gradient-to-r from-primary-500 to-primary-600',
        'purple' => 'bg-gradient-to-r from-purple-500 to-purple-600',
        'indigo' => 'bg-gradient-to-r from-indigo-500 to-indigo-600',
    ];
    
    $sizes = [
        'sm' => 'h-2',
        'md' => 'h-3',
        'lg' => 'h-4',
    ];
    
    $variantClass = $variants[$variant] ?? $variants['primary'];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    
    $label = '';
    if ($showLabel) {
        $textColor = $variant === 'warning' ? 'text-yellow-600' : 
                     ($variant === 'error' ? 'text-red-600' : 
                     ($variant === 'success' ? 'text-green-600' : 'text-primary-600'));
        
        $label = sprintf(
            '<div class="flex items-center justify-between text-sm mb-2">
                <span class="font-medium text-gray-700">Progreso</span>
                <span class="font-semibold %s">%.1f%%</span>
            </div>',
            $textColor,
            $percentage
        );
    }
    
    return sprintf(
        '<div>
            %s
            <div class="w-full bg-gray-200 rounded-full %s overflow-hidden">
                <div class="%s %s rounded-full transition-all duration-500" style="width: %.1f%%"></div>
            </div>
        </div>',
        $label,
        $sizeClass,
        $variantClass,
        $sizeClass,
        $percentage
    );
}

function renderProgressBarWithSteps(array $steps, int $currentStep): string
{
    $totalSteps = count($steps);
    $percentage = ($currentStep / $totalSteps) * 100;
    
    $stepsHtml = '';
    foreach ($steps as $index => $stepName) {
        $stepNumber = $index + 1;
        $isCompleted = $stepNumber < $currentStep;
        $isCurrent = $stepNumber === $currentStep;
        
        $circleClass = $isCompleted ? 'bg-green-500 text-white' : 
                      ($isCurrent ? 'bg-primary-600 text-white' : 'bg-gray-300 text-gray-600');
        
        $textClass = $isCompleted ? 'text-green-600' : 
                    ($isCurrent ? 'text-gray-900' : 'text-gray-500');
        
        $stepsHtml .= sprintf(
            '<div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full %s flex items-center justify-center font-semibold text-sm mb-2">
                    %s
                </div>
                <span class="text-xs font-medium %s text-center">%s</span>
            </div>',
            $circleClass,
            $isCompleted ? '✓' : $stepNumber,
            $textClass,
            htmlspecialchars($stepName)
        );
        
        if ($stepNumber < $totalSteps) {
            $lineClass = $isCompleted ? 'bg-green-500' : 'bg-gray-300';
            $stepsHtml .= sprintf(
                '<div class="flex-1 flex items-center px-4">
                    <div class="w-full h-0.5 %s"></div>
                </div>',
                $lineClass
            );
        }
    }
    
    return sprintf(
        '<div class="flex items-start justify-between">%s</div>',
        $stepsHtml
    );
}

function renderCircularProgress(float $percentage, string $variant = 'primary', int $size = 120): string
{
    $percentage = max(0, min(100, $percentage));
    $radius = ($size - 10) / 2;
    $circumference = 2 * pi() * $radius;
    $offset = $circumference - ($percentage / 100 * $circumference);
    
    $colors = [
        'success' => '#10b981',
        'error' => '#ef4444',
        'warning' => '#f59e0b',
        'info' => '#3b82f6',
        'primary' => '#0284c7',
        'purple' => '#a855f7',
        'indigo' => '#6366f1',
    ];
    
    $color = $colors[$variant] ?? $colors['primary'];
    
    return sprintf(
        '<div class="inline-flex items-center justify-center" style="width: %dpx; height: %dpx;">
            <svg class="transform -rotate-90" width="%d" height="%d">
                <circle cx="%d" cy="%d" r="%d" stroke="#e5e7eb" stroke-width="8" fill="none"/>
                <circle cx="%d" cy="%d" r="%d" stroke="%s" stroke-width="8" fill="none"
                        stroke-dasharray="%f" stroke-dashoffset="%f"
                        stroke-linecap="round" class="transition-all duration-500"/>
            </svg>
            <div class="absolute text-center">
                <span class="text-2xl font-bold text-gray-900">%.0f%%</span>
            </div>
        </div>',
        $size, $size,
        $size, $size,
        $size / 2, $size / 2, $radius,
        $size / 2, $size / 2, $radius, $color,
        $circumference, $offset,
        $percentage
    );
}

function renderProgressBarSegmented(array $segments): string
{
    $total = array_sum(array_column($segments, 'value'));
    
    $segmentsHtml = '';
    foreach ($segments as $segment) {
        $percentage = $total > 0 ? ($segment['value'] / $total) * 100 : 0;
        $segmentsHtml .= sprintf(
            '<div class="%s h-3 transition-all duration-500" style="width: %.1f%%" title="%s: %d"></div>',
            $segment['color'],
            $percentage,
            htmlspecialchars($segment['label']),
            $segment['value']
        );
    }
    
    $legendHtml = '';
    foreach ($segments as $segment) {
        $legendHtml .= sprintf(
            '<div class="flex items-center">
                <div class="w-3 h-3 rounded-sm %s mr-2"></div>
                <span class="text-sm text-gray-600">%s</span>
                <span class="ml-auto text-sm font-medium text-gray-900">%d</span>
            </div>',
            $segment['color'],
            htmlspecialchars($segment['label']),
            $segment['value']
        );
    }
    
    return sprintf(
        '<div>
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden flex">
                %s
            </div>
            <div class="mt-4 space-y-2">
                %s
            </div>
        </div>',
        $segmentsHtml,
        $legendHtml
    );
}
