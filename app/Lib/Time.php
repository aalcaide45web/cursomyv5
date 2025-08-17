<?php declare(strict_types=1);

class Time
{
    /**
     * Obtiene la fecha y hora actual en formato ISO 8601
     */
    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }
    
    /**
     * Convierte segundos a formato legible (HH:MM:SS)
     */
    public static function formatSeconds(float $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = floor($seconds % 60);
        
        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        }
        
        return sprintf('%02d:%02d', $minutes, $secs);
    }
    
    /**
     * Convierte formato HH:MM:SS a segundos
     */
    public static function parseTime(string $time): float
    {
        $parts = explode(':', $time);
        
        if (count($parts) === 3) {
            // HH:MM:SS
            return ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2];
        } elseif (count($parts) === 2) {
            // MM:SS
            return ($parts[0] * 60) + $parts[1];
        }
        
        return 0.0;
    }
    
    /**
     * Formatea segundos en formato humano (ej: "2 horas 30 minutos")
     */
    public static function formatHuman(float $seconds): string
    {
        if ($seconds < 60) {
            return round($seconds) . ' segundos';
        }
        
        $minutes = floor($seconds / 60);
        if ($minutes < 60) {
            return $minutes . ' minuto' . ($minutes !== 1 ? 's' : '');
        }
        
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        if ($remainingMinutes === 0) {
            return $hours . ' hora' . ($hours !== 1 ? 's' : '');
        }
        
        return $hours . ' hora' . ($hours !== 1 ? 's' : '') . ' ' . 
               $remainingMinutes . ' minuto' . ($remainingMinutes !== 1 ? 's' : '');
    }
    
    /**
     * Obtiene el timestamp de modificación de un archivo
     */
    public static function getFileMtime(string $filePath): int
    {
        if (!file_exists($filePath)) {
            return 0;
        }
        
        return filemtime($filePath);
    }
    
    /**
     * Obtiene el tamaño de un archivo en bytes
     */
    public static function getFileSize(string $filePath): int
    {
        if (!file_exists($filePath)) {
            return 0;
        }
        
        return filesize($filePath);
    }
    
    /**
     * Verifica si un archivo ha sido modificado desde un timestamp
     */
    public static function isFileModified(string $filePath, int $sinceTimestamp): bool
    {
        $fileMtime = self::getFileMtime($filePath);
        return $fileMtime > $sinceTimestamp;
    }
    
    /**
     * Formatea una fecha para mostrar en la interfaz
     */
    public static function formatDate(string $date, string $format = 'd/m/Y H:i'): string
    {
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return $date;
        }
        
        return date($format, $timestamp);
    }
    
    /**
     * Calcula la diferencia de tiempo entre dos fechas
     */
    public static function timeAgo(string $date): string
    {
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return 'fecha inválida';
        }
        
        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return 'hace un momento';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return 'hace ' . $minutes . ' minuto' . ($minutes !== 1 ? 's' : '');
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return 'hace ' . $hours . ' hora' . ($hours !== 1 ? 's' : '');
        } elseif ($diff < 2592000) {
            $days = floor($diff / 86400);
            return 'hace ' . $days . ' día' . ($days !== 1 ? 's' : '');
        } else {
            return self::formatDate($date, 'd/m/Y');
        }
    }
}
