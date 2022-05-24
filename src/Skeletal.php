<?php

use Skeletal\Contracts\Http\HandleRequests;

use Skeletal\Http\Kernel;
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
        static::bootstrap(static::$instance = $this);
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

        Response::send(bind($callback)->call());
    }

    /**
     * @todo refactor resolve() to take a $name instead of $abstract (class name)
     */
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

    /**
     * @todo refactor make() to take an alias name for the $abstract
     * - set $this->container[$name] instead of $abstract
     */
    public function make(string $abstract, array $arguments = [])
    {
        if (array_key_exists($abstract, $class = $this->definitions())) {
            return $this->container[$abstract] = new Resolver($class[$abstract]);
        }

        if (class_exists($abstract)) {
            return $this->container[$abstract] = new $abstract(...$arguments);
        }

        return null;
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

    protected static function bootstrap($instance): void
    {
        set_error_handler([Skeletal\Exceptions\Handler::class, 'reportError']);
        set_exception_handler([Skeletal\Exceptions\Handler::class, 'report']);

        static::$basePath = $_ENV['APP_BASE_PATH'] ?? dirname(getcwd());
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
