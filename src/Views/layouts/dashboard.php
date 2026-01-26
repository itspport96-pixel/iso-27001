<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ISO 27001 Compliance Platform</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full">
    
    <div class="h-full flex flex-col">
        <?php include __DIR__ . '/../dashboard/header.php'; ?>
        
        <div class="flex flex-1 overflow-hidden">
            <?php include __DIR__ . '/../dashboard/sidebar.php'; ?>
            
            <main class="flex-1 overflow-y-auto p-6 lg:p-8">
                <?php include __DIR__ . '/../dashboard/sections/resumen.php'; ?>
                
                <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <?php include __DIR__ . '/../dashboard/cards/controles.php'; ?>
                    <?php include __DIR__ . '/../dashboard/cards/requerimientos.php'; ?>
                    <?php include __DIR__ . '/../dashboard/cards/gaps.php'; ?>
                    <?php include __DIR__ . '/../dashboard/cards/evidencias.php'; ?>
                </div>
            </main>
        </div>
    </div>
    
</body>
</html>
