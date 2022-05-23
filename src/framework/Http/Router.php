<?php

namespace Skeletal\Http;

use Skeletal\Support\Reflector;

class Router
{
    protected static $routes;

    protected static $request;

    public static function get(string $route, array|string $controller)
    {
        if (is_string($controller)) {
            $controller = explode('@', $controller);
        }

        if (!class_exists($abstract = $controller[0]) || !method_exists($abstract, $method = $controller[1])) {
            if (empty($method)) {
                throw new \ErrorException("class $abstract not found");
            }

            throw new \ErrorException("$abstract::$method not found");
        }

        $routeKey = 'get.'.str_replace('/', '.', ltrim($route, '/'));

        static::$routes = collect()->set($routeKey, [
            Reflector::buildFromClass($abstract),
            Reflector::buildFromMethod($abstract, $method)
        ]);
    }

    public static function handle($method, $path)
    {
        $routeKey = strtolower($method).'.'.str_replace('/', '.', ltrim($path, '/'));
        [$controllerInstance, $methodReflector] = static::$routes->get($routeKey);
        return $methodReflector->call($controllerInstance->create());
    }
}
