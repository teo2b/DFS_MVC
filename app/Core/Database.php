<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;

final class Database
{
    public static function connection(): PDO
    {
        $host = self::requiredEnvironmentVariable('DB_HOST');
        $port = self::requiredEnvironmentVariable('DB_PORT');
        $database = self::requiredEnvironmentVariable('DB_DATABASE');
        $username = self::requiredEnvironmentVariable('DB_USERNAME');
        $password = self::requiredEnvironmentVariable('DB_PASSWORD');

        return new PDO(
            sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $database),
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    private static function requiredEnvironmentVariable(string $name): string
    {
        $value = getenv($name);

        if ($value === false || $value === '') {
            throw new RuntimeException(sprintf('La variable d’environnement %s est requise.', $name));
        }

        return $value;
    }
}
