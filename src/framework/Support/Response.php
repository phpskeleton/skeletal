<?php

namespace Skeletal\Support;

use Stringable;
use Throwable;

class Response implements Stringable
{

    public static function json(array|Collection $body): string
    {
        header('Content-Type: ' . 'application/json');
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

    public function __toString(): string
    {
        return json_encode([
            'class' => $this::class
        ]);
    }
}
