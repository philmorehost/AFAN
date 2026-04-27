<?php
/**
 * AFAN Beneficiary Service
 */

class BeneficiaryService {
    private $db;
    private $ninApiKey;

    public function __construct($db) {
        $this->db = $db;
        $this->ninApiKey = defined('NIN_API_KEY') ? NIN_API_KEY : '';
    }

    /**
     * Verify NIN via Datagifting API
     */
    public function verifyNIN($nin) {
        if (empty($this->ninApiKey)) {
            return ['status' => 'error', 'message' => 'NIN Verification API Key not configured.'];
        }

        $url = "https://v6.datagifting.com.ng/web/api/nin-card.php";
        $data = [
            'api_key' => $this->ninApiKey,
            'nin'     => $nin
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['status' => 'error', 'message' => 'NIN API connection error: ' . $error];
        }

        return json_decode($response, true);
    }

    /**
     * Register a new beneficiary
     */
    public function register($data) {
        // Check if exists
        $check = $this->db->prepare("SELECT id FROM beneficiaries WHERE nin = ? OR phone = ?");
        $check->execute([$data['nin'], $data['phone']]);
        if ($check->fetch()) return false;

        $stmt = $this->db->prepare("INSERT INTO beneficiaries (name, phone, email, nin, gender, state_of_origin, photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['name'],
            $data['phone'],
            $data['email'] ?? null,
            $data['nin'],
            $data['gender'] ?? null,
            $data['state_of_origin'] ?? null,
            $data['photo'] ?? null
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
     * Count total beneficiaries
     */
    public function countAll() {
        return $this->db->query("SELECT COUNT(*) FROM beneficiaries")->fetchColumn();
    }
}
