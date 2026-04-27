<?php
/**
 * AFAN Token Service
 */

class TokenService {
    private $db;
    private $comm;

    public function __construct($db, $commService = null) {
        $this->db = $db;
        $this->comm = $commService;
    }

    /**
     * Generate and assign a token to a beneficiary
     */
    public function issueToken($beneficiary_id, $program_id) {
        // Generate secure 8-character token (4 bytes)
        $token_code = strtoupper(generateToken(4));

        $stmt = $this->db->prepare("INSERT INTO tokens (program_id, beneficiary_id, token_code, status) VALUES (?, ?, ?, 'unused')");
        if ($stmt->execute([$program_id, $beneficiary_id, $token_code])) {
            
            // Notify Beneficiary
            if ($this->comm) {
                $beneficiary = $this->getBeneficiary($beneficiary_id);
                $program = $this->getProgram($program_id);
                
                $msg = "Hello {$beneficiary['name']}, your AFAN token for {$program['name']} is: {$token_code}. Present this to any authorized agent for redemption.";
                
                // SMS
                $this->comm->sendSMS($beneficiary['phone'], $msg);
                
                // Email if available
                if (!empty($beneficiary['email'])) {
                    $this->comm->sendEmail($beneficiary['email'], "AFAN Token Assigned", $msg);
                }
            }
            
            return $token_code;
        }
        return false;
    }

    /**
     * Redeem a token
     */
    public function redeemToken($token_code, $agent_id) {
        $stmt = $this->db->prepare("SELECT t.*, b.name as b_name, b.phone as b_phone, b.email as b_email, p.name as p_name 
                                   FROM tokens t 
                                   JOIN beneficiaries b ON t.beneficiary_id = b.id 
                                   JOIN programs p ON t.program_id = p.id
                                   WHERE t.token_code = ? AND t.status = 'unused'");
        $stmt->execute([strtoupper($token_code)]);
        $token = $stmt->fetch();

        if (!$token) return ['success' => false, 'message' => 'Invalid or already redeemed token.'];

        // Update status
        $update = $this->db->prepare("UPDATE tokens SET status = 'redeemed', redeemed_at = NOW(), redeemed_by = ? WHERE id = ?");
        if ($update->execute([$agent_id, $token['id']])) {
            
            // Audit Log
            log_audit($agent_id, 'token_redemption', "Redeemed token {$token_code} for beneficiary {$token['b_name']}");

            // Notify Beneficiary of success
            if ($this->comm) {
                $msg = "Transaction Successful: Your token {$token_code} has been redeemed for {$token['p_name']}. Thank you.";
                $this->comm->sendSMS($token['b_phone'], $msg);
                
                if (!empty($token['b_email'])) {
                    $this->comm->sendEmail($token['b_email'], "AFAN Token Redeemed", $msg);
                }
            }

            return ['success' => true, 'data' => $token];
        }

        return ['success' => false, 'message' => 'System error during redemption.'];
    }

    private function getBeneficiary($id) {
        $stmt = $this->db->prepare("SELECT * FROM beneficiaries WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    private function getProgram($id) {
        $stmt = $this->db->prepare("SELECT * FROM programs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
