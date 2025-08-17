<?php declare(strict_types=1);

/**
 * Repositorio para la tabla ratings
 */
class RatingRepository extends BaseRepository {
    protected string $table = 'ratings';
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Obtener valoración de un usuario para un curso
     */
    public function getByUserAndCourse(int $userId, int $courseId): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? AND course_id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $courseId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
    
    /**
     * Obtener todas las valoraciones de un curso
     */
    public function getByCourse(int $courseId): array {
        $sql = "SELECT * FROM {$this->table} WHERE course_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courseId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener valoraciones recientes
     */
    public function getRecent(int $limit = 10): array {
        $sql = "
            SELECT r.*, c.name as course_name, c.slug as course_slug
            FROM {$this->table} r
            JOIN course c ON r.course_id = c.id
            WHERE c.is_deleted = 0
            ORDER BY r.created_at DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear o actualizar valoración
     */
    public function upsertRating(int $userId, int $courseId, int $rating): bool {
        try {
            // Verificar si ya existe una valoración
            $existingRating = $this->getByUserAndCourse($userId, $courseId);
            
            if ($existingRating) {
                // Actualizar valoración existente
                $sql = "
                    UPDATE {$this->table} 
                    SET rating = ?, updated_at = ? 
                    WHERE id = ?
                ";
                $stmt = $this->db->prepare($sql);
                $success = $stmt->execute([
                    $rating,
                    date('Y-m-d H:i:s'),
                    $existingRating['id']
                ]);
            } else {
                // Crear nueva valoración
                $sql = "
                    INSERT INTO {$this->table} (user_id, course_id, rating, created_at) 
                    VALUES (?, ?, ?, ?)
                ";
                $stmt = $this->db->prepare($sql);
                $success = $stmt->execute([
                    $userId,
                    $courseId,
                    $rating,
                    date('Y-m-d H:i:s')
                ]);
            }
            
            if ($success) {
                // Actualizar estadísticas del curso
                $this->updateCourseRatingStats($courseId);
            }
            
            return $success;
        } catch (Exception $e) {
            error_log("Error en upsertRating: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar valoración
     */
    public function deleteRating(int $userId, int $courseId): bool {
        try {
            $sql = "DELETE FROM {$this->table} WHERE user_id = ? AND course_id = ?";
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([$userId, $courseId]);
            
            if ($success) {
                // Actualizar estadísticas del curso
                $this->updateCourseRatingStats($courseId);
            }
            
            return $success;
        } catch (Exception $e) {
            error_log("Error en deleteRating: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar estadísticas de rating del curso
     */
    private function updateCourseRatingStats(int $courseId): bool {
        try {
            // Calcular promedio y conteo
            $sql = "
                SELECT 
                    AVG(rating) as avg_rating,
                    COUNT(*) as ratings_count
                FROM {$this->table} 
                WHERE course_id = ?
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$courseId]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Actualizar curso con nuevas estadísticas
            $updateSql = "
                UPDATE course 
                SET avg_rating = ?, ratings_count = ?, updated_at = ?
                WHERE id = ?
            ";
            $updateStmt = $this->db->prepare($updateSql);
            
            $avgRating = $stats['avg_rating'] ? round($stats['avg_rating'], 2) : 0;
            $ratingsCount = $stats['ratings_count'] ?: 0;
            
            return $updateStmt->execute([
                $avgRating,
                $ratingsCount,
                date('Y-m-d H:i:s'),
                $courseId
            ]);
        } catch (Exception $e) {
            error_log("Error en updateCourseRatingStats: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener estadísticas de valoraciones
     */
    public function getStats(): array {
        $sql = "
            SELECT 
                COUNT(*) as total_ratings,
                COUNT(DISTINCT course_id) as courses_rated,
                COUNT(DISTINCT user_id) as users_rating,
                AVG(rating) as global_avg_rating,
                COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star_count,
                COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star_count,
                COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star_count,
                COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star_count,
                COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star_count
            FROM {$this->table}
        ";
        
        return $this->queryOne($sql) ?: [];
    }
    
    /**
     * Obtener distribución de valoraciones por curso
     */
    public function getRatingDistribution(int $courseId): array {
        $sql = "
            SELECT 
                rating,
                COUNT(*) as count
            FROM {$this->table} 
            WHERE course_id = ?
            GROUP BY rating
            ORDER BY rating DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courseId]);
        
        $distribution = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $distribution[$row['rating']] = (int) $row['count'];
        }
        
        // Asegurar que todas las valoraciones estén representadas
        for ($i = 1; $i <= 5; $i++) {
            if (!isset($distribution[$i])) {
                $distribution[$i] = 0;
            }
        }
        
        krsort($distribution); // Ordenar de mayor a menor
        return $distribution;
    }
    
    /**
     * Buscar valoraciones por texto
     */
    public function searchByText(string $query): array {
        $sql = "
            SELECT r.*, c.name as course_name, c.slug as course_slug
            FROM {$this->table} r
            JOIN course c ON r.course_id = c.id
            WHERE c.is_deleted = 0 
            AND (c.name LIKE ? OR c.description LIKE ?)
            ORDER BY r.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $searchTerm = '%' . $query . '%';
        $stmt->execute([$searchTerm, $searchTerm]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
