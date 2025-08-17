<?php declare(strict_types=1);

class Str
{
    /**
     * Convierte un string a slug (URL-friendly)
     */
    public static function slugify(string $text): string
    {
        // Normalizar tildes y caracteres especiales
        $text = self::normalizeAccents($text);
        
        // Convertir a minúsculas
        $text = strtolower($text);
        
        // Reemplazar caracteres no alfanuméricos con guiones
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        
        // Reemplazar espacios múltiples con un solo guión
        $text = preg_replace('/[\s-]+/', '-', $text);
        
        // Eliminar guiones al inicio y final
        $text = trim($text, '-');
        
        return $text;
    }
    
    /**
     * Normaliza acentos y caracteres especiales del español
     */
    public static function normalizeAccents(string $text): string
    {
        $replacements = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'ñ' => 'n', 'Ñ' => 'N',
            'ü' => 'u', 'Ü' => 'U',
            'ç' => 'c', 'Ç' => 'C'
        ];
        
        return strtr($text, $replacements);
    }
    
    /**
     * Trunca un texto a una longitud específica
     */
    public static function truncate(string $text, int $length, string $suffix = '...'): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length - strlen($suffix)) . $suffix;
    }
    
    /**
     * Limpia y sanitiza un string
     */
    public static function clean(string $text): string
    {
        // Eliminar espacios múltiples
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Eliminar espacios al inicio y final
        $text = trim($text);
        
        // Convertir caracteres especiales HTML
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        
        return $text;
    }
    
    /**
     * Genera un hash simple para archivos
     */
    public static function hashFile(string $filePath): string
    {
        if (!file_exists($filePath)) {
            return '';
        }
        
        return hash('xxh3', file_get_contents($filePath));
    }
    
    /**
     * Formatea bytes en formato legible
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
