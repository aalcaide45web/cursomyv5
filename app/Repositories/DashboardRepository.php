<?php declare(strict_types=1);

class DashboardRepository
{
    /**
     * Obtiene estadísticas generales del dashboard
     */
    public function getStats(): array
    {
        $sql = "
            SELECT 
                (SELECT COUNT(*) FROM course WHERE is_deleted = 0) as courses_count,
                (SELECT COUNT(*) FROM lesson) as lessons_count,
                (SELECT COALESCE(SUM(duration_seconds), 0) FROM lesson) as total_duration_seconds,
                (SELECT COUNT(*) FROM topic) as topics_count,
                (SELECT COUNT(*) FROM instructor) as instructors_count,
                (SELECT COUNT(*) FROM rating) as ratings_count,
                (SELECT AVG(avg_rating) FROM course WHERE is_deleted = 0 AND ratings_count > 0) as avg_course_rating
        ";
        
        $result = DB::query($sql)->fetch();
        
        if (!$result) {
            return [
                'courses_count' => 0,
                'lessons_count' => 0,
                'total_duration_seconds' => 0,
                'topics_count' => 0,
                'instructors_count' => 0,
                'ratings_count' => 0,
                'avg_course_rating' => 0
            ];
        }
        
        // Calcular horas totales
        $totalHours = round($result['total_duration_seconds'] / 3600, 1);
        
        return [
            'courses_count' => (int) $result['courses_count'],
            'lessons_count' => (int) $result['lessons_count'],
            'total_duration_seconds' => (float) $result['total_duration_seconds'],
            'total_hours' => $totalHours,
            'topics_count' => (int) $result['topics_count'],
            'instructors_count' => (int) $result['instructors_count'],
            'ratings_count' => (int) $result['ratings_count'],
            'avg_course_rating' => round((float) $result['avg_course_rating'], 1)
        ];
    }
    
    /**
     * Obtiene cursos recientes
     */
    public function getRecentCourses(int $limit = 8): array
    {
        $sql = "
            SELECT c.*, t.name as topic_name, t.slug as topic_slug, 
                   i.name as instructor_name, i.slug as instructor_slug,
                   (SELECT COUNT(*) FROM lesson l 
                    JOIN section s ON l.section_id = s.id 
                    WHERE s.course_id = c.id) as lesson_count
            FROM course c
            JOIN topic t ON c.topic_id = t.id
            JOIN instructor i ON c.instructor_id = i.id
            WHERE c.is_deleted = 0
            ORDER BY c.id DESC
            LIMIT ?
        ";
        
        return DB::prepare($sql)->execute([$limit])->fetchAll();
    }
    
    /**
     * Obtiene cursos populares
     */
    public function getPopularCourses(int $limit = 8): array
    {
        $sql = "
            SELECT c.*, t.name as topic_name, t.slug as topic_slug, 
                   i.name as instructor_name, i.slug as instructor_slug,
                   (SELECT COUNT(*) FROM lesson l 
                    JOIN section s ON l.section_id = s.id 
                    WHERE s.course_id = c.id) as lesson_count
            FROM course c
            JOIN topic t ON c.topic_id = t.id
            JOIN instructor i ON c.instructor_id = i.id
            WHERE c.is_deleted = 0 AND c.ratings_count > 0
            ORDER BY c.avg_rating DESC, c.ratings_count DESC
            LIMIT ?
        ";
        
        return DB::prepare($sql)->execute([$limit])->fetchAll();
    }
    
    /**
     * Obtiene lecciones recientes
     */
    public function getRecentLessons(int $limit = 10): array
    {
        $sql = "
            SELECT l.*, s.name as section_name, c.name as course_name, c.slug as course_slug,
                   t.name as topic_name, i.name as instructor_name
            FROM lesson l
            JOIN section s ON l.section_id = s.id
            JOIN course c ON s.course_id = c.id
            JOIN topic t ON c.topic_id = t.id
            JOIN instructor i ON c.instructor_id = i.id
            WHERE c.is_deleted = 0
            ORDER BY l.id DESC
            LIMIT ?
        ";
        
        return DB::prepare($sql)->execute([$limit])->fetchAll();
    }
    
    /**
     * Obtiene estadísticas por topic
     */
    public function getStatsByTopic(): array
    {
        $sql = "
            SELECT t.name, t.slug, COUNT(c.id) as course_count,
                   COUNT(l.id) as lesson_count,
                   COALESCE(SUM(l.duration_seconds), 0) as total_duration
            FROM topic t
            LEFT JOIN course c ON t.id = c.topic_id AND c.is_deleted = 0
            LEFT JOIN section s ON c.id = s.course_id
            LEFT JOIN lesson l ON s.id = l.section_id
            GROUP BY t.id, t.name, t.slug
            HAVING course_count > 0
            ORDER BY course_count DESC
        ";
        
        return DB::query($sql)->fetchAll();
    }
    
    /**
     * Obtiene estadísticas por instructor
     */
    public function getStatsByInstructor(): array
    {
        $sql = "
            SELECT i.name, i.slug, COUNT(c.id) as course_count,
                   COUNT(l.id) as lesson_count,
                   COALESCE(SUM(l.duration_seconds), 0) as total_duration,
                   AVG(c.avg_rating) as avg_rating
            FROM instructor i
            LEFT JOIN course c ON i.id = c.instructor_id AND c.is_deleted = 0
            LEFT JOIN section s ON c.id = s.course_id
            LEFT JOIN lesson l ON s.id = l.section_id
            GROUP BY i.id, i.name, i.slug
            HAVING course_count > 0
            ORDER BY course_count DESC
        ";
        
        return DB::query($sql)->fetchAll();
    }
    
    /**
     * Obtiene el progreso de aprendizaje (últimas lecciones vistas)
     */
    public function getLearningProgress(int $limit = 10): array
    {
        $sql = "
            SELECT p.*, l.name as lesson_name, l.file_path,
                   s.name as section_name, c.name as course_name, c.slug as course_slug,
                   t.name as topic_name, i.name as instructor_name
            FROM progress p
            JOIN lesson l ON p.lesson_id = l.id
            JOIN section s ON l.section_id = s.id
            JOIN course c ON s.course_id = c.id
            JOIN topic t ON c.topic_id = t.id
            JOIN instructor i ON c.instructor_id = i.id
            WHERE c.is_deleted = 0
            ORDER BY p.updated_at DESC
            LIMIT ?
        ";
        
        return DB::prepare($sql)->execute([$limit])->fetchAll();
    }
}
