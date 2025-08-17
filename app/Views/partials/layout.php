<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'CursoMy LMS Lite' ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'glass': 'rgba(255, 255, 255, 0.1)',
                        'glass-border': 'rgba(255, 255, 255, 0.2)',
                        'dark-bg': '#0f0f23',
                        'dark-surface': '#1a1a2e',
                        'dark-card': '#16213e'
                    },
                    backdropBlur: {
                        'xs': '2px',
                    }
                }
            }
        }
    </script>
    
    <!-- Estilos personalizados para glassmorphism -->
    <style>
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .glass-dark {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
        }
        
        .text-glow {
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen text-gray-100">
    <!-- Topbar -->
    <?php include __DIR__ . '/topbar.php'; ?>
    
    <!-- Contenido principal -->
    <main class="container mx-auto px-4 py-8">
        <?= $content ?? '' ?>
    </main>
    
    <!-- Scripts -->
    <script type="module" src="/assets/js/main.js"></script>
</body>
</html>
