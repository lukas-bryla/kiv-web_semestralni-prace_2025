<?php
namespace App\Core;

class Router {
    private $routes = [];

    public function add($method, $path, $controllerAction) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'action' => $controllerAction
        ];
    }

    public function dispatch($twig) {
        $url = $_GET['url'] ?? '';
        $url = trim($url, '/');
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes as $route) {
            $pattern = '@^' . preg_replace('/\{(\d+)\}/', '(\d+)', $route['path']) . '$@';
            if (preg_match($pattern, $url, $matches) && $route['method'] === $method) {
                array_shift($matches);
                $action = explode('@', $route['action']);
                $controller = "App\\Controller\\" . $action[0];
                $methodName = $action[1];
                call_user_func_array([new $controller(), $methodName], array_merge([$twig], $matches));
                return;
            }
        }

        http_response_code(404);
        echo $twig->render('errors/404.twig');
    }
}
