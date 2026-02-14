<!DOCTYPE html>
<html lang="es" class="h-full bg-gradient-to-br from-blue-50 to-indigo-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $title ?? 'ISO 27001 Platform - Autenticación' ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Tailwind Config personalizado -->
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
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="h-full font-sans antialiased">
    <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl w-full space-y-8">
            <!-- Logo y título -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-full bg-primary-600 shadow-lg">
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h1 class="mt-6 text-3xl font-bold text-gray-900">
                    ISO 27001 Platform
                </h1>
                <p class="mt-2 text-sm text-gray-600">
                    Sistema de Gestión de Cumplimiento
                </p>
            </div>
            
            <!-- Contenido -->
            <div class="bg-white rounded-2xl shadow-xl p-6 lg:p-10">
                <?= $content ?? '' ?>
            </div>
            
            <!-- Footer -->
            <p class="text-center text-xs text-gray-500">
                &copy; <?= date('Y') ?> ISO 27001 Compliance Platform v2.0
            </p>
        </div>
    </div>
</body>
</html>
