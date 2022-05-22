<?php

namespace Skeletal\Support;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionParameter;

class Reflector
{

    protected function __construct(protected ReflectionClass|ReflectionFunction $reflection, protected array $parameters = [])
    {

    }

    public function call()
    {
        if ($this->reflection instanceof ReflectionFunction) {
            return $this->reflection->invokeArgs($this->parameters);
        }

        throw new ErrorException("Unable to call " . $reflection->getName() . ' as a function');
    }

    public static function buildFromClass(string $class): static
    {

    }

    public static function buildFromClosure(Closure $closure): static
    {
        $app = app();
        $closure->bindTo($app);

        $reflection = new ReflectionFunction($closure);
        $parameters = [];

        foreach ($reflection->getParameters() as $index => $param) {
            if (! $abstract = $param->getType()?->getName()) {
                $abstract = $param->getName();
            }

            $parameters[] = $app->resolve((string) $abstract);
        }

        return new self($reflection, $parameters);
    }
}
