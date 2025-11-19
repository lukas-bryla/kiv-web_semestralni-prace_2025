<?php
namespace App\Model;

use App\Core\Database;
use PDO;
use Throwable;

class EventModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($approved = null, array $filters = []) {
        $sql = "
            SELECT 
                e.*,
                u.username AS author,
                GROUP_CONCAT(DISTINCT t.name ORDER BY t.name SEPARATOR ', ') AS tags
            FROM events e
            JOIN users u ON e.user_id = u.id
            LEFT JOIN event_tag et ON et.event_id = e.id
            LEFT JOIN tags t ON t.id = et.tag_id
        ";

        $conditions = [];
        $params = [];

        if ($approved !== null) {
            $conditions[] = "e.approved = ?";
            $params[] = $approved;
        }

        if (!empty($filters['only_upcoming'])) {
            $conditions[] = "e.date >= NOW()";
        }

        if (!empty($filters['query'])) {
            $q = '%' . $filters['query'] . '%';
            $conditions[] = "(e.title LIKE ? OR e.location LIKE ? OR e.description LIKE ? OR t.name LIKE ?)";
            array_push($params, $q, $q, $q, $q);
        }

        if (!empty($filters['tag_ids']) && is_array($filters['tag_ids'])) {
            $tagIds = array_values(array_unique(array_map('intval', $filters['tag_ids'])));
            if ($tagIds) {
                $placeholders = implode(',', array_fill(0, count($tagIds), '?'));
                $conditions[] = "e.id IN (
                    SELECT et2.event_id
                    FROM event_tag et2
                    WHERE et2.tag_id IN ($placeholders)
                    GROUP BY et2.event_id
                    HAVING COUNT(DISTINCT et2.tag_id) = " . count($tagIds) . "
                )";
                $params = array_merge($params, $tagIds);
            }
        }

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= " GROUP BY e.id ORDER BY e.date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data, ?string $image = null, array $tagIds = []): bool {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO events 
                (title, description, date, capacity, location, image, approved, user_id) 
                VALUES (?, ?, ?, ?, ?, ?, 0, ?)
            ");
            $stmt->execute([
                htmlspecialchars($data['title']),
                $data['description'],
                $data['date'],
                (int)$data['capacity'],
                htmlspecialchars($data['location']),
                $image,
                $_SESSION['user']['id'],
            ]);

            $eventId = (int)$this->db->lastInsertId();

            $tagIds = array_values(array_unique(array_map('intval', $tagIds)));
            if ($eventId > 0 && $tagIds) {
                $etStmt = $this->db->prepare("INSERT INTO event_tag (event_id, tag_id) VALUES (?, ?)");
                foreach ($tagIds as $tagId) {
                    if ($tagId > 0) {
                        $etStmt->execute([$eventId, $tagId]);
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function find($id) {
        $stmt = $this->db->prepare("
            SELECT 
                e.*,
                u.username AS author,
                GROUP_CONCAT(DISTINCT t.name ORDER BY t.name SEPARATOR ', ') AS tags
            FROM events e
            JOIN users u ON e.user_id = u.id
            LEFT JOIN event_tag et ON et.event_id = e.id
            LEFT JOIN tags t ON t.id = et.tag_id
            WHERE e.id = ?
            GROUP BY e.id
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function approve($id) {
        $stmt = $this->db->prepare("UPDATE events SET approved = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM events WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAllTags($approved = 1) {
        $stmt = $this->db->query("SELECT id, name FROM tags ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
