<?php
/**
 * AFAN Admin & RBAC Service
 */

class AdminService {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // --- Role Management ---

    public function createRole($name, $permissions) {
        $stmt = $this->db->prepare("INSERT INTO roles (name, permissions) VALUES (?, ?)");
        return $stmt->execute([$name, json_encode($permissions)]);
    }

    public function getRoles() {
        return $this->db->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();
    }

    public function getRole($id) {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // --- Admin Management ---

    public function createAdmin($username, $email, $password, $role_id) {
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)");
        return $stmt->execute([
            $username,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $role_id
        ]);
    }

    public function getAdmins() {
        return $this->db->query("SELECT u.*, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.id ASC")->fetchAll();
    }

    // --- Permission Check ---

    public function hasPermission($user_id, $permission_key) {
        $stmt = $this->db->prepare("SELECT r.permissions FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
        $stmt->execute([$user_id]);
        $role = $stmt->fetch();

        if (!$role) return false;

        $permissions = json_decode($role['permissions'], true);
        
        // Super Admin check (if 'all' is in permissions)
        if (in_array('all', $permissions)) return true;

        return in_array($permission_key, $permissions);
    }
}
