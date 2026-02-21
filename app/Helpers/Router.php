<?php
/**
 * Router simple para SAV12
 * Parsea la URL y ejecuta el controller + método correspondiente
 */

class Router {
    private array $routes = [];

    public function get(string $path, string $controller, string $method): void {
        $this->routes['GET'][$path] = [$controller, $method];
    }

    public function post(string $path, string $controller, string $method): void {
        $this->routes['POST'][$path] = [$controller, $method];
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        // Buscar ruta exacta
        if (isset($this->routes[$method][$uri])) {
            $this->call($this->routes[$method][$uri]);
            return;
        }

        // Buscar rutas con parámetros {id}
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';
            if (preg_match($pattern, $uri, $matches)) {
                // Extraer parámetros con nombre
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->call($handler, $params);
                return;
            }
        }

        // 404
        http_response_code(404);
        echo '<h1>404 - Página no encontrada</h1>';
    }

    private function call(array $handler, array $params = []): void {
        [$controllerClass, $method] = $handler;
        $controllerFile = APP_PATH . '/Controllers/' . $controllerClass . '.php';

        if (!file_exists($controllerFile)) {
            http_response_code(500);
            echo "Controller $controllerClass no encontrado";
            return;
        }

        require_once $controllerFile;
        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            http_response_code(500);
            echo "Método $method no encontrado en $controllerClass";
            return;
        }

        call_user_func_array([$controller, $method], $params);
    }
}
