<?php declare(strict_types=1);

class TopicRepository extends BaseRepository
{
    protected string $table = 'topic';
    
    /**
     * Obtiene un topic por slug
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }
    
    /**
     * Crea o actualiza un topic
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
     * Obtiene todos los topics con conteo de cursos
     */
    public function getAllWithCourseCount(): array
    {
        $sql = "
            SELECT t.*, COUNT(c.id) as course_count 
            FROM {$this->table} t 
            LEFT JOIN course c ON t.id = c.topic_id AND c.is_deleted = 0
            GROUP BY t.id 
            ORDER BY t.name
        ";
        
        return $this->queryAll($sql);
    }
    
    /**
     * Obtiene topics populares (con más cursos)
     */
    public function getPopular(int $limit = 10): array
    {
        $sql = "
            SELECT t.*, COUNT(c.id) as course_count 
            FROM {$this->table} t 
            LEFT JOIN course c ON t.id = c.topic_id AND c.is_deleted = 0
            GROUP BY t.id 
            HAVING course_count > 0
            ORDER BY course_count DESC 
            LIMIT ?
        ";
        
        return $this->queryAll($sql, [$limit]);
    }
    
    /**
     * Busca topics por nombre
     */
    public function searchByName(string $query): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE name LIKE ? ORDER BY name";
        return $this->queryAll($sql, ["%{$query}%"]);
    }
    
    /**
     * Obtiene estadísticas de topics
     */
    public function getStats(): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_topics,
                COUNT(DISTINCT c.instructor_id) as total_instructors,
                COUNT(c.id) as total_courses
            FROM {$this->table} t
            LEFT JOIN course c ON t.id = c.topic_id AND c.is_deleted = 0
        ";
        
        return $this->queryOne($sql) ?: [];
    }
}
