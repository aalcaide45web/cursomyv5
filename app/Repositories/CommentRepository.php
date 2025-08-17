<?php declare(strict_types=1);

/**
 * Repositorio para la tabla comments
 */
class CommentRepository extends BaseRepository {
    protected string $table = 'comments';
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Obtener comentarios de una lección
     */
    public function getByLesson(int $lessonId): array {
        $sql = "SELECT * FROM {$this->table} WHERE lesson_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lessonId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener comentarios de un curso
     */
    public function getByCourse(int $courseId): array {
        $sql = "
            SELECT c.*, l.name as lesson_name, l.file_path
            FROM {$this->table} c
            JOIN lesson l ON c.lesson_id = l.id
            JOIN section s ON l.section_id = s.id
            WHERE s.course_id = ?
            ORDER BY c.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courseId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear nuevo comentario
     */
    public function create(int $lessonId, string $content, ?int $timestamp = null): bool {
        $sql = "
            INSERT INTO {$this->table} (lesson_id, content, timestamp, created_at) 
            VALUES (?, ?, ?, ?)
        ";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $lessonId,
            $content,
            $timestamp,
            date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Actualizar comentario
     */
    public function update(int $id, string $content, ?int $timestamp = null): bool {
        $sql = "
            UPDATE {$this->table} 
            SET content = ?, timestamp = ?, updated_at = ? 
            WHERE id = ?
        ";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $content,
            $timestamp,
            date('Y-m-d H:i:s'),
            $id
        ]);
    }
    
    /**
     * Eliminar comentario
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([$id]);
    }
    
    /**
     * Buscar comentarios por contenido
     */
    public function searchByContent(string $query): array {
        $sql = "
            SELECT c.*, l.name as lesson_name, co.name as course_name
            FROM {$this->table} c
            JOIN lesson l ON c.lesson_id = l.id
            JOIN section s ON l.section_id = s.id
            JOIN course co ON s.course_id = co.id
            WHERE c.content LIKE ? AND co.is_deleted = 0
            ORDER BY c.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['%' . $query . '%']);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener comentarios recientes
     */
    public function getRecent(int $limit = 10): array {
        $sql = "
            SELECT c.*, l.name as lesson_name, co.name as course_name, co.slug as course_slug
            FROM {$this->table} c
            JOIN lesson l ON c.lesson_id = l.id
            JOIN section s ON l.section_id = s.id
            JOIN course co ON s.course_id = co.id
            WHERE co.is_deleted = 0
            ORDER BY c.created_at DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener estadísticas de comentarios
     */
    public function getStats(): array {
        $sql = "
            SELECT 
                COUNT(*) as total_comments,
                COUNT(DISTINCT lesson_id) as lessons_with_comments,
                COUNT(CASE WHEN timestamp IS NOT NULL THEN 1 END) as comments_with_timestamp,
                AVG(LENGTH(content)) as avg_content_length
            FROM {$this->table}
        ";
        
        return $this->queryOne($sql) ?: [];
    }
}
