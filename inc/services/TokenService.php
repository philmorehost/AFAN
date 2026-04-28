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
                
                // SMS (Plain Text)
                $this->comm->sendSMS($beneficiary['phone'], $msg);
                
                // Email (Modern HTML)
                if (!empty($beneficiary['email'])) {
                    $emailContent = "
                        <p>Hello {$beneficiary['name']},</p>
                        <p>A new distribution token has been assigned to you for the following program:</p>
                        <p><strong>Program:</strong> {$program['name']}</p>
                        <div style='text-align: center;'>
                            <div class='token-badge'>{$token_code}</div>
                        </div>
                        <p>Please present this token to any authorized AFAN agent to receive your inputs/benefits.</p>
                    ";
                    $htmlBody = get_email_template("New Token Assigned", $emailContent);
                    $this->comm->sendEmail($beneficiary['email'], "AFAN Token Assigned", $htmlBody);
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
                    $emailContent = "
                        <p>Hello {$token['b_name']},</p>
                        <p>This is to confirm that your token has been successfully redeemed.</p>
                        <div class='alert-box' style='border-left-color: #059669; background-color: #ecfdf5;'>
                            <strong>Redemption Details:</strong><br>
                            Program: {$token['p_name']}<br>
                            Token: {$token_code}<br>
                            Date: " . date('F j, Y, g:i a') . "
                        </div>
                        <p>Thank you for participating in the AFAN Food Security Program.</p>
                    ";
                    $htmlBody = get_email_template("Token Redeemed Successfully", $emailContent);
                    $this->comm->sendEmail($token['b_email'], "AFAN Token Redeemed", $htmlBody);
                }
            }

            return ['success' => true, 'data' => $token];
        }

        return ['success' => false, 'message' => 'System error during redemption.'];
    }

    /**
     * Count tokens
     */
    public function countIssued() {
        return $this->db->query("SELECT COUNT(*) FROM tokens")->fetchColumn();
    }

    public function countRedeemed() {
        return $this->db->query("SELECT COUNT(*) FROM tokens WHERE status = 'redeemed'")->fetchColumn();
    }

    /**
     * Get recent redemptions
     */
    public function getRecentRedemptions($limit = 10) {
        $stmt = $this->db->prepare("SELECT t.*, b.name as beneficiary_name, p.name as program_name
                                   FROM tokens t
                                   JOIN beneficiaries b ON t.beneficiary_id = b.id
                                   JOIN programs p ON t.program_id = p.id
                                   WHERE t.status = 'redeemed'
                                   ORDER BY t.redeemed_at DESC LIMIT ?");
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
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
