<?php

namespace App\Libraries;

use CodeIgniter\Email\Email;
use Config\Services;

class EmailService
{
    public function sendEmail($smtpConfig, $to, $subject, $message)
    {
        $email = Services::email();

        // Configure custom SMTP settings
        $config = [
            'protocol'  => 'smtp',
            'SMTPHost'  => $smtpConfig['smtpHost'],
            'SMTPUser'  => $smtpConfig['smtpUser'],
            'SMTPPass'  => $smtpConfig['smtpPass'],
            'SMTPPort'  => (int)$smtpConfig['smtpPort'],
            'SMTPCrypto' => 'tls', // tls or ssl
            'mailType'  => 'html',
            'charset'   => 'utf-8',
            'wordWrap'  => true,
            'newline' => '\r\n'
        ];

        $email->initialize($config);

        $email->setTo($to);
        $email->setFrom($smtpConfig['fromMail'], 'Your Custom SMTP');
        $email->setSubject($subject);
        $email->setMessage($message);

        if ($email->send()) {
            return ['status' => true, 'message' => 'Email sent successfully.'];
        } else {
            return ['status' => false, 'message' => $email->printDebugger(['headers'])];
        }
    }
}
