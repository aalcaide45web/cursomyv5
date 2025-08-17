<?php declare(strict_types=1);

class JsonResponse
{
    /**
     * Respuesta de éxito
     */
    public static function ok(array $data = [], string $message = 'OK', int $code = 200): void
    {
        self::send([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }
    
    /**
     * Respuesta de error
     */
    public static function error(string $message, int $code = 400, array $data = []): void
    {
        self::send([
            'status' => 'error',
            'message' => $message,
            'data' => $data
        ], $code);
    }
    
    /**
     * Respuesta de validación
     */
    public static function validationError(array $errors, string $message = 'Error de validación'): void
    {
        self::send([
            'status' => 'validation_error',
            'message' => $message,
            'errors' => $errors
        ], 422);
    }
    
    /**
     * Respuesta de recurso no encontrado
     */
    public static function notFound(string $message = 'Recurso no encontrado'): void
    {
        self::error($message, 404);
    }
    
    /**
     * Respuesta de acceso denegado
     */
    public static function forbidden(string $message = 'Acceso denegado'): void
    {
        self::error($message, 403);
    }
    
    /**
     * Respuesta de error interno del servidor
     */
    public static function serverError(string $message = 'Error interno del servidor'): void
    {
        self::error($message, 500);
    }
    
    /**
     * Respuesta de paginación
     */
    public static function paginated(array $data, int $page, int $perPage, int $total): void
    {
        self::ok([
            'items' => $data,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'has_next' => ($page * $perPage) < $total,
                'has_prev' => $page > 1
            ]
        ]);
    }
    
    /**
     * Envía la respuesta JSON
     */
    private static function send(array $data, int $code): void
    {
        // Establecer headers
        header('Content-Type: application/json');
        http_response_code($code);
        
        // Permitir CORS para desarrollo
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Enviar respuesta
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /**
     * Respuesta para operaciones de escaneo
     */
    public static function scanProgress(int $percentage, string $status, array $logs = []): void
    {
        self::ok([
            'percentage' => $percentage,
            'status' => $status,
            'logs' => $logs
        ], 'Progreso del escaneo');
    }
    
    /**
     * Respuesta para estadísticas del dashboard
     */
    public static function dashboardStats(array $stats): void
    {
        self::ok($stats, 'Estadísticas del dashboard');
    }
}
