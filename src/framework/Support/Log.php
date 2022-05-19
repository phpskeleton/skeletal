<?php

namespace Skeletal\Support;

class Log
{
    public static function debug(string|Stringable|array|null $log): void
    {
        if (is_array($log)) {
            $log = json_encode($log);
        }

        file_put_contents(static::getLogFile(), ($log ?? 'null').PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    public static function echo(string $log): void
    {
        echo $log.PHP_EOL;
        debug('echo done');
    }

    private static function getLogFile(): string
    {
        return getcwd().'/../storage/logs/skeletal.log';
    }
}
