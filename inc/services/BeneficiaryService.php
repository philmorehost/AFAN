<?php
/**
 * AFAN Beneficiary Service
 */

class BeneficiaryService {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Add a single beneficiary
     */
    public function addBeneficiary($data) {
        $stmt = $this->db->prepare("INSERT INTO beneficiaries (name, phone, email, location, nin_number, farm_size) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['name'],
            $data['phone'],
            $data['email'] ?? null,
            $data['location'] ?? null,
            $data['nin_number'] ?? null,
            $data['farm_size'] ?? 0
        ]);
    }

    /**
     * Get all beneficiaries
     */
    public function getAll($limit = 100, $offset = 0) {
        $stmt = $this->db->prepare("SELECT * FROM beneficiaries ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Import beneficiaries from CSV
     */
    public function importCSV($filePath) {
        $handle = fopen($filePath, "r");
        if (!$handle) return false;

        $header = fgetcsv($handle); // Skip header
        $count = 0;

        $this->db->beginTransaction();
        try {
            while (($data = fgetcsv($handle)) !== FALSE) {
                // Assuming columns: Name, Phone, Email, Location, NIN, FarmSize
                $this->addBeneficiary([
                    'name'      => $data[0],
                    'phone'     => $data[1],
                    'email'     => $data[2] ?? null,
                    'location'  => $data[3] ?? null,
                    'nin_number'=> $data[4] ?? null,
                    'farm_size' => $data[5] ?? 0
                ]);
                $count++;
            }
            $this->db->commit();
            return $count;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Import Error: " . $e->getMessage());
            return false;
        }
    }
}
