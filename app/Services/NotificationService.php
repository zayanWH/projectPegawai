<?php

namespace App\Services;

use App\Models\NotificationModel;
use App\Services\WebSocketService;
use App\Services\EmailService;

class NotificationService
{
    private $notificationModel;
    private $webSocketService;
    private $emailService;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
        $this->emailService = new EmailService();
    }

    /**
     * Process document upload notification
     * This is the main method called when a file is uploaded
     */
    public function processDocumentUploadNotification($documentId, $documentName, $uploaderName, $category = null)
    {
        try {
            // Step 1: Create notifications in database
            $result = $this->notificationModel->createDocumentNotification(
                $documentId, 
                $documentName, 
                $uploaderName, 
                $category
            );

            if (!$result) {
                log_message('error', 'Failed to create notifications in database');
                return false;
            }

            log_message('info', "Created notifications for document upload: {$documentName}");

            // Step 2: Send real-time WebSocket notifications
            $this->sendRealtimeNotifications($documentId, $documentName, $uploaderName, $category);

            // Step 3: Send email notifications (async)
            $this->sendEmailNotifications();

            return true;

        } catch (\Exception $e) {
            log_message('error', 'NotificationService error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send real-time WebSocket notifications
     */
    private function sendRealtimeNotifications($documentId, $documentName, $uploaderName, $category)
    {
        try {
            // Prepare notification data
            $notificationData = [
                'type' => 'document_upload',
                'document_id' => $documentId,
                'document_name' => $documentName,
                'uploader_name' => $uploaderName,
                'category' => $category,
                'message' => "Dokumen '{$documentName}'" . ($category ? " (kategori: {$category})" : "") . " telah diupload oleh {$uploaderName}",
                'timestamp' => date('Y-m-d H:i:s'),
                'url' => base_url('hrd/dokumen-umum')
            ];

            // Send via WebSocket if server is running
            if ($this->isWebSocketServerRunning()) {
                $this->broadcastWebSocketNotification($notificationData);
            } else {
                log_message('warning', 'WebSocket server is not running, skipping real-time notification');
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to send real-time notifications: ' . $e->getMessage());
        }
    }

    /**
     * Send email notifications to users
     */
    private function sendEmailNotifications()
    {
        try {
            // Get notifications that need to be emailed
            $notifications = $this->notificationModel->getNotificationsToEmail();

            if (empty($notifications)) {
                log_message('info', 'No notifications to email');
                return;
            }

            // Send emails in background (you might want to use a queue system for this)
            $this->processEmailNotifications($notifications);

        } catch (\Exception $e) {
            log_message('error', 'Failed to send email notifications: ' . $e->getMessage());
        }
    }

    /**
     * Process email notifications
     */
    private function processEmailNotifications($notifications)
    {
        // In a production environment, you might want to use a queue system
        // For now, we'll process them synchronously with a small delay
        
        $results = $this->emailService->sendBulkNotificationEmails($notifications);
        
        log_message('info', "Email notifications processed: {$results['success']} sent, {$results['failed']} failed");
    }

    /**
     * Check if WebSocket server is running
     */
    private function isWebSocketServerRunning($host = '127.0.0.1', $port = 8080)
    {
        $connection = @fsockopen($host, $port, $errno, $errstr, 1);
        if ($connection) {
            fclose($connection);
            return true;
        }
        return false;
    }

    /**
     * Broadcast WebSocket notification
     */
    private function broadcastWebSocketNotification($notificationData)
    {
        try {
            // Send HTTP request to WebSocket server endpoint
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8080/broadcast');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notificationData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($notificationData))
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                log_message('info', 'WebSocket notification broadcasted successfully');
            } else {
                log_message('warning', "WebSocket broadcast failed with HTTP code: {$httpCode}");
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to broadcast WebSocket notification: ' . $e->getMessage());
        }
    }

    /**
     * Get unread notifications for user
     */
    public function getUserNotifications($userId)
    {
        return $this->notificationModel->getUnreadNotifications($userId);
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead($notificationId, $userId)
    {
        return $this->notificationModel->markAsRead($notificationId, $userId);
    }

    /**
     * Test notification system
     */
    public function testNotificationSystem($testEmail = null)
    {
        try {
            // Test email configuration
            $emailTest = $this->emailService->testEmailConfiguration($testEmail);
            
            // Test WebSocket connection
            $webSocketTest = $this->isWebSocketServerRunning();
            
            return [
                'email' => $emailTest,
                'websocket' => $webSocketTest,
                'database' => $this->testDatabaseConnection()
            ];
            
        } catch (\Exception $e) {
            log_message('error', 'Notification system test failed: ' . $e->getMessage());
            return [
                'email' => false,
                'websocket' => false,
                'database' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Test database connection
     */
    private function testDatabaseConnection()
    {
        try {
            $db = \Config\Database::connect();
            return $db->connID ? true : false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
