<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $mailer;
    private $config;

    public function __construct()
    {
        $this->config = config('Email');
        $this->setupMailer();
    }

    /**
     * Setup PHPMailer configuration
     */
    private function setupMailer()
    {
        $this->mailer = new PHPMailer(true);

        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = env('email.SMTPHost', 'smtp.gmail.com');
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = env('email.SMTPUser');
            $this->mailer->Password = env('email.SMTPPass');
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = env('email.SMTPPort', 587);

            // Default sender
            $this->mailer->setFrom(
                env('email.fromEmail', env('email.SMTPUser')), 
                env('email.fromName', 'Sistem Dokumen HRD')
            );

            // Character set
            $this->mailer->CharSet = 'UTF-8';
            
        } catch (Exception $e) {
            log_message('error', 'EmailService setup failed: ' . $e->getMessage());
        }
    }

    /**
     * Send notification email
     */
    public function sendNotificationEmail($toEmail, $toName, $notification)
    {
        try {
            // Reset recipients
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            // Recipients
            $this->mailer->addAddress($toEmail, $toName);

            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $notification['title'];
            
            $htmlBody = $this->buildNotificationEmailTemplate($notification['title'], $notification['message'], $toEmail, $notification['document_name']);
            $this->mailer->Body = $htmlBody;
            
            // Plain text version
            $this->mailer->AltBody = strip_tags($notification['message']);

            // Send email
            $result = $this->mailer->send();
            
            if ($result) {
                log_message('info', "Notification email sent to {$toEmail}");
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            log_message('error', "Failed to send notification email to {$toEmail}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Build HTML email template for notification
     */
    private function buildNotificationEmailTemplate($title, $message, $toEmail, $documentName = null)
    {
        $baseUrl = base_url();
        $logoUrl = $baseUrl . 'assets/images/logo.png'; // Adjust path as needed
        
        $html = '
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . htmlspecialchars($title) . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
                .header { background-color: #2563eb; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; }
                .notification-box { background-color: #eff6ff; border-left: 4px solid #2563eb; padding: 20px; margin: 20px 0; }
                .footer { background-color: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #64748b; }
                .button { display: inline-block; background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 15px 0; }
                .button:hover { background-color: #1d4ed8; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🔔 Notifikasi Dokumen HRD</h1>
                </div>
                
                <div class="content">
                    <h2>Halo!</h2>
                    
                    <div class="notification-box">
                        <h3>' . htmlspecialchars($title) . '</h3>
                        <p>' . nl2br(htmlspecialchars($message)) . '</p>
                        <p><small>📅 ' . date('d F Y, H:i') . '</small></p>
                    </div>
                    
                    <p>Anda dapat melihat dokumen tersebut dengan mengklik tombol di bawah ini:</p>
                    
                    <a href="' . $baseUrl . 'hrd/dokumen-umum" class="button">
                        📄 Lihat Dokumen Umum
                    </a>
                    
                    <hr style="margin: 30px 0; border: none; border-top: 1px solid #e2e8f0;">
                    
                    <p><small>
                        <strong>Catatan:</strong> Email ini dikirim secara otomatis oleh sistem. 
                        Jangan membalas email ini.
                    </small></p>
                </div>
                
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' Sistem Manajemen Dokumen HRD</p>
                    <p>Email ini dikirim ke ' . htmlspecialchars($toEmail) . '</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }

    /**
     * Send bulk notification emails
     */
    public function sendBulkNotificationEmails($notifications)
    {
        $successCount = 0;
        $failCount = 0;
        
        foreach ($notifications as $notification) {
            if (isset($notification['email']) && isset($notification['user_name'])) {
                $result = $this->sendNotificationEmail(
                    $notification['email'],
                    $notification['user_name'],
                    $notification
                );
                
                if ($result) {
                    $successCount++;
                    // Mark as emailed in database
                    $notificationModel = new \App\Models\NotificationModel();
                    $notificationModel->markAsEmailed($notification['id']);
                } else {
                    $failCount++;
                }
                
                // Small delay to avoid overwhelming SMTP server
                usleep(500000); // 0.5 second delay
            }
        }
        
        log_message('info', "Bulk email sending completed: {$successCount} success, {$failCount} failed");
        
        return [
            'success' => $successCount,
            'failed' => $failCount,
            'total' => count($notifications)
        ];
    }

    /**
     * Test email configuration
     */
    public function testEmailConfiguration($testEmail = null)
    {
        try {
            $testEmail = $testEmail ?: env('email.SMTPUser');
            
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($testEmail, 'Test User');
            
            $this->mailer->Subject = 'Test Konfigurasi Email - Sistem Dokumen HRD';
            $this->mailer->Body = '
                <h2>Test Email Berhasil!</h2>
                <p>Konfigurasi email sistem dokumen HRD telah berhasil diatur.</p>
                <p>Timestamp: ' . date('Y-m-d H:i:s') . '</p>
            ';
            $this->mailer->AltBody = 'Test email berhasil dikirim pada ' . date('Y-m-d H:i:s');
            
            return $this->mailer->send();
            
        } catch (Exception $e) {
            log_message('error', 'Email test failed: ' . $e->getMessage());
            return false;
        }
    }
}
