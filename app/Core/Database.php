<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

class Database
{
    private static ?PDO $instance = null;

    public static function connection(array $config): PDO
    {
        if (self::$instance === null) {
            self::$instance = new PDO(
                $config['dsn'],
                $config['username'],
                $config['password'],
                $config['options']
            );
        }

        return self::$instance;
    }
}
