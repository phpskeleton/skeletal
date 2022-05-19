<?php

namespace Skeletal\Exceptions;

use Skeletal\Support\Log;
use Throwable;

class Handler
{

    protected static $level = 'debug';

    public static function report(Throwable $e): void
    {
        Log::{static::$level}($e);
    }

    public static function reportError($level, $message, $file = '', $line = 0, $context = [])
    {
        if (error_reporting() & $level) {
            // Log::debug($level);
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }
}
