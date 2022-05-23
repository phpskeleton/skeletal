<?php

namespace Skeletal\Http;

use Skeletal\Support\Reflector;

class Router
{
    protected static $routes;

    protected static $request;

    public static function get(string $route, array|string $controller)
    {
        static::requestMethod(__FUNCTION__, $route, $controller);
    }

    public static function post(string $route, array|string $controller)
    {
        static::requestMethod(__FUNCTION__, $route, $controller);
    }

    private static function requestMethod(string $httpMethod, string $route, array|string $controller)
    {
        /**
         * @todo think about moving this bootstrap call
         */
        static::bootstrap();

        if (is_string($controller)) {
            $controller = explode('@', $controller);
        }

        if (!class_exists($abstract = $controller[0]) || !method_exists($abstract, $method = $controller[1])) {
            if (empty($method)) {
                throw new \ErrorException("class $abstract not found");
            }

            throw new \ErrorException("$abstract::$method not found");
        }

        $routeKey = $httpMethod.'.'.str_replace('/', '.', ltrim($route, '/'));

        static::$routes->set($routeKey, [
            Reflector::buildFromClass($abstract),
            Reflector::buildFromMethod($abstract, $method)
        ]);
    }

    public static function handle($method, $path)
    {
        $routeKey = strtolower($method).'.'.str_replace('/', '.', ltrim($path, '/'));
        [$controllerInstance, $methodReflector] = static::$routes->get($routeKey);

        try {
            return $methodReflector->call($controllerInstance->create());
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Route ' . request()->method() . ' ' . $path . ' not found'
            ], 404);
        }
    }

    private static function bootstrap()
    {
        if (empty(static::$routes)) {
            static::$routes = collect();
        }
    }
}
