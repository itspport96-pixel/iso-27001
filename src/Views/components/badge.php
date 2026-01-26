<?php
/**
 * Badge Component
 * 
 * Uso:
 * include __DIR__ . '/components/badge.php';
 * echo renderBadge('Implementado', 'success');
 * echo renderBadge('Pendiente', 'warning', 'lg');
 * 
 * @param string $text - Texto del badge
 * @param string $variant - success, error, warning, info, gray, primary
 * @param string $size - sm, md, lg
 * @return string HTML del badge
 */

function renderBadge(string $text, string $variant = 'gray', string $size = 'md'): string
{
    $variants = [
        'success' => 'bg-green-100 text-green-800 border-green-200',
        'error' => 'bg-red-100 text-red-800 border-red-200',
        'warning' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'info' => 'bg-blue-100 text-blue-800 border-blue-200',
        'gray' => 'bg-gray-100 text-gray-800 border-gray-200',
        'primary' => 'bg-primary-100 text-primary-800 border-primary-200',
        'purple' => 'bg-purple-100 text-purple-800 border-purple-200',
        'indigo' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
    ];
    
    $sizes = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-0.5 text-sm',
        'lg' => 'px-3 py-1 text-base',
    ];
    
    $variantClass = $variants[$variant] ?? $variants['gray'];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    
    return sprintf(
        '<span class="inline-flex items-center rounded-full font-medium border %s %s">%s</span>',
        $variantClass,
        $sizeClass,
        htmlspecialchars($text)
    );
}

function renderBadgeWithDot(string $text, string $variant = 'gray', string $size = 'md'): string
{
    $dotColors = [
        'success' => 'bg-green-500',
        'error' => 'bg-red-500',
        'warning' => 'bg-yellow-500',
        'info' => 'bg-blue-500',
        'gray' => 'bg-gray-500',
        'primary' => 'bg-primary-500',
        'purple' => 'bg-purple-500',
        'indigo' => 'bg-indigo-500',
    ];
    
    $variants = [
        'success' => 'bg-green-50 text-green-800 border-green-200',
        'error' => 'bg-red-50 text-red-800 border-red-200',
        'warning' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
        'info' => 'bg-blue-50 text-blue-800 border-blue-200',
        'gray' => 'bg-gray-50 text-gray-800 border-gray-200',
        'primary' => 'bg-primary-50 text-primary-800 border-primary-200',
        'purple' => 'bg-purple-50 text-purple-800 border-purple-200',
        'indigo' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
    ];
    
    $sizes = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-0.5 text-sm',
        'lg' => 'px-3 py-1 text-base',
    ];
    
    $dotColor = $dotColors[$variant] ?? $dotColors['gray'];
    $variantClass = $variants[$variant] ?? $variants['gray'];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    
    return sprintf(
        '<span class="inline-flex items-center rounded-full font-medium border %s %s"><span class="w-1.5 h-1.5 rounded-full mr-1.5 %s"></span>%s</span>',
        $variantClass,
        $sizeClass,
        $dotColor,
        htmlspecialchars($text)
    );
}

function getEstadoImplementacionBadge(string $estado): string
{
    $map = [
        'implementado' => ['text' => 'Implementado', 'variant' => 'success'],
        'parcialmente_implementado' => ['text' => 'Parcial', 'variant' => 'warning'],
        'no_implementado' => ['text' => 'No Implementado', 'variant' => 'error'],
        'no_aplicable' => ['text' => 'No Aplicable', 'variant' => 'gray'],
    ];
    
    $config = $map[$estado] ?? ['text' => ucfirst(str_replace('_', ' ', $estado)), 'variant' => 'gray'];
    return renderBadge($config['text'], $config['variant']);
}

function getPrioridadBadge(string $prioridad): string
{
    $map = [
        'alta' => ['text' => 'Alta', 'variant' => 'error'],
        'media' => ['text' => 'Media', 'variant' => 'warning'],
        'baja' => ['text' => 'Baja', 'variant' => 'success'],
    ];
    
    $config = $map[$prioridad] ?? ['text' => ucfirst($prioridad), 'variant' => 'gray'];
    return renderBadgeWithDot($config['text'], $config['variant']);
}

function getEstadoRequerimientoBadge(string $estado): string
{
    $map = [
        'completado' => ['text' => 'Completado', 'variant' => 'success'],
        'en_proceso' => ['text' => 'En Proceso', 'variant' => 'info'],
        'pendiente' => ['text' => 'Pendiente', 'variant' => 'gray'],
    ];
    
    $config = $map[$estado] ?? ['text' => ucfirst(str_replace('_', ' ', $estado)), 'variant' => 'gray'];
    return renderBadge($config['text'], $config['variant']);
}

function getEstadoEvidenciaBadge(string $estado): string
{
    $map = [
        'aprobada' => ['text' => 'Aprobada', 'variant' => 'success'],
        'pendiente' => ['text' => 'Pendiente', 'variant' => 'warning'],
        'rechazada' => ['text' => 'Rechazada', 'variant' => 'error'],
    ];
    
    $config = $map[$estado] ?? ['text' => ucfirst($estado), 'variant' => 'gray'];
    return renderBadge($config['text'], $config['variant']);
}
