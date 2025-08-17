<?php declare(strict_types=1);

class Hasher
{
    private string $cachePath;
    private array $hashCache = [];
    
    public function __construct(string $cachePath)
    {
        $this->cachePath = rtrim($cachePath, '/\\');
        $this->loadHashCache();
    }
    
    /**
     * Genera un hash para un archivo
     */
    public function hashFile(string $filePath): string
    {
        if (!file_exists($filePath)) {
            return '';
        }
        
        try {
            // Usar xxh3 para velocidad (más rápido que md5/sha1)
            return hash('xxh3', file_get_contents($filePath));
        } catch (Exception $e) {
            // Fallback a hash del nombre + tamaño + mtime
            return $this->fallbackHash($filePath);
        }
    }
    
    /**
     * Hash de fallback basado en metadatos del archivo
     */
    private function fallbackHash(string $filePath): string
    {
        $filename = basename($filePath);
        $size = filesize($filePath);
        $mtime = filemtime($filePath);
        
        return hash('xxh3', $filename . $size . $mtime);
    }
    
    /**
     * Verifica si un archivo ha cambiado
     */
    public function hasFileChanged(string $filePath): bool
    {
        $currentHash = $this->hashFile($filePath);
        $cachedHash = $this->getCachedHash($filePath);
        
        return $currentHash !== $cachedHash;
    }
    
    /**
     * Obtiene el hash cacheado de un archivo
     */
    public function getCachedHash(string $filePath): ?string
    {
        $relativePath = $this->getRelativePath($filePath);
        return $this->hashCache[$relativePath] ?? null;
    }
    
    /**
     * Actualiza el hash cacheado de un archivo
     */
    public function updateCachedHash(string $filePath, string $hash): void
    {
        $relativePath = $this->getRelativePath($filePath);
        $this->hashCache[$relativePath] = $hash;
        $this->saveHashCache();
    }
    
    /**
     * Obtiene la ruta relativa desde uploads
     */
    private function getRelativePath(string $filePath): string
    {
        // Buscar la posición de 'uploads' en la ruta
        $uploadsPos = strpos($filePath, 'uploads');
        if ($uploadsPos === false) {
            return $filePath;
        }
        
        // Extraer la parte después de 'uploads'
        $relativePath = substr($filePath, $uploadsPos + 7); // 7 = strlen('uploads')
        return ltrim($relativePath, '/\\');
    }
    
    /**
     * Carga el cache de hashes desde archivo
     */
    private function loadHashCache(): void
    {
        $cacheFile = $this->cachePath . DIRECTORY_SEPARATOR . 'hash_cache.json';
        
        if (file_exists($cacheFile)) {
            try {
                $content = file_get_contents($cacheFile);
                if ($content !== false) {
                    $this->hashCache = json_decode($content, true) ?: [];
                }
            } catch (Exception $e) {
                $this->hashCache = [];
            }
        }
    }
    
    /**
     * Guarda el cache de hashes en archivo
     */
    private function saveHashCache(): void
    {
        $cacheFile = $this->cachePath . DIRECTORY_SEPARATOR . 'hash_cache.json';
        
        try {
            // Crear directorio si no existe
            $cacheDir = dirname($cacheFile);
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            
            file_put_contents($cacheFile, json_encode($this->hashCache, JSON_PRETTY_PRINT));
        } catch (Exception $e) {
            // Log error pero no fallar
            error_log("Error al guardar hash cache: " . $e->getMessage());
        }
    }
    
    /**
     * Limpia hashes de archivos que ya no existen
     */
    public function cleanStaleHashes(string $uploadsPath): int
    {
        $cleaned = 0;
        $uploadsPath = rtrim($uploadsPath, '/\\');
        
        foreach ($this->hashCache as $relativePath => $hash) {
            $fullPath = $uploadsPath . DIRECTORY_SEPARATOR . $relativePath;
            
            if (!file_exists($fullPath)) {
                unset($this->hashCache[$relativePath]);
                $cleaned++;
            }
        }
        
        if ($cleaned > 0) {
            $this->saveHashCache();
        }
        
        return $cleaned;
    }
    
    /**
     * Obtiene estadísticas del cache de hashes
     */
    public function getCacheStats(): array
    {
        return [
            'total_cached_files' => count($this->hashCache),
            'cache_file_size' => $this->getCacheFileSize(),
            'cache_file_path' => $this->cachePath . DIRECTORY_SEPARATOR . 'hash_cache.json'
        ];
    }
    
    /**
     * Obtiene el tamaño del archivo de cache
     */
    private function getCacheFileSize(): int
    {
        $cacheFile = $this->cachePath . DIRECTORY_SEPARATOR . 'hash_cache.json';
        
        if (file_exists($cacheFile)) {
            return filesize($cacheFile);
        }
        
        return 0;
    }
    
    /**
     * Compara dos archivos para ver si son iguales
     */
    public function compareFiles(string $file1, string $file2): bool
    {
        if (!file_exists($file1) || !file_exists($file2)) {
            return false;
        }
        
        $hash1 = $this->hashFile($file1);
        $hash2 = $this->hashFile($file2);
        
        return $hash1 === $hash2;
    }
    
    /**
     * Obtiene archivos duplicados en un directorio
     */
    public function findDuplicates(array $files): array
    {
        $hashGroups = [];
        $duplicates = [];
        
        foreach ($files as $file) {
            $hash = $this->hashFile($file['full_path']);
            $hashGroups[$hash][] = $file;
        }
        
        foreach ($hashGroups as $hash => $fileGroup) {
            if (count($fileGroup) > 1) {
                $duplicates[] = [
                    'hash' => $hash,
                    'files' => $fileGroup,
                    'count' => count($fileGroup)
                ];
            }
        }
        
        return $duplicates;
    }
}
