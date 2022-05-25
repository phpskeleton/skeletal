<?php

namespace Skeletal\Support;

class Binding
{
    public $app;

    private function __construct()
    {
        $this->app = app();
    }

    public static function create()
    {
        return new static;
    }
}
