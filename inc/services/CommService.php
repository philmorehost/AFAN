<?php
/**
 * AFAN Communication Service
 * Handles SMTP Email via PHPMailer and SMS via PhilmoreSMS API
 */

class CommService {
    private $emailConfig;
    private $smsConfig;

    public function __construct() {
        // Load configurations from global constants defined in config.php
        $this->emailConfig = [
            'host'       => defined('SMTP_HOST') ? SMTP_HOST : '',
            'port'       => defined('SMTP_PORT') ? SMTP_PORT : 587,
            'auth'       => defined('SMTP_AUTH') ? SMTP_AUTH : true,
            'user'       => defined('SMTP_USER') ? SMTP_USER : '',
            'pass'       => defined('SMTP_PASS') ? SMTP_PASS : '',
            'encryption' => defined('SMTP_ENCR') ? SMTP_ENCR : 'tls',
            'from_email' => defined('SMTP_FROM') ? SMTP_FROM : '',
            'from_name'  => defined('APP_NAME') ? APP_NAME : 'AFAN Platform'
        ];

        $this->smsConfig = [
            'token'    => defined('SMS_TOKEN') ? SMS_TOKEN : '',
            'senderID' => defined('SMS_SENDER_ID') ? SMS_SENDER_ID : 'AFAN-FISP',
            'api_url'  => 'https://app.philmoresms.com/api/sms.php',
            'bal_url'  => 'https://app.philmoresms.com/api/balance.php'
        ];
    }

    /**
     * Send Transactional Email
     */
    public function sendEmail($to, $subject, $body, $isHtml = true) {
        // PHPMailer Integration
        // Note: Assuming PHPMailer is placed in vendor/phpmailer/
        require_once dirname(__DIR__, 2) . '/vendor/phpmailer/src/Exception.php';
        require_once dirname(__DIR__, 2) . '/vendor/phpmailer/src/PHPMailer.php';
        require_once dirname(__DIR__, 2) . '/vendor/phpmailer/src/SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $this->emailConfig['host'];
            $mail->SMTPAuth   = $this->emailConfig['auth'];
            $mail->Username   = $this->emailConfig['user'];
            $mail->Password   = $this->emailConfig['pass'];
            $mail->SMTPSecure = $this->emailConfig['encryption'];
            $mail->Port       = $this->emailConfig['port'];
            $mail->Timeout    = 10; // Fast handshake

            // Recipients
            $mail->setFrom($this->emailConfig['from_email'], $this->emailConfig['from_name']);
            $mail->addAddress($to);

            // Content
            $mail->isHTML($isHtml);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            return $mail->send();
        } catch (Exception $e) {
            error_log("Email Error: " . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Send SMS via PhilmoreSMS API
     */
    public function sendSMS($recipients, $message) {
        if (empty($this->smsConfig['token'])) return false;

        $payload = [
            'token'      => $this->smsConfig['token'],
            'senderID'   => $this->smsConfig['senderID'],
            'recipients' => is_array($recipients) ? implode(',', $recipients) : $recipients,
            'message'    => $message
        ];

        return $this->apiCall($this->smsConfig['api_url'], $payload);
    }

    /**
     * Check SMS Wallet Balance
     */
    public function getSMSBalance() {
        if (empty($this->smsConfig['token'])) return false;

        $payload = ['token' => $this->smsConfig['token']];
        return $this->apiCall($this->smsConfig['bal_url'], $payload);
    }

    /**
     * Execute API Call via cURL
     */
    private function apiCall($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10s timeout limit
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("SMS API Error: " . $error);
            return false;
        }

        return json_decode($response, true);
    }
}
