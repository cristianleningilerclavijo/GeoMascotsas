<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/QrToken.php';

class Pet
{
    public static function create(int $ownerId, array $data, string $qrToken): array
    {
        $pdo = get_pdo();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO pets (owner_id, name, species, breed, color, photo_url, medical_notes, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $ownerId,
                $data['name'],
                $data['species'] ?? 'perro',
                $data['breed'] ?? null,
                $data['color'] ?? null,
                $data['photo_url'] ?? null,
                $data['medical_notes'] ?? null,
                $data['status'] ?? 'activo',
            ]);
            $petId = (int) $pdo->lastInsertId();

            QrToken::create($petId, $qrToken);

            $pdo->commit();
            return self::findByIdForOwner($petId, $ownerId);
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function listByOwner(int $ownerId): array
    {
        $stmt = get_pdo()->prepare(
            'SELECT p.*, qt.token AS qr_token
             FROM pets p
             LEFT JOIN qr_tokens qt ON qt.pet_id = p.id AND qt.is_active = 1
             WHERE p.owner_id = ?
             ORDER BY p.created_at DESC'
        );
        $stmt->execute([$ownerId]);
        return array_map([self::class, 'attachPhotoUrl'], $stmt->fetchAll());
    }

    public static function findByIdForOwner(int $petId, int $ownerId): ?array
    {
        $stmt = get_pdo()->prepare(
            'SELECT p.*, qt.token AS qr_token
             FROM pets p
             LEFT JOIN qr_tokens qt ON qt.pet_id = p.id AND qt.is_active = 1
             WHERE p.id = ? AND p.owner_id = ?'
        );
        $stmt->execute([$petId, $ownerId]);
        $row = $stmt->fetch();
        return $row ? self::attachPhotoUrl($row) : null;
    }

    public static function findPublicByToken(string $token): ?array
    {
        $stmt = get_pdo()->prepare(
            'SELECT p.*, qt.token AS qr_token, qt.id AS qr_token_id,
                    o.full_name AS owner_full_name, o.phone AS owner_phone
             FROM qr_tokens qt
             JOIN pets p ON p.id = qt.pet_id
             JOIN owners o ON o.id = p.owner_id
             WHERE qt.token = ? AND qt.is_active = 1'
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $row ? self::attachPhotoUrl($row) : null;
    }

    public static function update(int $petId, int $ownerId, array $data): bool
    {
        $fields = ['name', 'species', 'breed', 'color', 'medical_notes', 'status'];
        $sets = [];
        $params = [];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $sets[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (isset($data['photo_url'])) {
            $sets[] = 'photo_url = ?';
            $params[] = $data['photo_url'];
        }

        if (empty($sets)) {
            return false;
        }

        $params[] = $petId;
        $params[] = $ownerId;

        $stmt = get_pdo()->prepare(
            'UPDATE pets SET ' . implode(', ', $sets) . ' WHERE id = ? AND owner_id = ?'
        );
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public static function delete(int $petId, int $ownerId): bool
    {
        $stmt = get_pdo()->prepare('DELETE FROM pets WHERE id = ? AND owner_id = ?');
        $stmt->execute([$petId, $ownerId]);
        return $stmt->rowCount() > 0;
    }

    private static function attachPhotoUrl(array $pet): array
    {
        if (!empty($pet['photo_url']) && strpos($pet['photo_url'], '/') !== 0) {
            $pet['photo_url'] = UPLOADS_URL_PREFIX . $pet['photo_url'];
        }
        return $pet;
    }
}
