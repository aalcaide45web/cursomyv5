<?php declare(strict_types=1);

/**
 * Controlador del Dashboard - Maneja acciones de cursos
 */
class DashboardController {
    private CourseRepository $courseRepository;
    private ProgressRepository $progressRepository;
    
    public function __construct() {
        $this->courseRepository = new CourseRepository();
        $this->progressRepository = new ProgressRepository();
    }
    
    /**
     * Obtener estadísticas del dashboard
     */
    public function getStats(): array {
        try {
            $stats = $this->courseRepository->getStats();
            return [
                'success' => true,
                'data' => $stats
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener lista de cursos activos
     */
    public function getCourses(): array {
        try {
            $courses = $this->courseRepository->getAllActive();
            return [
                'success' => true,
                'data' => $courses
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener cursos: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener curso por slug
     */
    public function getCourse(string $slug): array {
        try {
            $course = $this->courseRepository->findBySlugWithDetails($slug);
            if (!$course) {
                return [
                    'success' => false,
                    'error' => 'Curso no encontrado'
                ];
            }
            
            return [
                'success' => true,
                'data' => $course
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener curso: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Renombrar curso
     */
    public function renameCourse(string $slug, string $newName): array {
        try {
            // Validar nombre
            if (empty(trim($newName))) {
                return [
                    'success' => false,
                    'error' => 'El nombre del curso no puede estar vacío'
                ];
            }
            
            $course = $this->courseRepository->findBySlug($slug);
            if (!$course) {
                return [
                    'success' => false,
                    'error' => 'Curso no encontrado'
                ];
            }
            
            // Verificar si ya existe un curso con ese nombre
            $existingCourse = $this->courseRepository->findByName($newName);
            if ($existingCourse && $existingCourse['id'] !== $course['id']) {
                return [
                    'success' => false,
                    'error' => 'Ya existe un curso con ese nombre'
                ];
            }
            
            $success = $this->courseRepository->rename($slug, $newName);
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Curso renombrado exitosamente',
                    'data' => ['new_name' => $newName]
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'No se pudo renombrar el curso'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al renombrar curso: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Eliminar curso (soft delete)
     */
    public function deleteCourse(string $slug): array {
        try {
            $course = $this->courseRepository->findBySlug($slug);
            if (!$course) {
                return [
                    'success' => false,
                    'error' => 'Curso no encontrado'
                ];
            }
            
            $success = $this->courseRepository->softDelete($slug);
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Curso eliminado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'No se pudo eliminar el curso'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al eliminar curso: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Reactivar curso
     */
    public function reactivateCourse(string $slug): array {
        try {
            $course = $this->courseRepository->findBySlug($slug);
            if (!$course) {
                return [
                    'success' => false,
                    'error' => 'Curso no encontrado'
                ];
            }
            
            $success = $this->courseRepository->reactivate($slug);
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Curso reactivado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'No se pudo reactivar el curso'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al reactivar curso: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener progreso del curso
     */
    public function getCourseProgress(string $slug): array {
        try {
            $course = $this->courseRepository->findBySlug($slug);
            if (!$course) {
                return [
                    'success' => false,
                    'error' => 'Curso no encontrado'
                ];
            }
            
            $progress = $this->progressRepository->getByCourse($course['id']);
            return [
                'success' => true,
                'data' => $progress
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener progreso: ' . $e->getMessage()
            ];
        }
    }
}
