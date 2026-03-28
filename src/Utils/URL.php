<?php

namespace App\Utils;

class URL
{
    private static string $url;

    public function __construct(string $url) {
        self::$url = $url;
    }
    public static function setUrl(string $url): void {
        self::$url = $url;
    }
    public static function addUrl(string $url): void
    {
        self::$url .= $url;
    }
    public static function getUrl(): string {
        return self::$url;
    }
    public static function make(string $value): string {
        self::addUrl(str_replace(' ', '-', $value));
        self::addUrl('-' . date('m-d'));
        return self::getUrl();
    }
}