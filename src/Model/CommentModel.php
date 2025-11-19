<?php
namespace App\Model;

use App\Core\Database;
use PDO;

class CommentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create(int $eventId, int $userId, string $content): bool {
        $stmt = $this->db->prepare("
            INSERT INTO comments (event_id, user_id, content, status)
            VALUES (?, ?, ?, 0)
        ");
        return $stmt->execute([$eventId, $userId, trim($content)]);
    }

    public function getApprovedForEvent(int $eventId): array {
        $stmt = $this->db->prepare("
            SELECT c.*, u.username
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.event_id = ? AND c.status = 1
            ORDER BY c.created_at ASC
        ");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }

    public function getPending(): array {
        $stmt = $this->db->query("
            SELECT c.*, u.username, e.title AS event_title
            FROM comments c
            JOIN users u ON c.user_id = u.id
            JOIN events e ON c.event_id = e.id
            WHERE c.status = 0
            ORDER BY c.created_at ASC
        ");
        return $stmt->fetchAll();
    }

    public function setStatus(int $id, int $status): bool {
        $stmt = $this->db->prepare("UPDATE comments SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}
