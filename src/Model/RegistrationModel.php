<?php
namespace App\Model;

use App\Core\Database;

class RegistrationModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function isRegistered(int $eventId, int $userId): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM registrations WHERE event_id = ? AND user_id = ? LIMIT 1");
        $stmt->execute([$eventId, $userId]);
        return (bool)$stmt->fetchColumn();
    }

    public function countForEvent(int $eventId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM registrations WHERE event_id = ?");
        $stmt->execute([$eventId]);
        return (int)$stmt->fetchColumn();
    }

    public function register(int $eventId, int $userId, int $capacity): bool {
        if ($this->isRegistered($eventId, $userId)) {
            return true;
        }

        if ($this->countForEvent($eventId) >= $capacity) {
            return false;
        }

        $stmt = $this->db->prepare("INSERT INTO registrations (event_id, user_id) VALUES (?, ?)");
        return $stmt->execute([$eventId, $userId]);
    }

    public function getEventsForUser(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT 
                e.*,
                u.username AS author,
                GROUP_CONCAT(DISTINCT t.name ORDER BY t.name SEPARATOR ', ') AS tags
            FROM registrations r
            JOIN events e ON r.event_id = e.id
            JOIN users u ON e.user_id = u.id
            LEFT JOIN event_tag et ON et.event_id = e.id
            LEFT JOIN tags t ON t.id = et.tag_id
            WHERE r.user_id = ? AND e.approved = 1
            GROUP BY e.id
            ORDER BY e.date ASC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getUsersForEvent(int $eventId): array {
        $stmt = $this->db->prepare("
            SELECT u.id, u.username, u.email, r.created_at
            FROM registrations r
            JOIN users u ON r.user_id = u.id
            WHERE r.event_id = ?
            ORDER BY r.created_at ASC
        ");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }
}
