<?php

namespace Skeletal\Http;

use Stringable;
use Throwable;

class Response implements Stringable
{

    public static function json(array|Collection $body, int $code = 0): string
    {
        header('Content-Type: ' . 'application/json');
        http_response_code($code);

        return json_encode($body instanceof Collection ? $body->getArrayCopy() : $body);
    }

    public static function send(string|Stringable $body): void
    {
        echo $body;
    }

    public static function sendError(Throwable $e)
    {
        echo static::json([
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }

    public function __call(string $method, array $arguments)
    {
        return static::$method(...$arguments);
    }

    public function __toString(): string
    {
        return json_encode([
            'class' => $this::class
        ]);
    }
}
