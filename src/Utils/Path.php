<?php

namespace App\Utils;

class Path
{
    public static function preparePath(string $path): string {
        if (PHP_OS_FAMILY === 'Windows') {
            str_replace('/', DIRECTORY_SEPARATOR, $path);
        }
        return $path;
    }
}