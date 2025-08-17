<?php declare(strict_types=1);

class FilesystemScanner
{
    private string $uploadsPath;
    private array $videoExtensions = ['mp4', 'mkv', 'webm', 'mov'];
    private array $scannedFiles = [];
    private array $errors = [];
    
    public function __construct(string $uploadsPath)
    {
        $this->uploadsPath = rtrim($uploadsPath, '/\\');
    }
    
    /**
     * Escanea recursivamente la carpeta de uploads
     */
    public function scan(): array
    {
        $this->scannedFiles = [];
        $this->errors = [];
        
        if (!is_dir($this->uploadsPath)) {
            $this->errors[] = "La carpeta de uploads no existe: {$this->uploadsPath}";
            return [];
        }
        
        try {
            $this->scanDirectory($this->uploadsPath);
        } catch (Exception $e) {
            $this->errors[] = "Error durante el escaneo: " . $e->getMessage();
        }
        
        return [
            'files' => $this->scannedFiles,
            'errors' => $this->errors,
            'total_files' => count($this->scannedFiles),
            'total_errors' => count($this->errors)
        ];
    }
    
    /**
     * Escanea un directorio recursivamente
     */
    private function scanDirectory(string $path, string $relativePath = ''): void
    {
        $items = scandir($path);
        if ($items === false) {
            $this->errors[] = "No se puede leer el directorio: {$path}";
            return;
        }
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $fullPath = $path . DIRECTORY_SEPARATOR . $item;
            $currentRelativePath = $relativePath ? $relativePath . DIRECTORY_SEPARATOR . $item : $item;
            
            if (is_dir($fullPath)) {
                // Es un directorio, escanear recursivamente
                $this->scanDirectory($fullPath, $currentRelativePath);
            } elseif (is_file($fullPath)) {
                // Es un archivo, verificar si es video
                if ($this->isVideoFile($fullPath)) {
                    $this->addScannedFile($fullPath, $currentRelativePath);
                }
            }
        }
    }
    
    /**
     * Verifica si un archivo es de video
     */
    private function isVideoFile(string $filePath): bool
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return in_array($extension, $this->videoExtensions, true);
    }
    
    /**
     * Agrega un archivo escaneado a la lista
     */
    private function addScannedFile(string $fullPath, string $relativePath): void
    {
        try {
            $fileInfo = [
                'full_path' => $fullPath,
                'relative_path' => $relativePath,
                'filename' => basename($fullPath),
                'extension' => strtolower(pathinfo($fullPath, PATHINFO_EXTENSION)),
                'size' => filesize($fullPath),
                'mtime' => filemtime($fullPath),
                'hash' => Str::hashFile($fullPath),
                'parsed_path' => $this->parseFilePath($relativePath)
            ];
            
            $this->scannedFiles[] = $fileInfo;
            
        } catch (Exception $e) {
            $this->errors[] = "Error al procesar archivo {$relativePath}: " . $e->getMessage();
        }
    }
    
    /**
     * Parsea la ruta del archivo para extraer topic, instructor, curso, sección
     */
    private function parseFilePath(string $relativePath): array
    {
        $parts = explode(DIRECTORY_SEPARATOR, $relativePath);
        
        // Estructura esperada: topic/instructor/course/section/lesson.ext
        if (count($parts) >= 5) {
            return [
                'topic' => $parts[0],
                'instructor' => $parts[1],
                'course' => $parts[2],
                'section' => $parts[3],
                'lesson' => pathinfo($parts[4], PATHINFO_FILENAME)
            ];
        } elseif (count($parts) >= 4) {
            return [
                'topic' => $parts[0],
                'instructor' => $parts[1],
                'course' => $parts[2],
                'section' => $parts[3],
                'lesson' => null
            ];
        } elseif (count($parts) >= 3) {
            return [
                'topic' => $parts[0],
                'instructor' => $parts[1],
                'course' => $parts[2],
                'section' => null,
                'lesson' => null
            ];
        } elseif (count($parts) >= 2) {
            return [
                'topic' => $parts[0],
                'instructor' => $parts[1],
                'course' => null,
                'section' => null,
                'lesson' => null
            ];
        } elseif (count($parts) >= 1) {
            return [
                'topic' => $parts[0],
                'instructor' => null,
                'course' => null,
                'section' => null,
                'lesson' => null
            ];
        }
        
        return [
            'topic' => null,
            'instructor' => null,
            'course' => null,
            'section' => null,
            'lesson' => null
        ];
    }
    
    /**
     * Obtiene estadísticas del escaneo
     */
    public function getScanStats(): array
    {
        $totalSize = 0;
        $extensions = [];
        $topics = [];
        $instructors = [];
        $courses = [];
        
        foreach ($this->scannedFiles as $file) {
            $totalSize += $file['size'];
            $extensions[$file['extension']] = ($extensions[$file['extension']] ?? 0) + 1;
            
            if ($file['parsed_path']['topic']) {
                $topics[$file['parsed_path']['topic']] = ($topics[$file['parsed_path']['topic']] ?? 0) + 1;
            }
            
            if ($file['parsed_path']['instructor']) {
                $instructors[$file['parsed_path']['instructor']] = ($instructors[$file['parsed_path']['instructor']] ?? 0) + 1;
            }
            
            if ($file['parsed_path']['course']) {
                $courses[$file['parsed_path']['course']] = ($courses[$file['parsed_path']['course']] ?? 0) + 1;
            }
        }
        
        return [
            'total_files' => count($this->scannedFiles),
            'total_size' => $totalSize,
            'total_size_formatted' => Str::formatBytes($totalSize),
            'extensions' => $extensions,
            'topics_count' => count($topics),
            'instructors_count' => count($instructors),
            'courses_count' => count($courses),
            'errors_count' => count($this->errors)
        ];
    }
    
    /**
     * Obtiene archivos por topic
     */
    public function getFilesByTopic(string $topic): array
    {
        return array_filter($this->scannedFiles, function($file) use ($topic) {
            return $file['parsed_path']['topic'] === $topic;
        });
    }
    
    /**
     * Obtiene archivos por instructor
     */
    public function getFilesByInstructor(string $instructor): array
    {
        return array_filter($this->scannedFiles, function($file) use ($instructor) {
            return $file['parsed_path']['instructor'] === $instructor;
        });
    }
    
    /**
     * Obtiene archivos por curso
     */
    public function getFilesByCourse(string $course): array
    {
        return array_filter($this->scannedFiles, function($file) use ($course) {
            return $file['parsed_path']['course'] === $course;
        });
    }
    
    /**
     * Obtiene archivos modificados desde un timestamp
     */
    public function getModifiedFiles(int $sinceTimestamp): array
    {
        return array_filter($this->scannedFiles, function($file) use ($sinceTimestamp) {
            return $file['mtime'] > $sinceTimestamp;
        });
    }
    
    /**
     * Obtiene archivos por tamaño (mayor que)
     */
    public function getFilesBySize(int $minSize): array
    {
        return array_filter($this->scannedFiles, function($file) use ($minSize) {
            return $file['size'] > $minSize;
        });
    }
}
