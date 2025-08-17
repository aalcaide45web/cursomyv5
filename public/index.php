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
require_once __DIR__ . '/../app/Repositories/ProgressRepository.php';
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

// Cargar clases del sistema de escaneo
require_once __DIR__ . '/../app/Services/Scanner/FilesystemScanner.php';
require_once __DIR__ . '/../app/Services/Scanner/Hasher.php';
require_once __DIR__ . '/../app/Services/Media/MediaProbe.php';
require_once __DIR__ . '/../app/Services/Scanner/Importer.php';
require_once __DIR__ . '/../app/Controllers/ScanController.php';

// Ruta para API de escaneo incremental
$router->post('/api/scan/incremental', function() {
    $controller = new ScanController();
    $controller->incremental();
});

// Ruta para API de escaneo rebuild
$router->post('/api/scan/rebuild', function() {
    $controller = new ScanController();
    $controller->rebuild();
});

// Ruta para API de información del sistema de escaneo
$router->get('/api/scan/system-info', function() {
    $controller = new ScanController();
    $controller->systemInfo();
});

// Ruta para API de estadísticas de escaneo
$router->get('/api/scan/stats', function() {
    $controller = new ScanController();
    $controller->scanStats();
});

// Ruta para API de estadísticas de hash
$router->get('/api/scan/hash-stats', function() {
    $controller = new ScanController();
    $controller->hashStats();
});

// Ruta para API de información de ffmpeg
$router->get('/api/scan/ffmpeg-info', function() {
    $controller = new ScanController();
    $controller->ffmpegInfo();
});

// Ruta para API de limpieza de hashes
$router->post('/api/scan/clean-hashes', function() {
    $controller = new ScanController();
    $controller->cleanHashes();
});

// Ruta para API de escaneo solo (diagnóstico)
$router->get('/api/scan/scan-only', function() {
    $controller = new ScanController();
    $controller->scanOnly();
});

// Ruta para API de archivos por topic
$router->get('/api/scan/files-by-topic/{topic}', function($topic) {
    $controller = new ScanController();
    $controller->filesByTopic($topic);
});

// Ruta para API de archivos por instructor
$router->get('/api/scan/files-by-instructor/{instructor}', function($instructor) {
    $controller = new ScanController();
    $controller->filesByInstructor($instructor);
});

// Ruta para API de archivos por curso
$router->get('/api/scan/files-by-course/{course}', function($course) {
    $controller = new ScanController();
    $controller->filesByCourse($course);
});

// Ruta para API de archivos modificados
$router->get('/api/scan/modified-files/{timestamp}', function($timestamp) {
    $controller = new ScanController();
    $controller->modifiedFiles((int) $timestamp);
});

// Ruta para API de archivos por tamaño
$router->get('/api/scan/files-by-size/{minSize}', function($minSize) {
    $controller = new ScanController();
    $controller->filesBySize((int) $minSize);
});

