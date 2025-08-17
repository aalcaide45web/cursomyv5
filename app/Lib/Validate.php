<?php declare(strict_types=1);

class Validate
{
    /**
     * Valida que un valor sea un entero válido
     */
    public static function int($value, int $min = null, int $max = null): bool
    {
        if (!is_numeric($value)) {
            return false;
        }
        
        $int = (int) $value;
        
        if ($min !== null && $int < $min) {
            return false;
        }
        
        if ($max !== null && $int > $max) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Valida que un valor sea un float válido
     */
    public static function float($value, float $min = null, float $max = null): bool
    {
        if (!is_numeric($value)) {
            return false;
        }
        
        $float = (float) $value;
        
        if ($min !== null && $float < $min) {
            return false;
        }
        
        if ($max !== null && $float > $max) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Valida que un valor sea un string válido
     */
    public static function string($value, int $minLength = null, int $maxLength = null): bool
    {
        if (!is_string($value)) {
            return false;
        }
        
        $length = strlen($value);
        
        if ($minLength !== null && $length < $minLength) {
            return false;
        }
        
        if ($maxLength !== null && $length > $maxLength) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Valida que un valor sea un email válido
     */
    public static function email(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Valida que un valor esté en una lista de valores permitidos
     */
    public static function in($value, array $allowedValues): bool
    {
        return in_array($value, $allowedValues, true);
    }
    
    /**
     * Valida que un valor sea una URL válida
     */
    public static function url(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Valida que un valor sea un slug válido
     */
    public static function slug(string $value): bool
    {
        return preg_match('/^[a-z0-9-]+$/', $value) === 1;
    }
    
    /**
     * Valida que un valor sea un timestamp válido
     */
    public static function timestamp($value): bool
    {
        if (!is_numeric($value)) {
            return false;
        }
        
        $timestamp = (int) $value;
        return $timestamp > 0 && $timestamp <= time() + 86400; // +1 día para futuros
    }
    
    /**
     * Valida que un valor sea una fecha válida
     */
    public static function date(string $value, string $format = 'Y-m-d H:i:s'): bool
    {
        $date = DateTime::createFromFormat($format, $value);
        return $date !== false;
    }
    
    /**
     * Valida que un archivo exista y sea legible
     */
    public static function fileExists(string $filePath): bool
    {
        return file_exists($filePath) && is_readable($filePath);
    }
    
    /**
     * Valida que un archivo sea de un tipo específico
     */
    public static function fileType(string $filePath, array $allowedExtensions): bool
    {
        if (!self::fileExists($filePath)) {
            return false;
        }
        
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return in_array($extension, $allowedExtensions, true);
    }
    
    /**
     * Valida que un archivo no exceda un tamaño máximo
     */
    public static function fileSize(string $filePath, int $maxSizeBytes): bool
    {
        if (!self::fileExists($filePath)) {
            return false;
        }
        
        $fileSize = filesize($filePath);
        return $fileSize !== false && $fileSize <= $maxSizeBytes;
    }
    
    /**
     * Valida múltiples campos a la vez
     */
    public static function multiple(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            if (!isset($data[$field])) {
                if (in_array('required', $fieldRules)) {
                    $errors[$field][] = "El campo '$field' es requerido";
                }
                continue;
            }
            
            $value = $data[$field];
            
            foreach ($fieldRules as $rule) {
                if (is_string($rule)) {
                    $ruleName = $rule;
                    $ruleParams = [];
                } else {
                    $ruleName = $rule[0];
                    $ruleParams = array_slice($rule, 1);
                }
                
                $method = 'validate' . ucfirst($ruleName);
                if (method_exists(self::class, $method)) {
                    if (!self::$method($value, ...$ruleParams)) {
                        $errors[$field][] = "El campo '$field' no cumple con la regla '$ruleName'";
                    }
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Valida que un valor sea requerido
     */
    public static function required($value): bool
    {
        if (is_string($value)) {
            return trim($value) !== '';
        }
        
        return $value !== null && $value !== '';
    }
    
    /**
     * Valida que un valor sea un array
     */
    public static function array($value): bool
    {
        return is_array($value);
    }
    
    /**
     * Valida que un array tenga un número mínimo de elementos
     */
    public static function arrayMin($value, int $min): bool
    {
        if (!is_array($value)) {
            return false;
        }
        
        return count($value) >= $min;
    }
    
    /**
     * Valida que un array tenga un número máximo de elementos
     */
    public static function arrayMax($value, int $max): bool
    {
        if (!is_array($value)) {
            return false;
        }
        
        return count($value) <= $max;
    }
}
