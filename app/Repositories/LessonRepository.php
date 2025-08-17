<?php declare(strict_types=1);

class LessonRepository extends BaseRepository
{
    protected string $table = 'lesson';
    
    /**
     * Obtiene lecciones de una sección ordenadas por order_index
     */
    public function getBySection(int $sectionId): array
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE section_id = ? 
            ORDER BY order_index, name
        ";
        
        return $this->queryAll($sql, [$sectionId]);
    }
    
    /**
     * Obtiene lecciones de un curso con información de sección
     */
    public function getByCourse(int $courseId): array
    {
        $sql = "
            SELECT l.*, s.name as section_name, s.order_index as section_order
            FROM {$this->table} l
            JOIN section s ON l.section_id = s.id
            WHERE s.course_id = ?
            ORDER BY s.order_index, l.order_index, l.name
        ";
        
        return $this->queryAll($sql, [$courseId]);
    }
    
    /**
     * Obtiene una lección por ruta de archivo
     */
    public function findByFilePath(string $filePath): ?array
    {
        return $this->findBy('file_path', $filePath);
    }
    
    /**
     * Actualiza la duración de una lección
     */
    public function updateDuration(int $id, float $durationSeconds): bool
    {
        return $this->update($id, ['duration_seconds' => $durationSeconds]);
    }
    
    /**
     * Actualiza la miniatura de una lección
     */
    public function updateThumbnail(int $id, string $thumbPath): bool
    {
        return $this->update($id, ['thumb_path' => $thumbPath]);
    }
    
    /**
     * Obtiene la siguiente posición disponible para una lección
     */
    public function getNextOrderIndex(int $sectionId): int
    {
        $sql = "SELECT MAX(order_index) + 1 FROM {$this->table} WHERE section_id = ?";
        $result = $this->queryOne($sql, [$sectionId]);
        
        return $result ? (int) $result['MAX(order_index) + 1'] : 0;
    }
    
    /**
     * Obtiene estadísticas de lecciones
     */
    public function getStats(): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_lessons,
                SUM(duration_seconds) as total_duration,
                AVG(duration_seconds) as avg_duration,
                COUNT(CASE WHEN thumb_path IS NOT NULL THEN 1 END) as lessons_with_thumb
            FROM {$this->table}
        ";
        
        return $this->queryOne($sql) ?: [];
    }
    
    /**
     * Obtiene lecciones recientes
     */
    public function getRecent(int $limit = 10): array
    {
        $sql = "
            SELECT l.*, s.name as section_name, c.name as course_name, c.slug as course_slug
            FROM {$this->table} l
            JOIN section s ON l.section_id = s.id
            JOIN course c ON s.course_id = c.id
            WHERE c.is_deleted = 0
            ORDER BY l.id DESC
            LIMIT ?
        ";
        
        return $this->queryAll($sql, [$limit]);
    }
}
