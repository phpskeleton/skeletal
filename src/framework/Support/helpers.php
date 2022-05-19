<?php

if (! function_exists('app')) {
    function app($abstract = null)
    {
        if (is_null($abstract)) {
            return Skeletal::getInstance();
        }

        return Skeletal::getInstance()->resolve($abstract);
    }
}

if (! function_exists('debug')) {
    function debug($log): void
    {
        Skeletal\Support\Log::debug($log);
    }
}

if (! function_exists('request')) {
    function request(): Skeletal\Http\Request
    {
        return app('request');
    }
}
