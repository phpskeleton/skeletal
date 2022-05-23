<?php

namespace Skeletal\Http;

class Kernel
{
    protected static $instance;

    public $uuid;

    private function __construct()
    {
        $this->uuid = uniqid();
    }

    public static function process()
    {
        static::bootstrap();
        static::routes();

        if (property_exists(static::$instance, $prop = 'aliases')) {
            collect(static::$instance->$prop)->each(function ($alias, $class) {
                class_alias($class, $alias);
            });
        }

        return Router::handle(request()->method(), request()->path());
    }

    public static function getInstance()
    {
        return static::$instance;
    }

    private static function bootstrap()
    {
        static::$instance = new static;
        Router::bootstrap();
    }

    protected static function routes()
    {
        require app()->basePath('routes/api.php');
    }
}
