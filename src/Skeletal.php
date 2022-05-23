<?php

use Skeletal\Contracts\Http\HandleRequests;
use Skeletal\Http\Request;


use Skeletal\Support\Reflector;
use Skeletal\Support\Resolver;
use Skeletal\Http\Response;

class Skeletal implements HandleRequests
{
    protected static $instance;

    protected static $basePath;

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
         * @uses Skeletal\Http\Response
         */

        $this->request = $this->resolve('request')->createFromGlobals(
            $_GET, $_POST, $_COOKIE, $_FILES, $_SERVER
        );

        Response::send(Reflector::createBoundClosure($callback)->call());
    }

    public function resolve(string $abstract)
    {
        if (is_a($this, $abstract)) {
            return $this;
        }

        if ($needle = array_search($abstract, $this->definitions())) {
            $abstract = $needle;
        }

        return $this->container[$abstract] ?? $this->make($abstract);
    }

    public function make(string $abstract, array $arguments = [])
    {
        if (class_exists($abstract)) {
            return $this->container[$abstract] = new $abstract(...$arguments);
        }

        return array_key_exists($abstract, $class = $this->definitions())
            ? $this->$abstract = new Resolver($class[$abstract])
            : null;
    }

    protected function definitions()
    {
        return [
            'request' => \Skeletal\Http\Request::class,
            'response' => \Skeletal\Http\Response::class
        ];
    }

    public static function getInstance(): Skeletal
    {
        return static::$instance ?: new static;
    }

    protected static function bootstrap(): void
    {
        static::$basePath = $_ENV['APP_BASE_PATH'] ?? dirname(getcwd());
        set_error_handler([Skeletal\Exceptions\Handler::class, 'reportError']);
        set_exception_handler([Skeletal\Exceptions\Handler::class, 'report']);
    }

    public static function basePath(string $path = '')
    {
        return static::$basePath.(!empty($path) ? DIRECTORY_SEPARATOR.$path : '');
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
