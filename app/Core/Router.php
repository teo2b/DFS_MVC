<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<string, callable>> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$this->normalizePath($path)] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$this->normalizePath($path)] = $handler;
    }

    public function dispatch(string $method, string $uri): bool
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $path = is_string($path) ? $this->normalizePath($path) : '/';

        foreach ($this->routes[strtoupper($method)] ?? [] as $route => $handler) {
            $parameters = $this->matchPath($route, $path);

            if ($parameters !== null) {
                call_user_func_array($handler, $parameters);
                return true;
            }
        }

        return false;
    }

    private function normalizePath(string $path): string
    {
        $normalizedPath = '/' . trim($path, '/');

        return $normalizedPath === '/' ? '/' : $normalizedPath;
    }

    /**
     * @return list<string>|null
     */
    private function matchPath(string $route, string $path): ?array
    {
        $routeSegments = explode('/', trim($route, '/'));
        $pathSegments = explode('/', trim($path, '/'));

        if (count($routeSegments) !== count($pathSegments)) {
            return null;
        }

        $parameters = [];
        foreach ($routeSegments as $index => $routeSegment) {
            if (preg_match('/^\{[a-zA-Z_][a-zA-Z0-9_]*\}$/', $routeSegment) === 1) {
                $parameters[] = rawurldecode($pathSegments[$index]);
                continue;
            }

            if ($routeSegment !== $pathSegments[$index]) {
                return null;
            }
        }

        return $parameters;
    }
}
