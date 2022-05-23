<?php

namespace Skeletal;

use Skeletal\Http\Router;

class Kernel
{
    public static function process()
    {
        static::routes();
        
        return Router::handle('GET', '/user');
    }

    protected static function routes()
    {
        require app()->basePath('routes/api.php');
    }
}
