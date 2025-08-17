<?php declare(strict_types=1);

/**
 * Repositorio para la tabla notes
 */
class NoteRepository extends BaseRepository {
    protected string $table = 'notes';
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Obtener notas de una lección
     */
    public function getByLesson(int $lessonId): array {
        $sql = "SELECT * FROM {$this->table} WHERE lesson_id = ? ORDER BY timestamp ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lessonId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener notas de un curso
     */
    public function getByCourse(int $courseId): array {
        $sql = "
            SELECT n.*, l.name as lesson_name, l.file_path
            FROM {$this->table} n
            JOIN lesson l ON n.lesson_id = l.id
            JOIN section s ON l.section_id = s.id
            WHERE s.course_id = ?
            ORDER BY n.timestamp ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courseId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear nueva nota
     */
    public function create(int $lessonId, int $timestamp, string $content): bool {
        $sql = "
            INSERT INTO {$this->table} (lesson_id, timestamp, content, created_at) 
            VALUES (?, ?, ?, ?)
        ";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $lessonId,
            $timestamp,
            $content,
            date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Actualizar nota
     */
    public function update(int $id, int $timestamp, string $content): bool {
        $sql = "
            UPDATE {$this->table} 
            SET timestamp = ?, content = ?, updated_at = ? 
            WHERE id = ?
        ";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $timestamp,
            $content,
            date('Y-m-d H:i:s'),
            $id
        ]);
    }
    
    /**
     * Eliminar nota
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([$id]);
    }
    
    /**
     * Buscar notas por contenido
     */
    public function searchByContent(string $query): array {
        $sql = "
            SELECT n.*, l.name as lesson_name, c.name as course_name
            FROM {$this->table} n
            JOIN lesson l ON n.lesson_id = l.id
            JOIN section s ON l.section_id = s.id
            JOIN course c ON s.course_id = c.id
            WHERE n.content LIKE ? AND c.is_deleted = 0
            ORDER BY n.timestamp ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['%' . $query . '%']);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener estadísticas de notas
     */
    public function getStats(): array {
        $sql = "
            SELECT 
                COUNT(*) as total_notes,
                COUNT(DISTINCT lesson_id) as lessons_with_notes,
                AVG(LENGTH(content)) as avg_content_length
            FROM {$this->table}
        ";
        
        return $this->queryOne($sql) ?: [];
    }
}
