<?php declare(strict_types=1);

class ScanController
{
    private Importer $importer;
    private FilesystemScanner $scanner;
    private Hasher $hasher;
    private MediaProbe $mediaProbe;
    
    public function __construct()
    {
        $config = require __DIR__ . '/../../config/env.example.php';
        
        $this->scanner = new FilesystemScanner($config['UPLOADS_PATH']);
        $this->hasher = new Hasher($config['CACHE_PATH']);
        $this->mediaProbe = new MediaProbe($config['CACHE_PATH'], $config['USE_FFMPEG']);
        $this->importer = new Importer($this->scanner, $this->hasher, $this->mediaProbe);
    }
    
    /**
     * Escaneo incremental - solo archivos modificados
     */
    public function incremental(): void
    {
        try {
            $result = $this->importer->importIncremental();
            
            if ($result['success']) {
                JsonResponse::ok([
                    'type' => 'incremental',
                    'result' => $result
                ], 'Escaneo incremental completado exitosamente');
            } else {
                JsonResponse::error('Error durante el escaneo incremental', 500, [
                    'type' => 'incremental',
                    'result' => $result
                ]);
            }
            
        } catch (Exception $e) {
            JsonResponse::serverError('Error en escaneo incremental: ' . $e->getMessage());
        }
    }
    
    /**
     * Reconstrucción completa de la base de datos
     */
    public function rebuild(): void
    {
        try {
            $result = $this->importer->importRebuild();
            
            if ($result['success']) {
                JsonResponse::ok([
                    'type' => 'rebuild',
                    'result' => $result
                ], 'Reconstrucción completa completada exitosamente');
            } else {
                JsonResponse::error('Error durante la reconstrucción', 500, [
                    'type' => 'rebuild',
                    'result' => $result
                ]);
            }
            
        } catch (Exception $e) {
            JsonResponse::serverError('Error en reconstrucción: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene información del sistema de escaneo
     */
    public function systemInfo(): void
    {
        try {
            $info = $this->importer->getSystemInfo();
            
            JsonResponse::ok($info, 'Información del sistema obtenida');
            
        } catch (Exception $e) {
            JsonResponse::serverError('Error al obtener información del sistema: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene estadísticas del escaneo
     */
    public function scanStats(): void
    {
        try {
            $stats = $this->scanner->getScanStats();
            
            JsonResponse::ok($stats, 'Estadísticas de escaneo obtenidas');
            
        } catch (Exception $e) {
            JsonResponse::serverError('Error al obtener estadísticas: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene estadísticas del cache de hashes
     */
    public function hashStats(): void
    {
        try {
            $stats = $this->hasher->getCacheStats();
            
            JsonResponse::ok($stats, 'Estadísticas de hash obtenidas');
            
        } catch (Exception $e) {
            JsonResponse::serverError('Error al obtener estadísticas de hash: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene información de ffmpeg
     */
    public function ffmpegInfo(): void
    {
        try {
            $info = $this->mediaProbe->getFfmpegInfo();
            
            JsonResponse::ok($info, 'Información de ffmpeg obtenida');
            
        } catch (Exception $e) {
            JsonResponse::serverError('Error al obtener información de ffmpeg: ' . $e->getMessage());
        }
    }
    
    /**
     * Limpia hashes obsoletos
     */
    public function cleanHashes(): void
    {
        try {
            $config = require __DIR__ . '/../../config/env.example.php';
            $cleaned = $this->hasher->cleanStaleHashes($config['UPLOADS_PATH']);
            
            JsonResponse::ok([
                'cleaned_count' => $cleaned
            ], "Se limpiaron {$cleaned} hashes obsoletos");
            
        } catch (Exception $e) {
            JsonResponse::serverError('Error al limpiar hashes: ' . $e->getMessage());
        }
    }
    
    /**
     * Escanea archivos sin importar (solo para diagnóstico)
     */
    public function scanOnly(): void
    {
        try {
            $result = $this->scanner->scan();
            
            JsonResponse::ok([
                'scan_result' => $result,
                'note' => 'Este es solo un escaneo de diagnóstico, no se importó nada'
            ], 'Escaneo de diagnóstico completado');
            
        } catch (Exception $e) {
            JsonResponse::serverError('Error en escaneo de diagnóstico: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene archivos por topic
     */
    public function filesByTopic(string $topic): void
    {
        try {
            $files = $this->scanner->getFilesByTopic($topic);
            
            JsonResponse::ok([
                'topic' => $topic,
                'files' => $files,
                'count' => count($files)
            ], "Archivos encontrados para el topic: {$topic}");
            
        } catch (Exception $e) {
            JsonResponse::serverError('Error al obtener archivos por topic: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene archivos por instructor
     */
    public function filesByInstructor(string $instructor): void
    {
        try {
            $files = $this->scanner->getFilesByInstructor($instructor);
            
            JsonResponse::ok([
                'instructor' => $instructor,
                'files' => $files,
                'count' => count($files)
            ], "Archivos encontrados para el instructor: {$instructor}");
            
        } catch (Exception $e) {
            JsonResponse::serverError('Error al obtener archivos por instructor: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene archivos por curso
     */
    public function filesByCourse(string $course): void
    {
        try {
            $files = $this->scanner->getFilesByCourse($course);
            
            JsonResponse::ok([
                'course' => $course,
                'files' => $files,
                'count' => count($files)
            ], "Archivos encontrados para el curso: {$course}");
            
        } catch (Exception $e) {
            JsonResponse::serverError('Error al obtener archivos por curso: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene archivos modificados desde un timestamp
     */
    public function modifiedFiles(int $sinceTimestamp): void
    {
        try {
            $files = $this->scanner->getModifiedFiles($sinceTimestamp);
            
            JsonResponse::ok([
                'since_timestamp' => $sinceTimestamp,
                'since_date' => date('Y-m-d H:i:s', $sinceTimestamp),
                'files' => $files,
                'count' => count($files)
            ], 'Archivos modificados obtenidos');
            
        } catch (Exception $e) {
            JsonResponse::serverError('Error al obtener archivos modificados: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene archivos por tamaño mínimo
     */
    public function filesBySize(int $minSize): void
    {
        try {
            $files = $this->scanner->getFilesBySize($minSize);
            
            JsonResponse::ok([
                'min_size' => $minSize,
                'min_size_formatted' => Str::formatBytes($minSize),
                'files' => $files,
                'count' => count($files)
            ], 'Archivos por tamaño obtenidos');
            
        } catch (Exception $e) {
            JsonResponse::serverError('Error al obtener archivos por tamaño: ' . $e->getMessage());
        }
    }
    
    /**
     * Encuentra archivos duplicados
     */
    public function findDuplicates(): void
    {
        try {
            $scanResult = $this->scanner->scan();
            $duplicates = $this->hasher->findDuplicates($scanResult['files']);
            
            JsonResponse::ok([
                'duplicates' => $duplicates,
                'total_duplicates' => count($duplicates),
                'total_files' => $scanResult['total_files']
            ], 'Búsqueda de duplicados completada');
            
        } catch (Exception $e) {
            JsonResponse::serverError('Error al buscar duplicados: ' . $e->getMessage());
        }
    }
}
