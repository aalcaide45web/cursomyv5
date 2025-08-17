<?php declare(strict_types=1);

// Cargar configuración
$config = require __DIR__ . '/../config/env.example.php';

// Cargar clases manualmente para asegurar que funcionen
require_once __DIR__ . '/../app/Services/DB.php';
require_once __DIR__ . '/../app/Lib/Str.php';
require_once __DIR__ . '/../app/Lib/Time.php';
require_once __DIR__ . '/../app/Lib/JsonResponse.php';
require_once __DIR__ . '/../app/Lib/Validate.php';
require_once __DIR__ . '/../app/Repositories/BaseRepository.php';
require_once __DIR__ . '/../app/Repositories/TopicRepository.php';
require_once __DIR__ . '/../app/Repositories/InstructorRepository.php';
require_once __DIR__ . '/../app/Repositories/CourseRepository.php';
require_once __DIR__ . '/../app/Repositories/SectionRepository.php';
require_once __DIR__ . '/../app/Repositories/LessonRepository.php';
require_once __DIR__ . '/../app/Repositories/DashboardRepository.php';
require_once __DIR__ . '/../app/Router.php';

// Autoloader simple para futuras clases
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../app/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Configurar ruta de base de datos
DB::setDbPath($config['DB_PATH']);

// Crear router
$router = new Router();

// Ruta principal - Dashboard
$router->get('/', function() use ($config) {
    $title = 'Dashboard - CursoMy LMS Lite';
    
    // Obtener estadísticas del dashboard
    $dashboardRepo = new DashboardRepository();
    $stats = $dashboardRepo->getStats();
    
    $content = include __DIR__ . '/../app/Views/pages/dashboard.php';
    
    include __DIR__ . '/../app/Views/partials/layout.php';
});

// Ruta para API de estadísticas del dashboard
$router->get('/api/dashboard/stats', function() {
    try {
        $dashboardRepo = new DashboardRepository();
        $stats = $dashboardRepo->getStats();
        
        JsonResponse::dashboardStats($stats);
    } catch (Exception $e) {
        JsonResponse::serverError('Error al obtener estadísticas: ' . $e->getMessage());
    }
});

// Ruta para API de escaneo incremental
$router->post('/api/scan/incremental', function() {
    // TODO: Implementar en FASE 2
    JsonResponse::error('Funcionalidad pendiente de implementación en FASE 2', 501);
});

// Ruta para API de escaneo rebuild
$router->post('/api/scan/rebuild', function() {
    // TODO: Implementar en FASE 2
    JsonResponse::error('Funcionalidad pendiente de implementación en FASE 2', 501);
});

// Ruta para API de cursos
$router->get('/api/courses', function() {
    try {
        $courseRepo = new CourseRepository();
        $courses = $courseRepo->getAllActive();
        
        JsonResponse::ok($courses, 'Cursos obtenidos correctamente');
    } catch (Exception $e) {
        JsonResponse::serverError('Error al obtener cursos: ' . $e->getMessage());
    }
});

// Ruta para API de un curso específico
$router->get('/api/courses/{slug}', function($slug) {
    try {
        $courseRepo = new CourseRepository();
        $course = $courseRepo->findBySlugWithDetails($slug);
        
        if (!$course) {
            JsonResponse::notFound('Curso no encontrado');
        }
        
        JsonResponse::ok($course, 'Curso obtenido correctamente');
    } catch (Exception $e) {
        JsonResponse::serverError('Error al obtener curso: ' . $e->getMessage());
    }
});

// Ruta para API de topics
$router->get('/api/topics', function() {
    try {
        $topicRepo = new TopicRepository();
        $topics = $topicRepo->getAllWithCourseCount();
        
        JsonResponse::ok($topics, 'Topics obtenidos correctamente');
    } catch (Exception $e) {
        JsonResponse::serverError('Error al obtener topics: ' . $e->getMessage());
    }
});

// Ruta para API de instructores
$router->get('/api/instructors', function() {
    try {
        $instructorRepo = new InstructorRepository();
        $instructors = $instructorRepo->getAllWithCourseCount();
        
        JsonResponse::ok($instructors, 'Instructores obtenidos correctamente');
    } catch (Exception $e) {
        JsonResponse::serverError('Error al obtener instructores: ' . $e->getMessage());
    }
});

// Ejecutar router
$router->run();
