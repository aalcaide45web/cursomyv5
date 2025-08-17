<?php declare(strict_types=1);

/**
 * Controlador de Valoraciones - Maneja el sistema de estrellas 1-5
 */
class RatingController {
    private RatingRepository $ratingRepository;
    private CourseRepository $courseRepository;
    
    public function __construct() {
        $this->ratingRepository = new RatingRepository();
        $this->courseRepository = new CourseRepository();
    }
    
    /**
     * Obtener valoración de un usuario para un curso
     */
    public function getUserRating(int $courseId): array {
        try {
            // Por ahora usamos un ID de usuario fijo (1) - en el futuro se implementará autenticación
            $userId = 1;
            
            $rating = $this->ratingRepository->getByUserAndCourse($userId, $courseId);
            
            return [
                'success' => true,
                'data' => [
                    'user_rating' => $rating ? $rating['rating'] : 0,
                    'has_rated' => $rating !== null
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener valoración: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener estadísticas de valoraciones de un curso
     */
    public function getCourseRatingStats(int $courseId): array {
        try {
            $course = $this->courseRepository->findById($courseId);
            if (!$course) {
                return [
                    'success' => false,
                    'error' => 'Curso no encontrado'
                ];
            }
            
            $distribution = $this->ratingRepository->getRatingDistribution($courseId);
            
            return [
                'success' => true,
                'data' => [
                    'avg_rating' => $course['avg_rating'] ?? 0,
                    'ratings_count' => $course['ratings_count'] ?? 0,
                    'distribution' => $distribution
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Crear o actualizar valoración
     */
    public function rateCourse(int $courseId, int $rating): array {
        try {
            // Validar rating
            if ($rating < 1 || $rating > 5) {
                return [
                    'success' => false,
                    'error' => 'La valoración debe estar entre 1 y 5'
                ];
            }
            
            // Verificar que el curso existe
            $course = $this->courseRepository->findById($courseId);
            if (!$course) {
                return [
                    'success' => false,
                    'error' => 'Curso no encontrado'
                ];
            }
            
            // Por ahora usamos un ID de usuario fijo (1) - en el futuro se implementará autenticación
            $userId = 1;
            
            $success = $this->ratingRepository->upsertRating($userId, $courseId, $rating);
            
            if ($success) {
                // Obtener estadísticas actualizadas
                $stats = $this->getCourseRatingStats($courseId);
                
                return [
                    'success' => true,
                    'message' => 'Valoración guardada exitosamente',
                    'data' => $stats['data']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'No se pudo guardar la valoración'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al guardar valoración: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Eliminar valoración
     */
    public function removeRating(int $courseId): array {
        try {
            // Verificar que el curso existe
            $course = $this->courseRepository->findById($courseId);
            if (!$course) {
                return [
                    'success' => false,
                    'error' => 'Curso no encontrado'
                ];
            }
            
            // Por ahora usamos un ID de usuario fijo (1) - en el futuro se implementará autenticación
            $userId = 1;
            
            $success = $this->ratingRepository->deleteRating($userId, $courseId);
            
            if ($success) {
                // Obtener estadísticas actualizadas
                $stats = $this->getCourseRatingStats($courseId);
                
                return [
                    'success' => true,
                    'message' => 'Valoración eliminada exitosamente',
                    'data' => $stats['data']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'No se pudo eliminar la valoración'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al eliminar valoración: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener valoraciones recientes
     */
    public function getRecentRatings(int $limit = 10): array {
        try {
            $ratings = $this->ratingRepository->getRecent($limit);
            
            return [
                'success' => true,
                'data' => $ratings
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener valoraciones recientes: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener estadísticas globales de valoraciones
     */
    public function getGlobalStats(): array {
        try {
            $stats = $this->ratingRepository->getStats();
            
            return [
                'success' => true,
                'data' => $stats
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener estadísticas globales: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Buscar valoraciones por texto
     */
    public function searchRatings(string $query): array {
        try {
            if (empty(trim($query))) {
                return [
                    'success' => false,
                    'error' => 'El término de búsqueda no puede estar vacío'
                ];
            }
            
            $ratings = $this->ratingRepository->searchByText(trim($query));
            
            return [
                'success' => true,
                'data' => $ratings
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error en la búsqueda: ' . $e->getMessage()
            ];
        }
    }
}
