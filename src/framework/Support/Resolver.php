<?php

namespace Skeletal\Support;

use Stringable;

class Resolver implements Stringable
{
    public function __construct(protected string $abstract)
    {
        //
    }

    public function build()
    {
        return new $this->abstract;
    }

    public function __call(string $method, array $arguments)
    {
        return $this->abstract::$method(...$arguments);
    }

    public function __toString(): string
    {
        $class = $this::class;
        return "[ class $class ] -> $this->abstract";
    }
}
