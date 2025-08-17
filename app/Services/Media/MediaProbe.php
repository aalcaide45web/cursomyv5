<?php declare(strict_types=1);

class MediaProbe
{
    private string $ffmpegPath;
    private string $ffprobePath;
    private string $cachePath;
    private bool $useFfmpeg;
    
    public function __construct(string $cachePath, bool $useFfmpeg = true)
    {
        $this->cachePath = rtrim($cachePath, '/\\');
        $this->useFfmpeg = $useFfmpeg;
        
        // Buscar ffmpeg y ffprobe en el sistema
        $this->ffmpegPath = $this->findFfmpeg();
        $this->ffprobePath = $this->findFfprobe();
    }
    
    /**
     * Busca ffmpeg en el sistema
     */
    private function findFfmpeg(): string
    {
        if (!$this->useFfmpeg) {
            return '';
        }
        
        // Buscar en PATH
        $paths = [
            'ffmpeg',
            'C:\ffmpeg\bin\ffmpeg.exe',
            '/usr/bin/ffmpeg',
            '/usr/local/bin/ffmpeg'
        ];
        
        foreach ($paths as $path) {
            if ($this->commandExists($path)) {
                return $path;
            }
        }
        
        return '';
    }
    
    /**
     * Busca ffprobe en el sistema
     */
    private function findFfprobe(): string
    {
        if (!$this->useFfmpeg) {
            return '';
        }
        
        // Buscar en PATH
        $paths = [
            'ffprobe',
            'C:\ffmpeg\bin\ffprobe.exe',
            '/usr/bin/ffprobe',
            '/usr/local/bin/ffprobe'
        ];
        
        foreach ($paths as $path) {
            if ($this->commandExists($path)) {
                return $path;
            }
        }
        
        return '';
    }
    
    /**
     * Verifica si un comando existe
     */
    private function commandExists(string $command): bool
    {
        if (strpos($command, DIRECTORY_SEPARATOR) !== false) {
            // Ruta absoluta
            return file_exists($command);
        }
        
        // Comando en PATH
        $output = [];
        $returnCode = 0;
        exec("where {$command} 2>nul", $output, $returnCode);
        
        if ($returnCode === 0 && !empty($output)) {
            return true;
        }
        
        // Fallback para Unix/Linux
        exec("which {$command} 2>/dev/null", $output, $returnCode);
        return $returnCode === 0 && !empty($output);
    }
    
    /**
     * Obtiene información de un archivo de video
     */
    public function probeVideo(string $filePath): array
    {
        if (!$this->useFfmpeg || empty($this->ffprobePath)) {
            return $this->fallbackProbe($filePath);
        }
        
        try {
            $command = sprintf(
                '%s -v quiet -print_format json -show_format -show_streams "%s"',
                $this->ffprobePath,
                $filePath
            );
            
            $output = shell_exec($command);
            if ($output === null) {
                throw new Exception('No se pudo ejecutar ffprobe');
            }
            
            $data = json_decode($output, true);
            if ($data === null) {
                throw new Exception('Error al decodificar JSON de ffprobe');
            }
            
            return $this->parseFfprobeOutput($data);
            
        } catch (Exception $e) {
            error_log("Error en ffprobe para {$filePath}: " . $e->getMessage());
            return $this->fallbackProbe($filePath);
        }
    }
    
    /**
     * Parsea la salida de ffprobe
     */
    private function parseFfprobeOutput(array $data): array
    {
        $format = $data['format'] ?? [];
        $streams = $data['streams'] ?? [];
        
        $videoStream = null;
        foreach ($streams as $stream) {
            if (($stream['codec_type'] ?? '') === 'video') {
                $videoStream = $stream;
                break;
            }
        }
        
        $duration = (float) ($format['duration'] ?? 0);
        $bitrate = (int) ($format['bit_rate'] ?? 0);
        $size = (int) ($format['size'] ?? 0);
        
        $width = (int) ($videoStream['width'] ?? 0);
        $height = (int) ($videoStream['height'] ?? 0);
        $codec = $videoStream['codec_name'] ?? 'unknown';
        
        return [
            'duration' => $duration,
            'duration_formatted' => Time::formatSeconds($duration),
            'bitrate' => $bitrate,
            'bitrate_formatted' => $this->formatBitrate($bitrate),
            'size' => $size,
            'size_formatted' => Str::formatBytes($size),
            'width' => $width,
            'height' => $height,
            'resolution' => $width > 0 && $height > 0 ? "{$width}x{$height}" : 'unknown',
            'codec' => $codec,
            'success' => true
        ];
    }
    
    /**
     * Fallback cuando ffmpeg no está disponible
     */
    private function fallbackProbe(string $filePath): array
    {
        $size = filesize($filePath);
        
        return [
            'duration' => 0,
            'duration_formatted' => '00:00',
            'bitrate' => 0,
            'bitrate_formatted' => '0 bps',
            'size' => $size,
            'size_formatted' => Str::formatBytes($size),
            'width' => 0,
            'height' => 0,
            'resolution' => 'unknown',
            'codec' => 'unknown',
            'success' => false,
            'note' => 'ffmpeg no disponible, información limitada'
        ];
    }
    
