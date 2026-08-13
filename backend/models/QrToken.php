<?php

require_once __DIR__ . '/../config/database.php';

class QrToken
{
    public static function create(int $petId, string $token): int
    {
        $stmt = get_pdo()->prepare(
            'INSERT INTO qr_tokens (pet_id, token, is_active) VALUES (?, ?, 1)'
        );
        $stmt->execute([$petId, $token]);
        return (int) get_pdo()->lastInsertId();
    }

    public static function deactivateAllForPet(int $petId): void
    {
        $stmt = get_pdo()->prepare('UPDATE qr_tokens SET is_active = 0 WHERE pet_id = ?');
        $stmt->execute([$petId]);
    }

    public static function findActiveByToken(string $token): ?array
    {
        $stmt = get_pdo()->prepare(
            'SELECT * FROM qr_tokens WHERE token = ? AND is_active = 1'
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findActiveByPet(int $petId): ?array
    {
        $stmt = get_pdo()->prepare(
            'SELECT * FROM qr_tokens WHERE pet_id = ? AND is_active = 1 ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$petId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function tokenExists(string $token): bool
    {
        $stmt = get_pdo()->prepare('SELECT 1 FROM qr_tokens WHERE token = ?');
        $stmt->execute([$token]);
        return (bool) $stmt->fetchColumn();
    }
}
