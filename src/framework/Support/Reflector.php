<?php

namespace Skeletal\Support;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;

use ErrorException;

class Reflector
{

    protected function __construct(protected ReflectionClass|ReflectionFunctionAbstract $reflection, protected array $parameters = [])
    {

    }

    public function call(?object $instance = null)
    {
        if ($this->reflection instanceof ReflectionFunction) {
            return $this->reflection->invokeArgs($this->parameters);
        }

        if ($this->reflection instanceof ReflectionMethod) {
            return $this->reflection->invokeArgs($instance, $this->parameters);
        }

        throw new ErrorException('Unable to call ' . $this->reflection->getName() . ' as a function');
    }

    public function create()
    {
        if ($this->reflection instanceof ReflectionClass) {
            return $this->reflection->newInstance(...$this->parameters);
        }

        throw new ErrorException('Unable to create ' . $this->reflection->getName() . ' as a class');
    }

    public static function buildFromClass(string $class): static
    {
        $app = app();

        $reflection = new ReflectionClass($class);
        $parameters = [];

        if ($constructor = $reflection->getConstructor()) {
            foreach ($constructor->getParameters() as $index => $param) {
                if (! $abstract = $param->getType()?->getName()) {
                    $abstract = $param->getName();
                }

                $parameters[] = $app->resolve((string) $abstract);
            }
        }

        return new self($reflection, $parameters);
    }

    public static function buildFromMethod(string $class, string $method): static
    {
        $reflection = new ReflectionMethod($class, $method);
        $parameters = [];

        foreach ($reflection->getParameters() as $index => $param) {
            if (! $abstract = $param->getType()?->getName()) {
                $abstract = $param->getName();
            }

            $parameters[] = app()->resolve((string) $abstract);
        }

        return new self($reflection, $parameters);
    }

    public static function buildFromClosure(Closure $closure): static
    {
        $reflection = new ReflectionFunction($closure);
        $parameters = [];

        foreach ($reflection->getParameters() as $index => $param) {
            if (! $abstract = $param->getType()?->getName()) {
                $abstract = $param->getName();
            }

            $parameters[] = app()->resolve((string) $abstract);
        }

        return new self($reflection, $parameters);
    }

    public static function createBoundClosure(Closure $closure)
    {
        $closure->bindTo(Binding::create());
        return static::buildFromClosure($closure);
    }
}