// Ruta para API de búsqueda de duplicados
$router->get('/api/scan/find-duplicates', function() {
    $controller = new ScanController();
    $controller->findDuplicates();
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

// Cargar controlador del dashboard
require_once __DIR__ . '/../app/Controllers/DashboardController.php';

// Ruta para API de renombrar curso
$router->patch('/api/courses/{slug}/rename', function($slug) {
    $controller = new DashboardController();
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['new_name'])) {
        JsonResponse::validationError(['new_name' => 'El nombre del curso es requerido']);
        return;
    }
    
    $result = $controller->renameCourse($slug, $data['new_name']);
    
    if ($result['success']) {
        JsonResponse::ok($result['data'], $result['message']);
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

// Ruta para API de eliminar curso
$router->delete('/api/courses/{slug}', function($slug) {
    $controller = new DashboardController();
    $result = $controller->deleteCourse($slug);
    
    if ($result['success']) {
        JsonResponse::ok(null, $result['message']);
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

// Ruta para API de reactivar curso
$router->patch('/api/courses/{slug}/reactivate', function($slug) {
    $controller = new DashboardController();
    $result = $controller->reactivateCourse($slug);
    
    if ($result['success']) {
        JsonResponse::ok(null, $result['message']);
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

// Ruta para API de progreso del curso
$router->get('/api/courses/{slug}/progress', function($slug) {
    $controller = new DashboardController();
    $result = $controller->getCourseProgress($slug);
    
    if ($result['success']) {
        JsonResponse::ok($result['data'], 'Progreso obtenido correctamente');
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

// Cargar controlador del curso
require_once __DIR__ . '/../app/Controllers/CourseController.php';

// Ruta para vista del curso
$router->get('/course/{slug}', function($slug) {
    $controller = new CourseController();
    $controller->show($slug);
});

// Ruta para API de secciones del curso
$router->get('/api/courses/{slug}/sections', function($slug) {
    try {
        $courseRepo = new CourseRepository();
        $course = $courseRepo->findBySlug($slug);
        
        if (!$course) {
            JsonResponse::notFound('Curso no encontrado');
            return;
        }
        
        $controller = new CourseController();
        $result = $controller->getSections($course['id']);
        
        if ($result['success']) {
            JsonResponse::ok($result['data'], 'Secciones obtenidas correctamente');
        } else {
            JsonResponse::badRequest($result['error']);
        }
    } catch (Exception $e) {
        JsonResponse::serverError('Error al obtener secciones: ' . $e->getMessage());
    }
});

// Ruta para API de lecciones del curso
$router->get('/api/courses/{slug}/lessons', function($slug) {
    try {
        $courseRepo = new CourseRepository();
        $course = $courseRepo->findBySlug($slug);
        
        if (!$course) {
            JsonResponse::notFound('Curso no encontrado');
            return;
        }
        
        $controller = new CourseController();
        $result = $controller->getLessons($course['id']);
        
        if ($result['success']) {
            JsonResponse::ok($result['data'], 'Lecciones obtenidas correctamente');
        } else {
            JsonResponse::badRequest($result['error']);
        }
    } catch (Exception $e) {
        JsonResponse::serverError('Error al obtener lecciones: ' . $e->getMessage());
    }
});

// Ruta para API de una lección específica
$router->get('/api/lessons/{id}', function($id) {
    $controller = new CourseController();
    $result = $controller->getLesson((int) $id);
    
    if ($result['success']) {
        JsonResponse::ok($result['data'], 'Lección obtenida correctamente');
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

// Cargar controlador del player
require_once __DIR__ . '/../app/Controllers/PlayerController.php';

// Rutas para notas
$router->get('/api/lessons/{id}/notes', function($id) {
    $controller = new PlayerController();
    $result = $controller->getNotes((int) $id);
    
    if ($result['success']) {
        JsonResponse::ok($result['data'], 'Notas obtenidas correctamente');
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

$router->post('/api/lessons/{id}/notes', function($id) {
    $input = json_decode(file_get_contents('php://input'), true);
    $timestamp = $input['timestamp'] ?? 0;
    $content = $input['content'] ?? '';
    
    $controller = new PlayerController();
    $result = $controller->createNote((int) $id, (int) $timestamp, $content);
    
    if ($result['success']) {
        JsonResponse::ok(null, $result['message']);
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

$router->put('/api/notes/{id}', function($id) {
    $input = json_decode(file_get_contents('php://input'), true);
    $timestamp = $input['timestamp'] ?? 0;
    $content = $input['content'] ?? '';
    
    $controller = new PlayerController();
    $result = $controller->updateNote((int) $id, (int) $timestamp, $content);
    
    if ($result['success']) {
        JsonResponse::ok(null, $result['message']);
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

$router->delete('/api/notes/{id}', function($id) {
    $controller = new PlayerController();
    $result = $controller->deleteNote((int) $id);
    
    if ($result['success']) {
        JsonResponse::ok(null, $result['message']);
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

// Rutas para comentarios
$router->get('/api/lessons/{id}/comments', function($id) {
    $controller = new PlayerController();
    $result = $controller->getComments((int) $id);
    
    if ($result['success']) {
        JsonResponse::ok($result['data'], 'Comentarios obtenidos correctamente');
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

$router->post('/api/lessons/{id}/comments', function($id) {
    $input = json_decode(file_get_contents('php://input'), true);
    $content = $input['content'] ?? '';
    $timestamp = $input['timestamp'] ?? null;
    
    $controller = new PlayerController();
    $result = $controller->createComment((int) $id, $content, $timestamp);
    
    if ($result['success']) {
        JsonResponse::ok(null, $result['message']);
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

$router->put('/api/comments/{id}', function($id) {
    $input = json_decode(file_get_contents('php://input'), true);
    $content = $input['content'] ?? '';
    $timestamp = $input['timestamp'] ?? null;
    
    $controller = new PlayerController();
    $result = $controller->updateComment((int) $id, $content, $timestamp);
    
    if ($result['success']) {
        JsonResponse::ok(null, $result['message']);
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

$router->delete('/api/comments/{id}', function($id) {
    $controller = new PlayerController();
    $result = $controller->deleteComment((int) $id);
    
    if ($result['success']) {
        JsonResponse::ok(null, $result['message']);
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

// Ruta para actualizar progreso
$router->post('/api/lessons/{id}/progress', function($id) {
    $input = json_decode(file_get_contents('php://input'), true);
    $position = $input['position'] ?? 0;
    $duration = $input['duration'] ?? 0;
    
    $controller = new PlayerController();
    $result = $controller->updateProgress((int) $id, (int) $position, (int) $duration);
    
    if ($result['success']) {
        JsonResponse::ok(null, $result['message']);
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

// Rutas para valoraciones
$router->get('/api/courses/{id}/rating', function($id) {
    $controller = new RatingController();
    $result = $controller->getUserRating((int) $id);
    
    if ($result['success']) {
        JsonResponse::ok($result['data'], 'Valoración obtenida correctamente');
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

$router->get('/api/courses/{id}/rating/stats', function($id) {
    $controller = new RatingController();
    $result = $controller->getCourseRatingStats((int) $id);
    
    if ($result['success']) {
        JsonResponse::ok($result['data'], 'Estadísticas obtenidas correctamente');
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

$router->post('/api/courses/{id}/rating', function($id) {
    $input = json_decode(file_get_contents('php://input'), true);
    $rating = $input['rating'] ?? 0;
    
    $controller = new RatingController();
    $result = $controller->rateCourse((int) $id, (int) $rating);
    
    if ($result['success']) {
        JsonResponse::ok($result['data'], $result['message']);
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

$router->delete('/api/courses/{id}/rating', function($id) {
    $controller = new RatingController();
    $result = $controller->removeRating((int) $id);
    
    if ($result['success']) {
        JsonResponse::ok($result['data'], $result['message']);
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

$router->get('/api/ratings/recent', function() {
    $limit = $_GET['limit'] ?? 10;
    
    $controller = new RatingController();
    $result = $controller->getRecentRatings((int) $limit);
    
    if ($result['success']) {
        JsonResponse::ok($result['data'], 'Valoraciones recientes obtenidas correctamente');
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

$router->get('/api/ratings/stats', function() {
    $controller = new RatingController();
    $result = $controller->getGlobalStats();
    
    if ($result['success']) {
        JsonResponse::ok($result['data'], 'Estadísticas globales obtenidas correctamente');
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

$router->get('/api/ratings/search', function() {
    $query = $_GET['q'] ?? '';
    
    $controller = new RatingController();
    $result = $controller->searchRatings($query);
    
    if ($result['success']) {
        JsonResponse::ok($result['data'], 'Búsqueda completada correctamente');
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

// Rutas para búsqueda global
$router->get('/api/search', function() {
    $query = $_GET['q'] ?? '';
    $limit = $_GET['limit'] ?? 50;
    
    $controller = new SearchController();
    $result = $controller->search($query, (int) $limit);
    
    if ($result['success']) {
        JsonResponse::ok($result, 'Búsqueda completada correctamente');
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

$router->get('/api/search/suggestions', function() {
    $query = $_GET['q'] ?? '';
    $limit = $_GET['limit'] ?? 10;
    
    $controller = new SearchController();
    $result = $controller->getSuggestions($query, (int) $limit);
    
    if ($result['success']) {
        JsonResponse::ok($result['data'], 'Sugerencias obtenidas correctamente');
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

$router->get('/api/search/filters', function() {
    $query = $_GET['q'] ?? '';
    $limit = $_GET['limit'] ?? 50;
    $filters = $_GET['filters'] ?? [];
    
    // Parsear filtros JSON si vienen como string
    if (is_string($filters)) {
        $filters = json_decode($filters, true) ?: [];
    }
    
    $controller = new SearchController();
    $result = $controller->searchWithFilters($query, $filters, (int) $limit);
    
    if ($result['success']) {
        JsonResponse::ok($result, 'Búsqueda con filtros completada correctamente');
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

$router->post('/api/search/rebuild-index', function() {
    $controller = new SearchController();
    $result = $controller->rebuildIndex();
    
    if ($result['success']) {
        JsonResponse::ok(null, $result['message']);
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

$router->get('/api/search/stats', function() {
    $controller = new SearchController();
    $result = $controller->getStats();
    
    if ($result['success']) {
        JsonResponse::ok($result['data'], 'Estadísticas de búsqueda obtenidas correctamente');
    } else {
        JsonResponse::badRequest($result['error']);
    }
});

// Ejecutar router
$router->run();
