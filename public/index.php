<?php declare(strict_types=1);

// Cargar configuración
$config = require __DIR__ . '/../config/env.example.php';

// Autoloader simple
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../app/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Crear router
$router = new Router();

// Ruta principal - Dashboard
$router->get('/', function() use ($config) {
    $title = 'Dashboard - CursoMy LMS Lite';
    $content = include __DIR__ . '/../app/Views/pages/dashboard.php';
    
    include __DIR__ . '/../app/Views/partials/layout.php';
});

// Ruta para API de escaneo incremental
$router->post('/api/scan/incremental', function() {
    // TODO: Implementar en FASE 2
    header('Content-Type: application/json');
    echo json_encode(['status' => 'not_implemented', 'message' => 'Fase 2 - Pendiente de implementación']);
});

// Ruta para API de escaneo rebuild
$router->post('/api/scan/rebuild', function() {
    // TODO: Implementar en FASE 2
    header('Content-Type: application/json');
    echo json_encode(['status' => 'not_implemented', 'message' => 'Fase 2 - Pendiente de implementación']);
});

// Ejecutar router
$router->run();
