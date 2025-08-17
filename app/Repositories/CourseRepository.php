<?php declare(strict_types=1);

class CourseRepository extends BaseRepository
{
    protected string $table = 'course';
    
    /**
     * Obtiene un curso por slug
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }
    
    /**
     * Obtiene todos los cursos activos (no eliminados)
     */
    public function getAllActive(): array
    {
        $sql = "
            SELECT c.*, t.name as topic_name, t.slug as topic_slug, 
                   i.name as instructor_name, i.slug as instructor_slug
            FROM {$this->table} c
            JOIN topic t ON c.topic_id = t.id
            JOIN instructor i ON c.instructor_id = i.id
            WHERE c.is_deleted = 0
            ORDER BY c.name
        ";
        
        return $this->queryAll($sql);
    }
    
    /**
     * Obtiene un curso completo con topic e instructor
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "
            SELECT c.*, t.name as topic_name, t.slug as topic_slug, 
                   i.name as instructor_name, i.slug as instructor_slug
            FROM {$this->table} c
            JOIN topic t ON c.topic_id = t.id
            JOIN instructor i ON c.instructor_id = i.id
            WHERE c.id = ? AND c.is_deleted = 0
        ";
        
        return $this->queryOne($sql, [$id]);
    }
    
    /**
     * Obtiene un curso por slug con detalles
     */
    public function findBySlugWithDetails(string $slug): ?array
    {
        $sql = "
            SELECT c.*, t.name as topic_name, t.slug as topic_slug, 
                   i.name as instructor_name, i.slug as instructor_slug
            FROM {$this->table} c
            JOIN topic t ON c.topic_id = t.id
            JOIN instructor i ON c.instructor_id = i.id
            WHERE c.slug = ? AND c.is_deleted = 0
        ";
        
        return $this->queryOne($sql, [$slug]);
    }
    
    /**
     * Obtiene cursos por topic
     */
    public function getByTopic(int $topicId): array
    {
        $sql = "
            SELECT c.*, t.name as topic_name, t.slug as topic_slug, 
                   i.name as instructor_name, i.slug as instructor_slug
            FROM {$this->table} c
            JOIN topic t ON c.topic_id = t.id
            JOIN instructor i ON c.instructor_id = i.id
            WHERE c.topic_id = ? AND c.is_deleted = 0
            ORDER BY c.name
        ";
        
        return $this->queryAll($sql, [$topicId]);
    }
    
    /**
     * Obtiene cursos por instructor
     */
    public function getByInstructor(int $instructorId): array
    {
        $sql = "
            SELECT c.*, t.name as topic_name, t.slug as topic_slug, 
                   i.name as instructor_name, i.slug as instructor_slug
            FROM {$this->table} c
            JOIN topic t ON c.topic_id = t.id
            JOIN instructor i ON c.instructor_id = i.id
            WHERE c.instructor_id = ? AND c.is_deleted = 0
            ORDER BY c.name
        ";
        
        return $this->queryAll($sql, [$instructorId]);
    }
    
    /**
     * Busca cursos por nombre
     */
    public function searchByName(string $query): array
    {
        $sql = "
            SELECT c.*, t.name as topic_name, t.slug as topic_slug, 
                   i.name as instructor_name, i.slug as instructor_slug
            FROM {$this->table} c
            JOIN topic t ON c.topic_id = t.id
            JOIN instructor i ON c.instructor_id = i.id
            WHERE c.name LIKE ? AND c.is_deleted = 0
            ORDER BY c.name
        ";
        
        return $this->queryAll($sql, ["%{$query}%"]);
    }
    
    /**
     * Obtiene cursos populares (con mejor rating)
     */
    public function getPopular(int $limit = 10): array
    {
        $sql = "
            SELECT c.*, t.name as topic_name, t.slug as topic_slug, 
                   i.name as instructor_name, i.slug as instructor_slug
            FROM {$this->table} c
            JOIN topic t ON c.topic_id = t.id
            JOIN instructor i ON c.instructor_id = i.id
            WHERE c.is_deleted = 0 AND c.ratings_count > 0
            ORDER BY c.avg_rating DESC, c.ratings_count DESC
            LIMIT ?
        ";
        
        return $this->queryAll($sql, [$limit]);
    }
    
    /**
     * Crea o actualiza un curso
     */
    public function createOrUpdate(array $data): int
    {
        $slug = Str::slugify($data['name']);
        
        // Buscar si ya existe
        $existing = $this->findBySlug($slug);
        if ($existing) {
            $this->update($existing['id'], $data);
            return $existing['id'];
        }
        
        // Crear nuevo
        $data['slug'] = $slug;
        return $this->create($data);
    }
    
    /**
     * Marca un curso como eliminado (soft delete)
     */
    public function softDelete(int $id): bool
    {
        return $this->update($id, ['is_deleted' => 1]);
    }
    
    /**
     * Reactiva un curso eliminado
     */
    public function reactivate(int $id): bool
    {
        return $this->update($id, ['is_deleted' => 0]);
    }
    
    /**
     * Renombra un curso
     */
    public function rename(int $id, string $newName): bool
    {
        $newSlug = Str::slugify($newName);
        return $this->update($id, [
            'name' => $newName,
            'slug' => $newSlug
        ]);
    }
    
    /**
     * Actualiza el rating promedio de un curso
     */
    public function updateRating(int $id, float $avgRating, int $ratingsCount): bool
    {
        return $this->update($id, [
            'avg_rating' => $avgRating,
            'ratings_count' => $ratingsCount
        ]);
    }
    
    /**
     * Obtiene estadísticas de cursos
     */
    public function getStats(): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_courses,
                COUNT(CASE WHEN is_deleted = 0 THEN 1 END) as active_courses,
                COUNT(CASE WHEN is_deleted = 1 THEN 1 END) as deleted_courses,
                AVG(CASE WHEN is_deleted = 0 THEN avg_rating END) as avg_rating,
                SUM(CASE WHEN is_deleted = 0 THEN ratings_count END) as total_ratings
            FROM {$this->table}
        ";
        
        return $this->queryOne($sql) ?: [];
    }
}
