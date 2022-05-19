<?php

namespace Skeletal\Support;

use Stringable;

class Response implements Stringable
{

    public static function json(array $body): string
    {
        header('Content-Type: ' . 'application/json');
        return json_encode($body);
    }

    public static function send(string $body): void
    {
        echo $body;
    }

    public function __toString(): string
    {
        return json_encode([
            'class' => $this::class
        ]);
    }
}
