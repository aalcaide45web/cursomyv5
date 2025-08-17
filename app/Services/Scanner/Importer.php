<?php declare(strict_types=1);

class Importer
{
    private FilesystemScanner $scanner;
    private Hasher $hasher;
    private MediaProbe $mediaProbe;
    private TopicRepository $topicRepo;
    private InstructorRepository $instructorRepo;
    private CourseRepository $courseRepo;
    private SectionRepository $sectionRepo;
    private LessonRepository $lessonRepo;
    
    private array $importStats = [];
    private array $importLogs = [];
    
    public function __construct(
        FilesystemScanner $scanner,
        Hasher $hasher,
        MediaProbe $mediaProbe
    ) {
        $this->scanner = $scanner;
        $this->hasher = $hasher;
        $this->mediaProbe = $mediaProbe;
        
        $this->topicRepo = new TopicRepository();
        $this->instructorRepo = new InstructorRepository();
        $this->courseRepo = new CourseRepository();
        $this->sectionRepo = new SectionRepository();
        $this->lessonRepo = new LessonRepository();
    }
    
    /**
     * Importa archivos de forma incremental (solo cambios)
     */
    public function importIncremental(): array
    {
        $this->resetStats();
        $this->addLog('🚀 Iniciando importación incremental...');
        
        try {
            // Escanear archivos
            $scanResult = $this->scanner->scan();
            $this->addLog("📁 Escaneados {$scanResult['total_files']} archivos");
            
            if (empty($scanResult['files'])) {
                $this->addLog('ℹ️ No se encontraron archivos para importar');
                return $this->getImportResult();
            }
            
            // Filtrar solo archivos modificados
            $changedFiles = [];
            foreach ($scanResult['files'] as $file) {
                if ($this->hasher->hasFileChanged($file['full_path'])) {
                    $changedFiles[] = $file;
                }
            }
            
            $this->addLog("🔄 {$scanResult['total_files']} archivos escaneados, {$this->importStats['files_processed']} modificados");
            
            if (empty($changedFiles)) {
                $this->addLog('✅ No hay archivos modificados para importar');
                return $this->getImportResult();
            }
            
            // Importar archivos modificados
            $this->importFiles($changedFiles);
            
            // Limpiar hashes obsoletos
            $cleanedHashes = $this->hasher->cleanStaleHashes($this->scanner->getUploadsPath());
            if ($cleanedHashes > 0) {
                $this->addLog("🧹 Limpiados {$cleanedHashes} hashes obsoletos");
            }
            
            $this->addLog('✅ Importación incremental completada');
            
        } catch (Exception $e) {
            $this->addLog('❌ Error durante importación incremental: ' . $e->getMessage());
            $this->importStats['errors']++;
        }
        
        return $this->getImportResult();
    }
    
    /**
     * Reconstruye completamente la base de datos desde los archivos
     */
    public function importRebuild(): array
    {
        $this->resetStats();
        $this->addLog('🚀 Iniciando reconstrucción completa...');
        
        try {
            // Limpiar base de datos existente (soft delete)
            $this->softDeleteAllCourses();
            $this->addLog('🗑️ Cursos existentes marcados como eliminados');
            
            // Escanear archivos
            $scanResult = $this->scanner->scan();
            $this->addLog("📁 Escaneados {$scanResult['total_files']} archivos");
            
            if (empty($scanResult['files'])) {
                $this->addLog('ℹ️ No se encontraron archivos para importar');
                return $this->getImportResult();
            }
            
            // Importar todos los archivos
            $this->importFiles($scanResult['files']);
            
            // Limpiar hashes obsoletos
            $cleanedHashes = $this->hasher->cleanStaleHashes($this->scanner->getUploadsPath());
            if ($cleanedHashes > 0) {
                $this->addLog("🧹 Limpiados {$cleanedHashes} hashes obsoletos");
            }
            
            $this->addLog('✅ Reconstrucción completa completada');
            
        } catch (Exception $e) {
            $this->addLog('❌ Error durante reconstrucción: ' . $e->getMessage());
            $this->importStats['errors']++;
        }
        
        return $this->getImportResult();
    }
    
    /**
     * Importa una lista de archivos
     */
    private function importFiles(array $files): void
    {
        $totalFiles = count($files);
        
        foreach ($files as $index => $file) {
            try {
                $this->importStats['files_processed']++;
                
                $this->addLog("📝 Procesando archivo " . ($index + 1) . "/{$totalFiles}: {$file['relative_path']}");
                
                // Importar archivo
                $this->importFile($file);
                
                // Actualizar hash cache
                $this->hasher->updateCachedHash($file['full_path'], $file['hash']);
                
                $this->importStats['files_imported']++;
                
            } catch (Exception $e) {
                $this->addLog("❌ Error procesando {$file['relative_path']}: " . $e->getMessage());
                $this->importStats['errors']++;
            }
        }
    }
    
