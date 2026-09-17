<?php

declare(strict_types=1);

class Usuario
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = database::conexion();
    }

    public function porEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nombre, email, password_hash
               FROM usuarios
              WHERE email = ?'
        );
        $stmt->execute([$email]);

        $fila = $stmt->fetch();

        return $fila === false ? null : $fila;
    }

    public function porId(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nombre, email
               FROM usuarios
              WHERE id = ?'
        );
        $stmt->execute([$id]);

        $fila = $stmt->fetch();

        return $fila === false ? null : $fila;
    }

    public function crearConCategorias(string $nombre, string $email, string $passwordHash): bool
    {
        $pdo = $this->pdo;

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'INSERT INTO usuarios (nombre, email, password_hash)
                 VALUES (?, ?, ?)'
            );
            $stmt->execute([$nombre, $email, $passwordHash]);

            $usuarioId = (int) $pdo->lastInsertId();

            $categorias = [
                ['Sueldo', 'ingreso'],
                ['Otros ingresos', 'ingreso'],
                ['Alimentación', 'gasto'],
                ['Transporte', 'gasto'],
                ['Servicios', 'gasto'],
                ['Ocio', 'gasto'],
            ];

            $stmt = $pdo->prepare(
                'INSERT INTO categorias (usuario_id, nombre, tipo, icono, color)
                 VALUES (?, ?, ?, ?, ?)'
            );

            foreach ($categorias as [$nombreCat, $tipo]) {
                $stmt->execute([$usuarioId, $nombreCat, $tipo, 'category', '#059669']);
            }

            $pdo->commit();

            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            error_log('Alta de usuario fallida: ' . $e->getMessage());

            return false;
        }
    }
}
