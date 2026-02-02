<?php
/**
 * Sparkline - Mini gráfico SVG inline (sin dependencias externas)
 * 
 * @param array $values - Array de valores numéricos
 * @param string $color - Color del gráfico (hex)
 * @param string $type - 'line' o 'bar'
 */
function renderSparkline(array $values, string $color = '#0ea5e9', string $type = 'line'): string
{
    if (empty($values)) {
        return '<svg width="100" height="30"></svg>';
    }
    
    $width = 100;
    $height = 30;
    $padding = 2;
    
    $max = max($values);
    $min = min($values);
    $range = $max - $min;
    
    if ($range == 0) $range = 1;
    
    $points = [];
    $count = count($values);
    $step = ($width - $padding * 2) / ($count - 1);
    
    foreach ($values as $i => $value) {
        $x = $padding + ($i * $step);
        $y = $height - $padding - (($value - $min) / $range * ($height - $padding * 2));
        $points[] = "$x,$y";
    }
    
    $path = 'M ' . implode(' L ', $points);
    
    ob_start();
    ?>
    <svg width="<?= $width ?>" height="<?= $height ?>" class="inline-block">
        <path d="<?= $path ?>" 
              fill="none" 
              stroke="<?= $color ?>" 
              stroke-width="2" 
              stroke-linecap="round" 
              stroke-linejoin="round"/>
    </svg>
    <?php
    return ob_get_clean();
}

/**
 * Progress Ring - Gráfico circular de progreso SVG
 */
function renderProgressRing(float $percentage, string $color = '#0ea5e9', int $size = 60): string
{
    $strokeWidth = 4;
    $radius = ($size - $strokeWidth) / 2;
    $circumference = 2 * M_PI * $radius;
    $offset = $circumference - ($percentage / 100) * $circumference;
    $center = $size / 2;
    
    ob_start();
    ?>
    <svg width="<?= $size ?>" height="<?= $size ?>" class="transform -rotate-90">
        <!-- Fondo -->
        <circle cx="<?= $center ?>" cy="<?= $center ?>" r="<?= $radius ?>" 
                fill="none" stroke="#e5e7eb" stroke-width="<?= $strokeWidth ?>"/>
        <!-- Progreso -->
        <circle cx="<?= $center ?>" cy="<?= $center ?>" r="<?= $radius ?>" 
                fill="none" stroke="<?= $color ?>" stroke-width="<?= $strokeWidth ?>"
                stroke-dasharray="<?= $circumference ?>" 
                stroke-dashoffset="<?= $offset ?>"
                stroke-linecap="round"/>
    </svg>
    <?php
    return ob_get_clean();
}
