<?php

namespace Skeletal\Http;

class Kernel
{
    public static function process()
    {
        static::routes();

        return Router::handle(request()->method(), request()->path());
    }

    protected static function routes()
    {
        require app()->basePath('routes/api.php');
    }
}
