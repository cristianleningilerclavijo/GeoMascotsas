<?php

require_once __DIR__ . '/../config/database.php';

class ScanLog
{
    public static function create(int $qrTokenId, int $petId, string $ipAddress, string $userAgent): void
    {
        $stmt = get_pdo()->prepare(
            'INSERT INTO scan_logs (qr_token_id, pet_id, ip_address, user_agent) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$qrTokenId, $petId, $ipAddress, substr($userAgent, 0, 255)]);
    }

    public static function listByPet(int $petId, int $ownerId): array
    {
        $stmt = get_pdo()->prepare(
            'SELECT sl.id, sl.scanned_at, sl.ip_address, sl.user_agent
             FROM scan_logs sl
             JOIN pets p ON p.id = sl.pet_id
             WHERE sl.pet_id = ? AND p.owner_id = ?
             ORDER BY sl.scanned_at DESC'
        );
        $stmt->execute([$petId, $ownerId]);
        return $stmt->fetchAll();
    }
}
