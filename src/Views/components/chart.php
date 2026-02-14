<?php
/**
 * Componente de gráfico usando Chart.js
 * 
 * @param string $id - ID único del canvas
 * @param string $type - Tipo: 'pie', 'bar', 'line', 'doughnut'
 * @param array $data - Datos del gráfico
 * @param array $options - Opciones adicionales
 */
function renderChart(string $id, string $type, array $data, array $options = []): string
{
    $height = $options['height'] ?? '300px';
    $title = $options['title'] ?? '';
    
    // Generar colores automáticos si no se proporcionan
    $defaultColors = [
        '#0ea5e9', // primary-500
        '#10b981', // green-500
        '#f59e0b', // amber-500
        '#ef4444', // red-500
        '#8b5cf6', // purple-500
        '#ec4899', // pink-500
        '#06b6d4', // cyan-500
    ];
    
    $chartId = 'chart_' . $id;
    $dataJson = json_encode($data);
    $colorsJson = json_encode($data['colors'] ?? $defaultColors);
    
    ob_start();
    ?>
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <?php if ($title): ?>
        <h3 class="text-lg font-semibold text-gray-900 mb-4"><?= htmlspecialchars($title) ?></h3>
        <?php endif; ?>
        <div style="height: <?= $height ?>">
            <canvas id="<?= $chartId ?>"></canvas>
        </div>
    </div>
    
    <script>
    (function() {
        const ctx = document.getElementById('<?= $chartId ?>').getContext('2d');
        const data = <?= $dataJson ?>;
        const colors = <?= $colorsJson ?>;
        
        new Chart(ctx, {
            type: '<?= $type ?>',
            data: {
                labels: data.labels,
                datasets: [{
                    label: data.label || '',
                    data: data.values,
                    backgroundColor: colors,
                    borderColor: colors.map(c => c),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: '<?= $options['legend'] ?? 'top' ?>',
                    }
                }
            }
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}
