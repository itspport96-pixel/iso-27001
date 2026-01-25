<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ISO 27001 Compliance Platform</title>
    <!-- Aquí irán los estilos CSS en futuras fases -->
</head>
<body>
    
    <?php include __DIR__ . '/../dashboard/header.php'; ?>
    
    <div class="container">
        
        <?php include __DIR__ . '/../dashboard/sidebar.php'; ?>
        
        <main class="main-content">
            
            <?php include __DIR__ . '/../dashboard/sections/resumen.php'; ?>
            
            <div class="cards-grid">
                <?php include __DIR__ . '/../dashboard/cards/controles.php'; ?>
                <?php include __DIR__ . '/../dashboard/cards/requerimientos.php'; ?>
                <?php include __DIR__ . '/../dashboard/cards/gaps.php'; ?>
                <?php include __DIR__ . '/../dashboard/cards/evidencias.php'; ?>
            </div>
            
        </main>
        
    </div>
    
</body>
</html>
