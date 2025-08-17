<?php declare(strict_types=1);

class Router
{
    private array $routes = [];
    private array $middlewares = [];
    
    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }
    
    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }
    
    public function patch(string $path, callable $handler): void
    {
        $this->routes['PATCH'][$path] = $handler;
    }
    
    public function delete(string $path, callable $handler): void
    {
        $this->routes['DELETE'][$path] = $handler;
    }
    
    public function addMiddleware(callable $middleware): void
    {
        $this->middlewares[] = $middleware;
    }
    
    public function dispatch(string $method, string $uri): void
    {
        // Ejecutar middlewares
        foreach ($this->middlewares as $middleware) {
            $middleware();
        }
        
        // Limpiar URI
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }
        
        // Buscar ruta
        if (isset($this->routes[$method][$uri])) {
            $handler = $this->routes[$method][$uri];
            call_user_func($handler);
            return;
        }
        
        // Ruta no encontrada
        http_response_code(404);
        echo "404 - Página no encontrada";
    }
    
    public function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->dispatch($method, $uri);
    }
}
