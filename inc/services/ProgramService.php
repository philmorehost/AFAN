<?php
/**
 * AFAN Support Program Service
 */

class ProgramService {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Create a new support program
     */
    public function createProgram($name, $description, $start_date, $end_date) {
        $stmt = $this->db->prepare("INSERT INTO programs (name, description, start_date, end_date) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$name, $description, $start_date, $end_date]);
    }

    /**
     * Get all programs
     */
    public function getAll() {
        return $this->db->query("SELECT * FROM programs ORDER BY created_at DESC")->fetchAll();
    }

    /**
     * Get active programs
     */
    public function getActive() {
        return $this->db->query("SELECT * FROM programs WHERE status = 'active' ORDER BY created_at DESC")->fetchAll();
    }

    /**
     * Count programs
     */
    public function countAll() {
        return $this->db->query("SELECT COUNT(*) FROM programs")->fetchColumn();
    }

    /**
     * Update program status
     */
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE programs SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}