    /**
     * Genera una miniatura para un archivo de video
     */
    public function generateThumbnail(string $filePath, float $timeOffset = 10.0): ?string
    {
        if (!$this->useFfmpeg || empty($this->ffmpegPath)) {
            return null;
        }
        
        try {
            // Crear directorio de miniaturas si no existe
            $thumbsDir = $this->cachePath . DIRECTORY_SEPARATOR . 'thumbs';
            if (!is_dir($thumbsDir)) {
                mkdir($thumbsDir, 0755, true);
            }
            
            // Generar nombre único para la miniatura
            $fileHash = Str::hashFile($filePath);
            $thumbPath = $thumbsDir . DIRECTORY_SEPARATOR . $fileHash . '.jpg';
            
            // Si ya existe, retornar la ruta
            if (file_exists($thumbPath)) {
                return $thumbPath;
            }
            
            // Generar miniatura con ffmpeg
            $command = sprintf(
                '%s -i "%s" -ss %.2f -vframes 1 -vf "scale=320:180:force_original_aspect_ratio=decrease" -y "%s" 2>nul',
                $this->ffmpegPath,
                $filePath,
                $timeOffset,
                $thumbPath
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($thumbPath)) {
                return $thumbPath;
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error al generar miniatura para {$filePath}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Genera múltiples miniaturas para un video
     */
    public function generateMultipleThumbnails(string $filePath, int $count = 3): array
    {
        if (!$this->useFfmpeg || empty($this->ffmpegPath)) {
            return [];
        }
        
        try {
            // Obtener duración del video
            $info = $this->probeVideo($filePath);
            $duration = $info['duration'];
            
            if ($duration <= 0) {
                return [];
            }
            
            $thumbsDir = $this->cachePath . DIRECTORY_SEPARATOR . 'thumbs';
            if (!is_dir($thumbsDir)) {
                mkdir($thumbsDir, 0755, true);
            }
            
            $fileHash = Str::hashFile($filePath);
            $thumbnails = [];
            
            // Generar miniaturas en diferentes puntos del video
            for ($i = 0; $i < $count; $i++) {
                $timeOffset = ($duration / ($count + 1)) * ($i + 1);
                $thumbPath = $thumbsDir . DIRECTORY_SEPARATOR . $fileHash . "_{$i}.jpg";
                
                $command = sprintf(
                    '%s -i "%s" -ss %.2f -vframes 1 -vf "scale=320:180:force_original_aspect_ratio=decrease" -y "%s" 2>nul',
                    $this->ffmpegPath,
                    $filePath,
                    $timeOffset,
                    $thumbPath
                );
                
                exec($command, $output, $returnCode);
                
                if ($returnCode === 0 && file_exists($thumbPath)) {
                    $thumbnails[] = [
                        'path' => $thumbPath,
                        'time_offset' => $timeOffset,
                        'time_formatted' => Time::formatSeconds($timeOffset)
                    ];
                }
            }
            
            return $thumbnails;
            
        } catch (Exception $e) {
            error_log("Error al generar múltiples miniaturas para {$filePath}: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Formatea el bitrate en formato legible
     */
    private function formatBitrate(int $bitrate): string
    {
        if ($bitrate === 0) {
            return '0 bps';
        }
        
        $units = ['bps', 'Kbps', 'Mbps', 'Gbps'];
        $unit = 0;
        $rate = $bitrate;
        
        while ($rate >= 1000 && $unit < count($units) - 1) {
            $rate /= 1000;
            $unit++;
        }
        
        return round($rate, 1) . ' ' . $units[$unit];
    }
    
    /**
     * Verifica si ffmpeg está disponible
     */
    public function isFfmpegAvailable(): bool
    {
        return $this->useFfmpeg && !empty($this->ffmpegPath) && !empty($this->ffprobePath);
    }
    
    /**
     * Obtiene información del sistema ffmpeg
     */
    public function getFfmpegInfo(): array
    {
        return [
            'use_ffmpeg' => $this->useFfmpeg,
            'ffmpeg_path' => $this->ffmpegPath,
            'ffprobe_path' => $this->ffprobePath,
            'available' => $this->isFfmpegAvailable(),
            'version' => $this->getFfmpegVersion()
        ];
    }
    
    /**
     * Obtiene la versión de ffmpeg
     */
    private function getFfmpegVersion(): string
    {
        if (empty($this->ffmpegPath)) {
            return 'No disponible';
        }
        
        try {
            $output = shell_exec("{$this->ffmpegPath} -version 2>&1");
            if ($output) {
                $lines = explode("\n", $output);
                return trim($lines[0] ?? 'Versión desconocida');
            }
        } catch (Exception $e) {
            // Ignorar errores
        }
        
        return 'Versión desconocida';
    }
}
