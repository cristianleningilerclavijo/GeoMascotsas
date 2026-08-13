<?php

require_once __DIR__ . '/../config/database.php';

class Owner
{
    public static function create(string $fullName, string $email, string $passwordHash, string $phone): int
    {
        $stmt = get_pdo()->prepare(
            'INSERT INTO owners (full_name, email, password_hash, phone) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$fullName, $email, $passwordHash, $phone]);
        return (int) get_pdo()->lastInsertId();
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = get_pdo()->prepare('SELECT * FROM owners WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = get_pdo()->prepare('SELECT id, full_name, email, phone FROM owners WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
