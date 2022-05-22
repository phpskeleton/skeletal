<?php

namespace Skeletal\Support;

/**
 * Skeletal\Support\Log
 *
 * Log output into a file
 *
 * @author Ben Hirst
 */
class Log
{
    public static function debug(string|Stringable|Collection|array|null $log): void
    {
        if (is_array($log)) {
            $log = json_encode($log);
        }

        if ($log instanceof Collection) {
            $log = $log->getArrayCopy();
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
