<?php
/**
 * AFAN Settings & CMS Service
 */

class SettingsService {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get a setting value
     */
    public function get($key, $default = null) {
        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    }

    /**
     * Update or create a setting
     */
    public function set($key, $value, $group = 'general') {
        $stmt = $this->db->prepare("INSERT INTO settings (setting_key, setting_value, setting_group)
                                   VALUES (?, ?, ?)
                                   ON DUPLICATE KEY UPDATE setting_value = ?, setting_group = ?");
        return $stmt->execute([$key, $value, $group, $value, $group]);
    }

    /**
     * Get settings by group
     */
    public function getByGroup($group) {
        $stmt = $this->db->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_group = ?");
        $stmt->execute([$group]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    // --- Landing Page CMS ---

    public function getLandingSections() {
        return $this->db->query("SELECT * FROM landing_sections ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    }

    public function updateLandingSection($key, $title, $content, $image_url = null) {
        $stmt = $this->db->prepare("UPDATE landing_sections SET title = ?, content = ?, image_url = ? WHERE section_key = ?");
        return $stmt->execute([$title, $content, $image_url, $key]);
    }

    // --- Dynamic Pages ---

    public function createPage($title, $slug, $content) {
        $stmt = $this->db->prepare("INSERT INTO pages (title, slug, content) VALUES (?, ?, ?)");
        return $stmt->execute([$title, $slug, $content]);
    }

    public function updatePage($id, $title, $slug, $content, $is_published = true) {
        $stmt = $this->db->prepare("UPDATE pages SET title = ?, slug = ?, content = ?, is_published = ? WHERE id = ?");
        return $stmt->execute([$title, $slug, $content, $is_published, $id]);
    }

    public function getPages($only_published = false) {
        $sql = "SELECT * FROM pages";
        if ($only_published) $sql .= " WHERE is_published = 1";
        $sql .= " ORDER BY created_at DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getPageBySlug($slug) {
        $stmt = $this->db->prepare("SELECT * FROM pages WHERE slug = ? AND is_published = 1");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    public function deletePage($id) {
        $stmt = $this->db->prepare("DELETE FROM pages WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
