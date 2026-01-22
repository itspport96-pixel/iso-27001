<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];
    private array $middlewares = [];

    public function get(string $uri, $action): self
    {
        return $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, $action): self
    {
        return $this->addRoute('POST', $uri, $action);
    }

    public function put(string $uri, $action): self
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    public function delete(string $uri, $action): self
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    private function addRoute(string $method, string $uri, $action): self
    {
        if (is_array($action) && count($action) === 2) {
            $action = fn($req) => (new $action[0])->{$action[1]}($req);
        }
        $this->routes[$method][$uri] = $action;
        return $this;
    }

    public function middleware(string $name, callable $handler): self
    {
        $this->middlewares[$name] = $handler;
        return $this;
    }

    public function dispatch(Request $req): mixed
    {
        $method = $req->method();
        $uri    = $req->uri();

        if (!isset($this->routes[$method][$uri])) {
            (new Response())->status(404)->text('Not Found');
        }

        $action = $this->routes[$method][$uri];

        $next = function ($req) use ($action) {
            return $action($req);
        };

        foreach ($this->middlewares as $mw) {
            $next = function ($req) use ($mw, $next) {
                return $mw($req, $next);
            };
        }

        return $next($req);
    }
}
