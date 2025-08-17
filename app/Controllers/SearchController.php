<?php declare(strict_types=1);

/**
 * Controlador de Búsqueda Global - Maneja búsquedas en todo el sistema
 */
class SearchController {
    private SearchService $searchService;
    
    public function __construct() {
        $this->searchService = new SearchService();
    }
    
    /**
     * Realizar búsqueda global
     */
    public function search(string $query, int $limit = 50): array {
        try {
            if (empty(trim($query))) {
                return [
                    'success' => false,
                    'error' => 'El término de búsqueda no puede estar vacío'
                ];
            }
            
            $results = $this->searchService->search(trim($query), $limit);
            
            if ($results['success']) {
                return [
                    'success' => true,
                    'data' => $results['data'],
                    'method' => $results['method'] ?? 'composite',
                    'query' => $query,
                    'count' => count($results['data'])
                ];
            } else {
                return $results;
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error en la búsqueda: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Reconstruir índice de búsqueda
     */
    public function rebuildIndex(): array {
        try {
            $success = $this->searchService->rebuildSearchIndex();
            
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Índice de búsqueda reconstruido exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'No se pudo reconstruir el índice de búsqueda'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error reconstruyendo índice: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener estadísticas de búsqueda
     */
    public function getStats(): array {
        try {
            $stats = $this->searchService->getSearchStats();
            
            return [
                'success' => true,
                'data' => $stats
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error obteniendo estadísticas: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Buscar con filtros específicos
     */
    public function searchWithFilters(string $query, array $filters = [], int $limit = 50): array {
        try {
            if (empty(trim($query))) {
                return [
                    'success' => false,
                    'error' => 'El término de búsqueda no puede estar vacío'
                ];
            }
            
            // Realizar búsqueda básica
            $results = $this->searchService->search(trim($query), $limit);
            
            if (!$results['success']) {
                return $results;
            }
            
            // Aplicar filtros
            $filteredResults = $this->applyFilters($results['data'], $filters);
            
            return [
                'success' => true,
                'data' => $filteredResults,
                'method' => $results['method'] ?? 'composite',
                'query' => $query,
                'filters' => $filters,
                'count' => count($filteredResults),
                'total_before_filters' => count($results['data'])
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error en búsqueda con filtros: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Aplicar filtros a los resultados
     */
    private function applyFilters(array $results, array $filters): array {
        if (empty($filters)) {
            return $results;
        }
        
        return array_filter($results, function($result) use ($filters) {
            // Filtro por tipo
            if (isset($filters['type']) && !empty($filters['type'])) {
                if (is_array($filters['type'])) {
                    if (!in_array($result['type'], $filters['type'])) {
                        return false;
                    }
                } else {
                    if ($result['type'] !== $filters['type']) {
                        return false;
                    }
                }
            }
            
            // Filtro por instructor
            if (isset($filters['instructor_id']) && !empty($filters['instructor_id'])) {
                if ($result['type'] === 'course' && isset($result['data']['instructor_id'])) {
                    if ($result['data']['instructor_id'] != $filters['instructor_id']) {
                        return false;
                    }
                } else {
                    return false;
                }
            }
            
            // Filtro por topic
            if (isset($filters['topic_id']) && !empty($filters['topic_id'])) {
                if ($result['type'] === 'course' && isset($result['data']['instructor_id'])) {
                    // Verificar topic del instructor
                    $instructor = $this->getInstructorTopic($result['data']['instructor_id']);
                    if (!$instructor || $instructor['topic_id'] != $filters['topic_id']) {
                        return false;
                    }
                } else {
                    return false;
                }
            }
            
            // Filtro por fecha (últimos X días)
            if (isset($filters['days']) && !empty($filters['days'])) {
                $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$filters['days']} days"));
                if (isset($result['data']['created_at'])) {
                    if ($result['data']['created_at'] < $cutoffDate) {
                        return false;
                    }
                }
            }
            
            return true;
        });
    }
    
    /**
     * Obtener topic de un instructor
     */
    private function getInstructorTopic(int $instructorId): ?array {
        try {
            $sql = "SELECT topic_id FROM instructor WHERE id = ?";
            $stmt = DB::getInstance()->getConnection()->prepare($sql);
            $stmt->execute([$instructorId]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Buscar sugerencias de autocompletado
     */
    public function getSuggestions(string $query, int $limit = 10): array {
        try {
            if (empty(trim($query))) {
                return [
                    'success' => false,
                    'error' => 'Query vacío para sugerencias'
                ];
            }
            
            $searchTerm = trim($query);
            $suggestions = [];
            
            // Buscar en nombres de cursos
            $suggestions = array_merge($suggestions, $this->getCourseSuggestions($searchTerm, $limit));
            
            // Buscar en nombres de lecciones
            $suggestions = array_merge($suggestions, $this->getLessonSuggestions($searchTerm, $limit));
            
            // Buscar en nombres de instructores
            $suggestions = array_merge($suggestions, $this->getInstructorSuggestions($searchTerm, $limit));
            
            // Buscar en nombres de topics
            $suggestions = array_merge($suggestions, $this->getTopicSuggestions($searchTerm, $limit));
            
            // Eliminar duplicados y limitar
            $suggestions = array_unique($suggestions, SORT_REGULAR);
            $suggestions = array_slice($suggestions, 0, $limit);
            
            return [
                'success' => true,
                'data' => $suggestions,
                'query' => $searchTerm
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error obteniendo sugerencias: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener sugerencias de cursos
     */
    private function getCourseSuggestions(string $query, int $limit): array {
        $sql = "
            SELECT DISTINCT name as text, 'course' as type, slug as link
            FROM course
            WHERE name LIKE ? AND is_deleted = 0
            ORDER BY name
            LIMIT ?
        ";
        
        $stmt = DB::getInstance()->getConnection()->prepare($sql);
        $stmt->execute(['%' . $query . '%', $limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener sugerencias de lecciones
     */
    private function getLessonSuggestions(string $query, int $limit): array {
        $sql = "
            SELECT DISTINCT l.name as text, 'lesson' as type, 
                   CONCAT(c.slug, '/lesson/', l.id) as link
            FROM lesson l
            JOIN section s ON l.section_id = s.id
            JOIN course c ON s.course_id = c.id
            WHERE l.name LIKE ? AND l.is_deleted = 0 AND c.is_deleted = 0
            ORDER BY l.name
            LIMIT ?
        ";
        
        $stmt = DB::getInstance()->getConnection()->prepare($sql);
        $stmt->execute(['%' . $query . '%', $limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener sugerencias de instructores
     */
    private function getInstructorSuggestions(string $query, int $limit): array {
        $sql = "
            SELECT DISTINCT name as text, 'instructor' as type, 
                   CONCAT('instructor/', id) as link
            FROM instructor
            WHERE name LIKE ? AND is_deleted = 0
            ORDER BY name
            LIMIT ?
        ";
        
        $stmt = DB::getInstance()->getConnection()->prepare($sql);
        $stmt->execute(['%' . $query . '%', $limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener sugerencias de topics
     */
    private function getTopicSuggestions(string $query, int $limit): array {
        $sql = "
            SELECT DISTINCT name as text, 'topic' as type, 
                   CONCAT('topic/', id) as link
            FROM topic
            WHERE name LIKE ? AND is_deleted = 0
            ORDER BY name
            LIMIT ?
        ";
        
        $stmt = DB::getInstance()->getConnection()->prepare($sql);
        $stmt->execute(['%' . $query . '%', $limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
