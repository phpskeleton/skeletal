<?php

use Skeletal\Contracts\Http\HandleRequests;
use Skeletal\Http\Request;


use Skeletal\Support\Resolver;
use Skeletal\Support\Response;

class Skeletal implements HandleRequests
{
    protected static $instance;

    protected $container = [];

    public function __construct()
    {
        static::bootstrap();

        static::$instance = $this;
    }

    public function getCurrentRequest()
    {
        return $this->request;
    }

    public function handleRequest(Closure $callback): void
    {
        /**
         * @uses Skeletal\Http\Request
         *
         * @uses Skeletal\Support\Resolver
         * @uses Skeletal\Support\Response
         */

        $this->request = $this->resolve('request')->createFromGlobals(
            $_GET, $_POST, $_COOKIE, $_FILES, $_SERVER
        );

        $callback = $callback->bindTo($this);

        $reflectionCallback = new ReflectionFunction($callback);
        $callbackParams = [];
        foreach ($reflectionCallback->getParameters() as $index => $param) {

            // if (class_exists($abstract = $param->getType())) {
            //     $dependency = $this->resolve($abstract);
            // }
            if (! $abstract = $param->getType()?->getName()) {
                $abstract = $param->getName();
            }

            $callbackParams[] = $this->resolve((string) $abstract);
        }

        Response::send(
            $reflectionCallback->invokeArgs($callbackParams)
        );
    }

    public function resolve(string $abstract)
    {
        // if (empty($this->container[$abstract])) {
        //     throw new \Exception("\"{$abstract}\" not defined on the Skeletal instance");
        // }

        return $this->container[$abstract] ?? $this->make($abstract);
             // : throw new ErrorException("\"{$abstract}\" couldn't be resolved within Skeletal instance");
    }

    public function make(string $abstract, array $arguments = [])
    {
        if (class_exists($abstract)) {
            return $this->container[$abstract] = new $abstract(...$arguments);
        }

        return array_key_exists($abstract, $class = $this->definitions())
            ? $this->$abstract = new Resolver($class[$abstract])
            : null; //throw new ErrorException("\"{$abstract}\" not defined on the Skeletal instance");
    }

    public function definitions()
    {
        return [
            'request' => \Skeletal\Http\Request::class
        ];
    }

    public static function getInstance(): Skeletal
    {
        return static::$instance ?: new static;
    }

    protected static function bootstrap(): void
    {
        set_error_handler([Skeletal\Exceptions\Handler::class, 'reportError']);
        set_exception_handler([Skeletal\Exceptions\Handler::class, 'report']);
    }

    public function __get($name): mixed
    {
        return $this->container[$name] ?? null;
    }

    public function __set($name, $value): void
    {
        $this->container[$name] = $value;
    }

}
