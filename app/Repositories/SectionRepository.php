<?php declare(strict_types=1);

class SectionRepository extends BaseRepository
{
    protected string $table = 'section';
    
    /**
     * Obtiene secciones de un curso ordenadas por order_index
     */
    public function getByCourse(int $courseId): array
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE course_id = ? 
            ORDER BY order_index, name
        ";
        
        return $this->queryAll($sql, [$courseId]);
    }
    
    /**
     * Obtiene secciones con conteo de lecciones
     */
    public function getByCourseWithLessonCount(int $courseId): array
    {
        $sql = "
            SELECT s.*, COUNT(l.id) as lesson_count
            FROM {$this->table} s
            LEFT JOIN lesson l ON s.id = l.section_id
            WHERE s.course_id = ?
            GROUP BY s.id
            ORDER BY s.order_index, s.name
        ";
        
        return $this->queryAll($sql, [$courseId]);
    }
    
    /**
     * Actualiza el orden de las secciones
     */
    public function updateOrder(int $id, int $orderIndex): bool
    {
        return $this->update($id, ['order_index' => $orderIndex]);
    }
    
    /**
     * Obtiene la siguiente posición disponible para una sección
     */
    public function getNextOrderIndex(int $courseId): int
    {
        $sql = "SELECT MAX(order_index) + 1 FROM {$this->table} WHERE course_id = ?";
        $result = $this->queryOne($sql, [$courseId]);
        
        return $result ? (int) $result['MAX(order_index) + 1'] : 0;
    }
}
