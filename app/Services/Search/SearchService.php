<?php declare(strict_types=1);

/**
 * Servicio de Búsqueda Global - Maneja FTS5 y búsquedas compuestas
 */
class SearchService {
    private PDO $db;
    private array $searchableTables;
    
    public function __construct() {
        $this->db = DB::getInstance()->getConnection();
        $this->searchableTables = [
            'topic' => ['name', 'description'],
            'instructor' => ['name', 'bio'],
            'course' => ['name', 'description'],
            'section' => ['name', 'description'],
            'lesson' => ['name', 'description'],
            'note' => ['content'],
            'comment' => ['content']
        ];
    }
    
    /**
     * Realizar búsqueda global
     */
    public function search(string $query, int $limit = 50): array {
        $query = trim($query);
        if (empty($query)) {
            return ['success' => false, 'error' => 'Query vacío'];
        }
        
        try {
            // Intentar usar FTS5 primero
            if ($this->isFTS5Available()) {
                return $this->searchWithFTS5($query, $limit);
            }
            
            // Fallback a búsqueda compuesta
            return $this->searchWithComposite($query, $limit);
        } catch (Exception $e) {
            error_log("Error en búsqueda: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error en búsqueda'];
        }
    }
    
    /**
     * Verificar si FTS5 está disponible
     */
    private function isFTS5Available(): bool {
        try {
            $stmt = $this->db->query("PRAGMA compile_options");
            $options = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return in_array('ENABLE_FTS5', $options);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Búsqueda usando FTS5
     */
    private function searchWithFTS5(string $query, int $limit): array {
        try {
            // Crear tabla virtual FTS5 si no existe
            $this->createFTSTable();
            
            // Realizar búsqueda FTS5
            $ftsResults = $this->searchFTSTable($query, $limit);
            
            // Enriquecer resultados con datos completos
            $enrichedResults = $this->enrichFTSResults($ftsResults);
            
            return [
                'success' => true,
                'data' => $enrichedResults,
                'method' => 'FTS5',
                'query' => $query
            ];
        } catch (Exception $e) {
            error_log("Error en FTS5: " . $e->getMessage());
            // Fallback a búsqueda compuesta
            return $this->searchWithComposite($query, $limit);
        }
    }
    
    /**
     * Crear tabla virtual FTS5
     */
    private function createFTSTable(): void {
        $sql = "
            CREATE VIRTUAL TABLE IF NOT EXISTS search_index USING fts5(
                content,
                table_name,
                record_id,
                field_name,
                searchable_text,
                tokenize='porter unicode61'
            )
        ";
        $this->db->exec($sql);
        
        // Indexar contenido existente si la tabla está vacía
        $stmt = $this->db->query("SELECT COUNT(*) FROM search_index");
        if ($stmt->fetchColumn() == 0) {
            $this->rebuildSearchIndex();
        }
    }
    
    /**
     * Reconstruir índice de búsqueda
     */
    public function rebuildSearchIndex(): bool {
        try {
            // Limpiar índice existente
            $this->db->exec("DELETE FROM search_index");
            
            // Indexar topics
            $this->indexTopics();
            
            // Indexar instructors
            $this->indexInstructors();
            
            // Indexar courses
            $this->indexCourses();
            
            // Indexar sections
            $this->indexSections();
            
            // Indexar lessons
            $this->indexLessons();
            
            // Indexar notes
            $this->indexNotes();
            
            // Indexar comments
            $this->indexComments();
            
            return true;
        } catch (Exception $e) {
            error_log("Error reconstruyendo índice: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Indexar topics
     */
    private function indexTopics(): void {
        $sql = "
            INSERT INTO search_index (content, table_name, record_id, field_name, searchable_text)
            SELECT 
                'topic' as content,
                'topic' as table_name,
                id as record_id,
                'name' as field_name,
                name as searchable_text
            FROM topic
            WHERE is_deleted = 0
        ";
        $this->db->exec($sql);
        
        // Indexar descripciones
        $sql = "
            INSERT INTO search_index (content, table_name, record_id, field_name, searchable_text)
            SELECT 
                'topic' as content,
                'topic' as table_name,
                id as record_id,
                'description' as field_name,
                COALESCE(description, '') as searchable_text
            FROM topic
            WHERE is_deleted = 0 AND description IS NOT NULL AND description != ''
        ";
        $this->db->exec($sql);
    }
    
    /**
     * Indexar instructors
     */
    private function indexInstructors(): void {
        $sql = "
            INSERT INTO search_index (content, table_name, record_id, field_name, searchable_text)
            SELECT 
                'instructor' as content,
                'instructor' as table_name,
                id as record_id,
                'name' as field_name,
                name as searchable_text
            FROM instructor
            WHERE is_deleted = 0
        ";
        $this->db->exec($sql);
        
        // Indexar bio
        $sql = "
            INSERT INTO search_index (content, table_name, record_id, field_name, searchable_text)
            SELECT 
                'instructor' as content,
                'instructor' as table_name,
                id as record_id,
                'bio' as field_name,
                COALESCE(bio, '') as searchable_text
            FROM instructor
            WHERE is_deleted = 0 AND bio IS NOT NULL AND bio != ''
        ";
        $this->db->exec($sql);
    }
    
    /**
     * Indexar courses
     */
    private function indexCourses(): void {
        $sql = "
            INSERT INTO search_index (content, table_name, record_id, field_name, searchable_text)
            SELECT 
                'course' as content,
                'course' as table_name,
                id as record_id,
                'name' as field_name,
                name as searchable_text
            FROM course
            WHERE is_deleted = 0
        ";
        $this->db->exec($sql);
        
        // Indexar descripciones
        $sql = "
            INSERT INTO search_index (content, table_name, record_id, field_name, searchable_text)
            SELECT 
                'course' as content,
                'course' as table_name,
                id as record_id,
                'description' as field_name,
                COALESCE(description, '') as searchable_text
            FROM course
            WHERE is_deleted = 0 AND description IS NOT NULL AND description != ''
        ";
        $this->db->exec($sql);
    }
    
    /**
     * Indexar sections
     */
    private function indexSections(): void {
        $sql = "
            INSERT INTO search_index (content, table_name, record_id, field_name, searchable_text)
            SELECT 
                'section' as content,
                'section' as table_name,
                id as record_id,
                'name' as field_name,
                name as searchable_text
            FROM section
            WHERE is_deleted = 0
        ";
        $this->db->exec($sql);
        
        // Indexar descripciones
        $sql = "
            INSERT INTO search_index (content, table_name, record_id, field_name, searchable_text)
            SELECT 
                'section' as content,
                'section' as table_name,
                id as record_id,
                'description' as field_name,
                COALESCE(description, '') as searchable_text
            FROM section
            WHERE is_deleted = 0 AND description IS NOT NULL AND description != ''
        ";
        $this->db->exec($sql);
    }
    
    /**
     * Indexar lessons
     */
    private function indexLessons(): void {
        $sql = "
            INSERT INTO search_index (content, table_name, record_id, field_name, searchable_text)
            SELECT 
                'lesson' as content,
                'lesson' as table_name,
                id as record_id,
                'name' as field_name,
                name as searchable_text
            FROM lesson
            WHERE is_deleted = 0
        ";
        $this->db->exec($sql);
        
        // Indexar descripciones
        $sql = "
            INSERT INTO search_index (content, table_name, record_id, field_name, searchable_text)
            SELECT 
                'lesson' as content,
                'lesson' as table_name,
                id as record_id,
                'description' as field_name,
                COALESCE(description, '') as searchable_text
            FROM lesson
            WHERE is_deleted = 0 AND description IS NOT NULL AND description != ''
        ";
        $this->db->exec($sql);
    }
    
    /**
     * Indexar notes
     */
    private function indexNotes(): void {
        $sql = "
            INSERT INTO search_index (content, table_name, record_id, field_name, searchable_text)
            SELECT 
                'note' as content,
                'note' as table_name,
                id as record_id,
                'content' as field_name,
                content as searchable_text
            FROM note
            WHERE is_deleted = 0
        ";
        $this->db->exec($sql);
    }
    
    /**
     * Indexar comments
     */
    private function indexComments(): void {
        $sql = "
            INSERT INTO search_index (content, table_name, record_id, field_name, searchable_text)
            SELECT 
                'comment' as content,
                'comment' as table_name,
                id as record_id,
                'content' as field_name,
                content as searchable_text
            FROM comment
            WHERE is_deleted = 0
        ";
        $this->db->exec($sql);
    }
    
    /**
     * Buscar en tabla FTS5
     */
    private function searchFTSTable(string $query, int $limit): array {
        $sql = "
            SELECT 
                content,
                table_name,
                record_id,
                field_name,
                searchable_text,
                rank
            FROM search_index
            WHERE searchable_text MATCH ?
            ORDER BY rank
            LIMIT ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$query, $limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Enriquecer resultados FTS5 con datos completos
     */
    private function enrichFTSResults(array $ftsResults): array {
        $enriched = [];
        
        foreach ($ftsResults as $result) {
            $fullRecord = $this->getFullRecord($result['table_name'], $result['record_id']);
            if ($fullRecord) {
                $enriched[] = [
                    'type' => $result['content'],
                    'table' => $result['table_name'],
                    'id' => $result['record_id'],
                    'field' => $result['field_name'],
                    'matched_text' => $result['searchable_text'],
                    'rank' => $result['rank'],
                    'data' => $fullRecord
                ];
            }
        }
        
        return $enriched;
    }
    
    /**
     * Obtener registro completo
     */
    private function getFullRecord(string $table, int $id): ?array {
        try {
            $sql = "SELECT * FROM {$table} WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($record) {
                // Agregar información adicional según el tipo
                switch ($table) {
                    case 'lesson':
                        $record = $this->enrichLesson($record);
                        break;
                    case 'section':
                        $record = $this->enrichSection($record);
                        break;
                    case 'course':
                        $record = $this->enrichCourse($record);
                        break;
                }
            }
            
            return $record;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Enriquecer lección con información del curso
     */
    private function enrichLesson(array $lesson): array {
        $sql = "
            SELECT c.name as course_name, c.slug as course_slug, s.name as section_name
            FROM course c
            JOIN section s ON c.id = s.course_id
            WHERE s.id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lesson['section_id']]);
        $courseInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($courseInfo) {
            $lesson['course_name'] = $courseInfo['course_name'];
            $lesson['course_slug'] = $courseInfo['course_slug'];
            $lesson['section_name'] = $courseInfo['section_name'];
        }
        
        return $lesson;
    }
    
    /**
     * Enriquecer sección con información del curso
     */
    private function enrichSection(array $section): array {
        $sql = "SELECT name as course_name, slug as course_slug FROM course WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$section['course_id']]);
        $courseInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($courseInfo) {
            $section['course_name'] = $courseInfo['course_name'];
            $section['course_slug'] = $courseInfo['course_slug'];
        }
        
        return $section;
    }
    
    /**
     * Enriquecer curso con información del instructor y topic
     */
    private function enrichCourse(array $course): array {
        $sql = "
            SELECT i.name as instructor_name, t.name as topic_name
            FROM instructor i
            LEFT JOIN topic t ON i.topic_id = t.id
            WHERE i.id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$course['instructor_id']]);
        $info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($info) {
            $course['instructor_name'] = $info['instructor_name'];
            $course['topic_name'] = $info['topic_name'];
        }
        
        return $course;
    }
    
    /**
     * Búsqueda compuesta (fallback cuando FTS5 no está disponible)
     */
    private function searchWithComposite(string $query, int $limit): array {
        $results = [];
        $searchTerm = '%' . $query . '%';
        
        // Buscar en topics
        $results = array_merge($results, $this->searchTopics($searchTerm));
        
        // Buscar en instructors
        $results = array_merge($results, $this->searchInstructors($searchTerm));
        
        // Buscar en courses
        $results = array_merge($results, $this->searchCourses($searchTerm));
        
        // Buscar en sections
        $results = array_merge($results, $this->searchSections($searchTerm));
        
        // Buscar en lessons
        $results = array_merge($results, $this->searchLessons($searchTerm));
        
        // Buscar en notes
        $results = array_merge($results, $this->searchNotes($searchTerm));
        
        // Buscar en comments
        $results = array_merge($results, $this->searchComments($searchTerm));
        
        // Ordenar por relevancia y limitar
        usort($results, function($a, $b) use ($query) {
            return $this->calculateRelevance($b, $query) - $this->calculateRelevance($a, $query);
        });
        
        return array_slice($results, 0, $limit);
    }
    
    /**
     * Buscar en topics
     */
    private function searchTopics(string $searchTerm): array {
        $sql = "
            SELECT 
                'topic' as type,
                'topic' as table_name,
                id,
                name,
                description,
                0 as rank
            FROM topic
            WHERE (name LIKE ? OR description LIKE ?) AND is_deleted = 0
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar en instructors
     */
    private function searchInstructors(string $searchTerm): array {
        $sql = "
            SELECT 
                'instructor' as type,
                'instructor' as table_name,
                id,
                name,
                bio as description,
                0 as rank
            FROM instructor
            WHERE (name LIKE ? OR bio LIKE ?) AND is_deleted = 0
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar en courses
     */
    private function searchCourses(string $searchTerm): array {
        $sql = "
            SELECT 
                'course' as type,
                'course' as table_name,
                c.id,
                c.name,
                c.description,
                0 as rank
            FROM course c
            WHERE (c.name LIKE ? OR c.description LIKE ?) AND c.is_deleted = 0
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar en sections
     */
    private function searchSections(string $searchTerm): array {
        $sql = "
            SELECT 
                'section' as type,
                'section' as table_name,
                s.id,
                s.name,
                s.description,
                0 as rank
            FROM section s
            WHERE (s.name LIKE ? OR s.description LIKE ?) AND s.is_deleted = 0
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar en lessons
     */
    private function searchLessons(string $searchTerm): array {
        $sql = "
            SELECT 
                'lesson' as type,
                'lesson' as table_name,
                l.id,
                l.name,
                l.description,
                0 as rank
            FROM lesson l
            WHERE (l.name LIKE ? OR l.description LIKE ?) AND l.is_deleted = 0
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar en notes
     */
    private function searchNotes(string $searchTerm): array {
        $sql = "
            SELECT 
                'note' as type,
                'note' as table_name,
                n.id,
                n.content as name,
                n.content as description,
                0 as rank
            FROM note n
            WHERE n.content LIKE ? AND n.is_deleted = 0
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar en comments
     */
    private function searchComments(string $searchTerm): array {
        $sql = "
            SELECT 
                'comment' as type,
                'comment' as table_name,
                c.id,
                c.content as name,
                c.content as description,
                0 as rank
            FROM comment c
            WHERE c.content LIKE ? AND c.is_deleted = 0
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Calcular relevancia para búsqueda compuesta
     */
    private function calculateRelevance(array $result, string $query): int {
        $relevance = 0;
        $query = strtolower($query);
        
        // Prioridad por tipo
        $typePriority = [
            'course' => 100,
            'lesson' => 90,
            'section' => 80,
            'instructor' => 70,
            'topic' => 60,
            'note' => 50,
            'comment' => 40
        ];
        
        $relevance += $typePriority[$result['type']] ?? 0;
        
        // Prioridad por coincidencia exacta
        if (stripos($result['name'], $query) !== false) {
            $relevance += 50;
        }
        
        // Prioridad por coincidencia en descripción
        if (isset($result['description']) && stripos($result['description'], $query) !== false) {
            $relevance += 25;
        }
        
        return $relevance;
    }
    
    /**
     * Obtener estadísticas de búsqueda
     */
    public function getSearchStats(): array {
        try {
            $stats = [];
            
            foreach ($this->searchableTables as $table => $fields) {
                $sql = "SELECT COUNT(*) as count FROM {$table} WHERE is_deleted = 0";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                $stats[$table] = $stmt->fetchColumn();
            }
            
            return $stats;
        } catch (Exception $e) {
            return [];
        }
    }
}