    /**
     * Importa un archivo individual
     */
    private function importFile(array $file): void
    {
        $parsedPath = $file['parsed_path'];
        
        // Validar estructura mínima
        if (!$parsedPath['topic'] || !$parsedPath['instructor'] || !$parsedPath['course']) {
            $this->addLog("⚠️ Estructura de directorio inválida para: {$file['relative_path']}");
            return;
        }
        
        // Crear o actualizar topic
        $topicId = $this->topicRepo->createOrUpdate($parsedPath['topic']);
        
        // Crear o actualizar instructor
        $instructorId = $this->instructorRepo->createOrUpdate($parsedPath['instructor']);
        
        // Crear o actualizar curso
        $courseData = [
            'name' => $parsedPath['course'],
            'topic_id' => $topicId,
            'instructor_id' => $instructorId,
            'is_deleted' => 0
        ];
        
        $courseId = $this->courseRepo->createOrUpdate($courseData);
        
        // Si hay sección, crearla
        $sectionId = null;
        if ($parsedPath['section']) {
            $sectionData = [
                'name' => $parsedPath['section'],
                'course_id' => $courseId,
                'order_index' => $this->sectionRepo->getNextOrderIndex($courseId)
            ];
            
            $sectionId = $this->sectionRepo->create($sectionData);
        }
        
        // Si hay lección, crearla
        if ($parsedPath['lesson'] && $sectionId) {
            $this->importLesson($file, $sectionId);
        }
    }
    
    /**
     * Importa una lección
     */
    private function importLesson(array $file, int $sectionId): void
    {
        // Verificar si la lección ya existe
        $existingLesson = $this->lessonRepo->findByFilePath($file['relative_path']);
        
        if ($existingLesson) {
            // Actualizar lección existente
            $this->updateLesson($existingLesson['id'], $file);
            $this->importStats['lessons_updated']++;
        } else {
            // Crear nueva lección
            $lessonData = [
                'name' => $file['parsed_path']['lesson'],
                'section_id' => $sectionId,
                'file_path' => $file['relative_path'],
                'file_size' => $file['size'],
                'order_index' => $this->lessonRepo->getNextOrderIndex($sectionId)
            ];
            
            $lessonId = $this->lessonRepo->create($lessonData);
            
            // Procesar media y generar miniatura
            $this->processLessonMedia($lessonId, $file);
            
            $this->importStats['lessons_created']++;
        }
    }
    
    /**
     * Actualiza una lección existente
     */
    private function updateLesson(int $lessonId, array $file): void
    {
        $updateData = [
            'file_size' => $file['size']
        ];
        
        $this->lessonRepo->update($lessonId, $updateData);
        
        // Procesar media si es necesario
        $this->processLessonMedia($lessonId, $file);
    }
    
    /**
     * Procesa media de una lección (duración, miniatura)
     */
    private function processLessonMedia(int $lessonId, array $file): void
    {
        try {
            // Obtener información del video
            $mediaInfo = $this->mediaProbe->probeVideo($file['full_path']);
            
            if ($mediaInfo['success']) {
                // Actualizar duración
                $this->lessonRepo->updateDuration($lessonId, $mediaInfo['duration']);
                
                // Generar miniatura
                $thumbnailPath = $this->mediaProbe->generateThumbnail($file['full_path']);
                if ($thumbnailPath) {
                    $this->lessonRepo->updateThumbnail($lessonId, $thumbnailPath);
                }
                
                $this->importStats['media_processed']++;
            }
            
        } catch (Exception $e) {
            $this->addLog("⚠️ Error procesando media para lección {$lessonId}: " . $e->getMessage());
        }
    }
    
    /**
     * Marca todos los cursos como eliminados (soft delete)
     */
    private function softDeleteAllCourses(): void
    {
        try {
            $courses = $this->courseRepo->all();
            foreach ($courses as $course) {
                $this->courseRepo->softDelete($course['id']);
            }
            $this->importStats['courses_soft_deleted'] = count($courses);
        } catch (Exception $e) {
            $this->addLog("⚠️ Error al marcar cursos como eliminados: " . $e->getMessage());
        }
    }
    
    /**
     * Resetea las estadísticas de importación
     */
    private function resetStats(): void
    {
        $this->importStats = [
            'files_processed' => 0,
            'files_imported' => 0,
            'topics_created' => 0,
            'instructors_created' => 0,
            'courses_created' => 0,
            'sections_created' => 0,
            'lessons_created' => 0,
            'lessons_updated' => 0,
            'media_processed' => 0,
            'courses_soft_deleted' => 0,
            'errors' => 0
        ];
        
        $this->importLogs = [];
    }
    
    /**
     * Agrega un log a la lista
     */
    private function addLog(string $message): void
    {
        $timestamp = date('H:i:s');
        $this->importLogs[] = "[{$timestamp}] {$message}";
    }
    
    /**
     * Obtiene el resultado de la importación
     */
    private function getImportResult(): array
    {
        return [
            'stats' => $this->importStats,
            'logs' => $this->importLogs,
            'success' => $this->importStats['errors'] === 0,
            'timestamp' => Time::now()
        ];
    }
    
    /**
     * Obtiene estadísticas de la importación
     */
    public function getImportStats(): array
    {
        return $this->importStats;
    }
    
    /**
     * Obtiene logs de la importación
     */
    public function getImportLogs(): array
    {
        return $this->importLogs;
    }
    
    /**
     * Obtiene información del sistema de importación
     */
    public function getSystemInfo(): array
    {
        return [
            'scanner' => $this->scanner->getScanStats(),
            'hasher' => $this->hasher->getCacheStats(),
            'media_probe' => $this->mediaProbe->getFfmpegInfo(),
            'database' => [
                'topics_count' => $this->topicRepo->count(),
                'instructors_count' => $this->instructorRepo->count(),
                'courses_count' => $this->courseRepo->count(),
                'sections_count' => $this->sectionRepo->count(),
                'lessons_count' => $this->lessonRepo->count()
            ]
        ];
    }
}
