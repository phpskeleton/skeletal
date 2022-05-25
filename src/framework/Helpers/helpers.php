<?php

use Skeletal\Http\Request;
use Skeletal\Http\Response;

use Skeletal\Support\Binding;
use Skeletal\Support\Collection;
use Skeletal\Support\Log;
use Skeletal\Support\Reflector;

if (! function_exists('app')) {
    function app($abstract = null)
    {
        if (is_null($abstract)) {
            return Skeletal::getInstance();
        }

        return Skeletal::getInstance()->resolve($abstract);
    }
}

if (! function_exists('bind')) {
    function bind(Closure $closure): Reflector
    {
        return Reflector::createBoundClosure($closure);
    }
}

if (! function_exists('collect')) {
    function collect(array $array = []): Collection
    {
        return new Collection($array);
    }
}

if (! function_exists('is_collection')) {
    function is_collection(mixed $item): bool
    {
        return $item instanceof Collection;
    }
}

if (! function_exists('debug')) {
    function debug($log): void
    {
        Log::debug($log);
    }
}

if (! function_exists('request')) {
    function request(): Request
    {
        return app('request');
    }
}

if (! function_exists('response')) {
    function response(): Response
    {
        return app('response')->build();
    }
}
