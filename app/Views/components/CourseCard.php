<?php declare(strict_types=1);

/**
 * Componente CourseCard - Tarjeta de curso para el dashboard
 */
class CourseCard {
    private array $course;
    private string $uploadsPath;
    
    public function __construct(array $course, string $uploadsPath) {
        $this->course = $course;
        $this->uploadsPath = $uploadsPath;
    }
    
    public function render(): string {
        $course = $this->course;
        $thumbnail = $this->getThumbnail($course);
        $rating = $this->formatRating($course['avg_rating'] ?? 0, $course['ratings_count'] ?? 0);
        $duration = $this->formatDuration($course['total_duration'] ?? 0);
        
        // Preparar variables para el heredoc
        $instructorName = $course['instructor_name'] ?? 'Sin instructor';
        $topicName = $course['topic_name'] ?? 'Sin tema';
        $avgRating = $course['avg_rating'] ?? 0;
        $lessonsCount = $course['lessons_count'] ?? 0;
        $sectionsCount = $course['sections_count'] ?? 0;
        $courseName = $course['name'];
        $courseSlug = $course['slug'];
        
        return <<<HTML
        <div class="course-card bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-4 hover:bg-white/15 transition-all duration-300 group">
            <!-- Thumbnail -->
            <div class="relative mb-4 overflow-hidden rounded-lg">
                <img src="{$thumbnail}" 
                     alt="{$this->escape($courseName)}" 
                     class="w-full h-32 object-cover group-hover:scale-105 transition-transform duration-300"
                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIwIiBoZWlnaHQ9IjEyOCIgdmlld0JveD0iMCAwIDMyMCAxMjgiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMjAiIGhlaWdodD0iMTI4IiBmaWxsPSIjMzM0MTU1Ii8+CjxwYXRoIGQ9Ik0xNjAgNjRMMTkyIDk2TDE2MCAxMjhMMTI4IDk2TDE2MCA2NFoiIGZpbGw9IiM2QjcyODAiLz4KPC9zdmc+'">
                <div class="absolute top-2 right-2 bg-black/50 text-white text-xs px-2 py-1 rounded">
                    {$duration}
                </div>
            </div>
            
            <!-- Course Info -->
            <div class="space-y-2">
                <h3 class="font-semibold text-white text-lg line-clamp-2 group-hover:text-blue-300 transition-colors">
                    {$this->escape($courseName)}
                </h3>
                
                <div class="flex items-center text-gray-300 text-sm">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/>
                    </svg>
                    {$this->escape($instructorName)}
                </div>
                
                <div class="flex items-center text-gray-300 text-sm">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                    </svg>
                    {$this->escape($topicName)}
                </div>
                
                <!-- Rating -->
                <div class="flex items-center">
                    <?php echo StarRating::display($avgRating, $lessonsCount, 'sm'); ?>
                </div>
                
                <!-- Stats -->
                <div class="flex justify-between text-xs text-gray-400">
                    <span>{$lessonsCount} lecciones</span>
                    <span>{$sectionsCount} secciones</span>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="mt-4 flex gap-2">
                <button onclick="viewCourse('{$this->escape($courseSlug)}')" 
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm py-2 px-3 rounded-lg transition-colors">
                    Ver
                </button>
                
                <button onclick="resumeCourse('{$this->escape($courseSlug)}')" 
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm py-2 px-3 rounded-lg transition-colors">
                    Reanudar
                </button>
            </div>
            
            <!-- Secondary Actions -->
            <div class="mt-2 flex gap-1">
                <button onclick="renameCourse('{$this->escape($courseSlug)}', '{$this->escape($courseName)}')" 
                        class="flex-1 bg-gray-600 hover:bg-gray-700 text-white text-xs py-1.5 px-2 rounded transition-colors">
                    Renombrar
                </button>
                
                <button onclick="deleteCourse('{$this->escape($courseSlug)}', '{$this->escape($courseName)}')" 
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white text-xs py-1.5 px-2 rounded transition-colors">
                    Eliminar
                </button>
            </div>
        </div>
        HTML;
    }
    
    private function getThumbnail(array $course): string {
        // Buscar thumbnail en cache
        $cachePath = "cache/thumbs/{$course['slug']}.jpg";
        if (file_exists($cachePath)) {
            return $cachePath;
        }
        
        // Fallback a imagen por defecto
        return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIwIiBoZWlnaHQ9IjEyOCIgdmlld0JveD0iMCAwIDMyMCAxMjgiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMjAiIGhlaWdodD0iMTI4IiBmaWxsPSIjMzM0MTU1Ii8+CjxwYXRoIGQ9Ik0xNjAgNjRMMTkyIDk2TDE2MCAxMjhMMTI4IDk2TDE2MCA2NFoiIGZpbGw9IiM2QjcyODAiLz4KPC9zdmc+';
    }
    
    private function formatDuration(int $seconds): string {
        if ($seconds === 0) {
            return '--:--';
        }
        
        $hours = intval($seconds / 3600);
        $minutes = intval(($seconds % 3600) / 60);
        
        if ($hours > 0) {
            return sprintf('%d:%02d', $hours, $minutes);
        }
        return sprintf('%d:%02d', $minutes, $seconds % 60);
    }
    
    private function escape(string $text): string {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
