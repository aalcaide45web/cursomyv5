<?php declare(strict_types=1);

/**
 * Controlador del Curso - Maneja la vista del curso con secciones y lecciones
 */
class CourseController {
    private CourseRepository $courseRepository;
    private SectionRepository $sectionRepository;
    private LessonRepository $lessonRepository;
    private ProgressRepository $progressRepository;
    
    public function __construct() {
        $this->courseRepository = new CourseRepository();
        $this->sectionRepository = new SectionRepository();
        $this->lessonRepository = new LessonRepository();
        $this->progressRepository = new ProgressRepository();
    }
    
    /**
     * Mostrar vista del curso
     */
    public function show(string $slug): void {
        try {
            // Obtener curso con detalles
            $course = $this->courseRepository->findBySlugWithDetails($slug);
            if (!$course) {
                http_response_code(404);
                echo "Curso no encontrado";
                return;
            }
            
            // Obtener secciones del curso ordenadas
            $sections = $this->sectionRepository->getByCourseOrdered($course['id']);
            
            // Obtener lecciones del curso ordenadas
            $lessons = $this->lessonRepository->getByCourseOrdered($course['id']);
            
            // Obtener progreso del usuario en este curso
            $progress = $this->progressRepository->getByCourse($course['id']);
            
            // Preparar datos para la vista
            $viewData = [
                'course' => $course,
                'sections' => $sections,
                'lessons' => $lessons,
                'progress' => $progress
            ];
            
            // Renderizar vista
            $this->renderCourseView($viewData);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo "Error interno del servidor: " . $e->getMessage();
        }
    }
    
    /**
     * Obtener información de una lección específica
     */
    public function getLesson(int $lessonId): array {
        try {
            $lesson = $this->lessonRepository->findById($lessonId);
            if (!$lesson) {
                return [
                    'success' => false,
                    'error' => 'Lección no encontrada'
                ];
            }
            
            // Obtener progreso de la lección
            $progress = $this->progressRepository->getByLesson($lessonId);
            
            return [
                'success' => true,
                'data' => [
                    'lesson' => $lesson,
                    'progress' => $progress
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener lección: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener secciones de un curso
     */
    public function getSections(int $courseId): array {
        try {
            $sections = $this->sectionRepository->getByCourseOrdered($courseId);
            
            return [
                'success' => true,
                'data' => $sections
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener secciones: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener lecciones de un curso
     */
    public function getLessons(int $courseId): array {
        try {
            $lessons = $this->lessonRepository->getByCourseOrdered($courseId);
            
            return [
                'success' => true,
                'data' => $lessons
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener lecciones: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Renderizar vista del curso
     */
    private function renderCourseView(array $data): void {
        // Extraer variables para la vista
        extract($data);
        
        // Título de la página
        $title = $course['name'] . ' - CursoMy LMS Lite';
        
        // Renderizar vista
        $content = include __DIR__ . '/../Views/pages/course.php';
        include __DIR__ . '/../Views/partials/layout.php';
    }
}
