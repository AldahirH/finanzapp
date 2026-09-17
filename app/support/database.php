<?php

declare(strict_types=1);

class database{
    private static ?PDO $pdo = null;

    public static function conexion(): PDO{
        if (self::$pdo === null) {
            $config = require __DIR__ . '/../../config/database.php';

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['dbname'],
                $config['charset']
            );

            try {
                self::$pdo = new PDO($dsn, $config['user'], $config['password'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log('Conexión fallida: ' . $e->getMessage());
                exit('No se pudo conectar con la base de datos.');
            }
        }

        return self::$pdo;
    }
}