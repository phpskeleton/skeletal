<?php

namespace Skeletal\Support;

use Stringable;

/**
 * This class might change
 *
 * Currently this class is used to chain static method calls
 * onto the $app->resolve() method so that it looks syntactically correct
 * For example imagine:
 *
 * $app->resolve('request')::createFromGlobals(...)
 * this doesnt really look syntactically correct, even if it technically is
 *
 * $app->resolve('request')->createFromGlobals(...)
 * looks much better
 */
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
