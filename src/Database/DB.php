<?php

namespace App\Database;

use PDO;

class DB {
    private static ?PDO $instance = NULL;
    private static string $db_path = '/../../database/database.sqlite';

    public static function getConnection(): PDO {
        if (self::$instance === NULL) {
            $path = __DIR__ . self::preparePath(self::$db_path);
            self::$instance = new PDO('sqlite:' . $path);
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            self::$instance->exec('CREATE TABLE IF NOT EXISTS articles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                author TEXT NULL,
                content TEXT NULL,
                url TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP  
)           ');
        }
        return self::$instance;
    }

    public static function preparePath(string $path): string {
        if (PHP_OS_FAMILY === 'Windows') {
            str_replace('/', DIRECTORY_SEPARATOR, $path);
        }
        return $path;
    }
}
