<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middlewares = [];

    public function get(string $uri, callable|array $action, array $middlewares = []): void
    {
        $this->addRoute('GET', $uri, $action, $middlewares);
    }

    public function post(string $uri, callable|array $action, array $middlewares = []): void
    {
        $this->addRoute('POST', $uri, $action, $middlewares);
    }

    private function addRoute(string $method, string $uri, callable|array $action, array $middlewares): void
    {
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'action' => $action,
            'middlewares' => $middlewares
        ];
    }

    public function dispatch(Request $request, Response $response): void
    {
        $uri = $request->uri();
        $method = $request->method();

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchUri($route['uri'], $uri, $params)) {
                $this->executeMiddlewares($route['middlewares'], $request, $response);
                $this->executeAction($route['action'], $params, $request, $response);
                return;
            }
        }

        $response->notFound();
    }

    private function matchUri(string $pattern, string $uri, &$params = []): bool
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches);
            $params = $matches;
            return true;
        }

        return false;
    }

    private function executeMiddlewares(array $middlewares, Request $request, Response $response): void
    {
        foreach ($middlewares as $middleware) {
            $instance = new $middleware();
            $instance->handle($request, $response);
        }
    }

    private function executeAction(callable|array $action, array $params, Request $request, Response $response): void
    {
        if (is_array($action)) {
            [$controller, $method] = $action;
            $instance = new $controller();
            call_user_func_array([$instance, $method], array_merge([$request, $response], $params));
        } else {
            call_user_func_array($action, array_merge([$request, $response], $params));
        }
    }
}
