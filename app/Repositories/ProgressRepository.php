<?php declare(strict_types=1);

/**
 * Repositorio para la tabla progress
 */
class ProgressRepository extends BaseRepository {
    protected string $table = 'progress';
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Obtener progreso por curso
     */
    public function getByCourse(int $courseId): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE course_id = ? ORDER BY updated_at DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courseId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
    
    /**
     * Obtener progreso por lección
     */
    public function getByLesson(int $lessonId): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE lesson_id = ? ORDER BY updated_at DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lessonId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
    
    /**
     * Actualizar o crear progreso
     */
    public function updateProgress(int $courseId, int $lessonId, int $position, int $duration): bool {
        try {
            $this->db->beginTransaction();
            
            // Verificar si ya existe un progreso
            $existing = $this->getByLesson($lessonId);
            
            if ($existing) {
                // Actualizar existente
                $sql = "UPDATE {$this->table} SET 
                        position = ?, 
                        duration = ?, 
                        updated_at = ? 
                        WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $position,
                    $duration,
                    date('Y-m-d H:i:s'),
                    $existing['id']
                ]);
            } else {
                // Crear nuevo
                $sql = "INSERT INTO {$this->table} 
                        (course_id, lesson_id, position, duration, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $courseId,
                    $lessonId,
                    $position,
                    $duration,
                    date('Y-m-d H:i:s'),
                    date('Y-m-d H:i:s')
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Obtener progreso reciente
     */
    public function getRecentProgress(int $limit = 10): array {
        $sql = "SELECT p.*, c.name as course_name, c.slug as course_slug, 
                       l.name as lesson_name, l.file_path
                FROM {$this->table} p
                JOIN course c ON p.course_id = c.id
                JOIN lesson l ON p.lesson_id = l.id
                WHERE c.is_deleted = 0
                ORDER BY p.updated_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener estadísticas de progreso
     */
    public function getProgressStats(): array {
        $sql = "SELECT 
                    COUNT(DISTINCT course_id) as courses_started,
                    COUNT(DISTINCT lesson_id) as lessons_started,
                    SUM(position) as total_watched_seconds,
                    AVG(position) as avg_position
                FROM {$this->table}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Limpiar progreso antiguo (más de 30 días)
     */
    public function cleanOldProgress(): int {
        $sql = "DELETE FROM {$this->table} 
                WHERE updated_at < datetime('now', '-30 days')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->rowCount();
    }
}
