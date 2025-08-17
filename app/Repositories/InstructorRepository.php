<?php declare(strict_types=1);

class InstructorRepository extends BaseRepository
{
    protected string $table = 'instructor';
    
    /**
     * Obtiene un instructor por slug
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }
    
    /**
     * Crea o actualiza un instructor
     */
    public function createOrUpdate(string $name): int
    {
        $slug = Str::slugify($name);
        
        // Buscar si ya existe
        $existing = $this->findBySlug($slug);
        if ($existing) {
            return $existing['id'];
        }
        
        // Crear nuevo
        return $this->create([
            'name' => $name,
            'slug' => $slug
        ]);
    }
    
    /**
     * Obtiene todos los instructores con conteo de cursos
     */
    public function getAllWithCourseCount(): array
    {
        $sql = "
            SELECT i.*, COUNT(c.id) as course_count 
            FROM {$this->table} i 
            LEFT JOIN course c ON i.id = c.instructor_id AND c.is_deleted = 0
            GROUP BY i.id 
            ORDER BY i.name
        ";
        
        return $this->queryAll($sql);
    }
    
    /**
     * Obtiene instructores populares (con más cursos)
     */
    public function getPopular(int $limit = 10): array
    {
        $sql = "
            SELECT i.*, COUNT(c.id) as course_count 
            FROM {$this->table} i 
            LEFT JOIN course c ON i.id = c.instructor_id AND c.is_deleted = 0
            GROUP BY i.id 
            HAVING course_count > 0
            ORDER BY course_count DESC 
            LIMIT ?
        ";
        
        return $this->queryAll($sql, [$limit]);
    }
    
    /**
     * Busca instructores por nombre
     */
    public function searchByName(string $query): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE name LIKE ? ORDER BY name";
        return $this->queryAll($sql, ["%{$query}%"]);
    }
    
    /**
     * Obtiene instructores por topic
     */
    public function getByTopic(int $topicId): array
    {
        $sql = "
            SELECT DISTINCT i.*, COUNT(c.id) as course_count
            FROM {$this->table} i
            JOIN course c ON i.id = c.instructor_id AND c.is_deleted = 0
            WHERE c.topic_id = ?
            GROUP BY i.id
            ORDER BY course_count DESC, i.name
        ";
        
        return $this->queryAll($sql, [$topicId]);
    }
    
    /**
     * Obtiene estadísticas de instructores
     */
    public function getStats(): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_instructors,
                COUNT(DISTINCT c.topic_id) as total_topics,
                COUNT(c.id) as total_courses,
                AVG(c.avg_rating) as avg_rating
            FROM {$this->table} i
            LEFT JOIN course c ON i.id = c.instructor_id AND c.is_deleted = 0
        ";
        
        return $this->queryOne($sql) ?: [];
    }
}
