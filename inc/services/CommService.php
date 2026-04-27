<?php
/**
 * AFAN Communication Service
 * Handles SMTP Email via PHPMailer and SMS via PhilmoreSMS API (v2)
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class CommService {
    private $emailConfig;
    private $smsConfig;

    public function __construct($db = null) {
        $settings = null;
        if ($db) {
            $settingsService = new SettingsService($db);
            $settings = [
                'smtp' => $settingsService->getByGroup('smtp'),
                'sms' => $settingsService->getByGroup('sms')
            ];
        }

        // Load configurations: DB settings take precedence over config.php constants
        $this->emailConfig = [
            'host'       => $settings['smtp']['smtp_host'] ?? (defined('SMTP_HOST') ? SMTP_HOST : ''),
            'port'       => $settings['smtp']['smtp_port'] ?? (defined('SMTP_PORT') ? SMTP_PORT : 587),
            'auth'       => true,
            'user'       => $settings['smtp']['smtp_user'] ?? (defined('SMTP_USER') ? SMTP_USER : ''),
            'pass'       => $settings['smtp']['smtp_pass'] ?? (defined('SMTP_PASS') ? SMTP_PASS : ''),
            'encryption' => defined('SMTP_ENCR') ? SMTP_ENCR : 'tls',
            'from_email' => $settings['smtp']['smtp_from'] ?? (defined('SMTP_FROM') ? SMTP_FROM : ''),
            'from_name'  => defined('APP_NAME') ? APP_NAME : 'AFAN Platform'
        ];

        $this->smsConfig = [
            'api_key'  => $settings['sms']['sms_api_key'] ?? (defined('SMS_API_KEY') ? SMS_API_KEY : ''),
            'sender_id' => $settings['sms']['sms_sender_id'] ?? (defined('SMS_SENDER_ID') ? SMS_SENDER_ID : 'AFAN-FISP'),
            'api_url'  => 'https://philmoresms.com/api/v2/sms/send'
        ];
    }

    /**
     * Send Transactional Email
     */
    public function sendEmail($to, $subject, $body, $isHtml = true) {
        if (empty($this->emailConfig['host'])) return false;

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $this->emailConfig['host'];
            $mail->SMTPAuth   = $this->emailConfig['auth'];
            $mail->Username   = $this->emailConfig['user'];
            $mail->Password   = $this->emailConfig['pass'];
            $mail->SMTPSecure = $this->emailConfig['encryption'] === 'none' ? '' : $this->emailConfig['encryption'];
            $mail->Port       = $this->emailConfig['port'];
            $mail->Timeout    = 10;

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
     * Send SMS via PhilmoreSMS API v2
     */
    public function sendSMS($recipients, $message) {
        if (empty($this->smsConfig['api_key'])) return false;

        $recipientList = is_array($recipients) ? $recipients : explode(',', $recipients);

        $results = [];
        foreach ($recipientList as $recipient) {
            $payload = [
                'sender_id' => $this->smsConfig['sender_id'],
                'recipient' => trim($recipient),
                'message'   => $message
            ];

            $response = $this->apiCall($this->smsConfig['api_url'], $payload);
            $results[] = $response;
        }

        return $results;
    }

    /**
     * Execute API Call via cURL with Header-based Auth
     */
    private function apiCall($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->smsConfig['api_key'],
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            error_log("SMS API Error: " . $error);
            return ['status' => 'error', 'message' => $error];
        }

        $result = json_decode($response, true);
        if ($httpCode >= 400 || (isset($result['status']) && ($result['status'] === 'error' || $result['status'] === false))) {
            return ['status' => 'error', 'message' => $result['message'] ?? 'API error'];
        }

        return ['status' => 'success', 'data' => $result];
    }
}
