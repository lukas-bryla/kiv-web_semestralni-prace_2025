<?php
namespace App\Model;

use App\Core\Database;
use PDO;

class UserModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($username, $email, $password, $role = 'user') {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$username, $email, $hash, $role]);
    }

    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT id, username, email, role, created_at FROM users ORDER BY id");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateRole($id, $newRole) {
        if ($id == 1) return false;
        $stmt = $this->db->prepare("UPDATE users SET role = ? WHERE id = ?");
        return $stmt->execute([$newRole, $id]);
    }

    public function delete($id) {
        if ($id == 1) return false;
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

