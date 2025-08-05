<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\NotificationService;
use App\Services\EmailService;
use App\Models\NotificationModel;

class NotificationController extends BaseController
{
    private $notificationService;
    private $emailService;
    private $notificationModel;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
        $this->emailService = new EmailService();
        $this->notificationModel = new NotificationModel();
    }

    /**
     * Test notification system
     */
    public function testSystem()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/hrd/dokumen-umum');
        }

        try {
            $testResults = $this->notificationService->testNotificationSystem();
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Test sistem notifikasi selesai',
                'results' => $testResults
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Test sistem gagal: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Test email configuration
     */
    public function testEmail()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/hrd/dokumen-umum');
        }

        try {
            $testEmail = $this->request->getPost('email') ?: session()->get('email');
            
            if (!$testEmail) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Email tidak ditemukan'
                ]);
            }

            $result = $this->emailService->testEmailConfiguration($testEmail);
            
            return $this->response->setJSON([
                'status' => $result ? 'success' : 'error',
                'message' => $result ? 'Test email berhasil dikirim!' : 'Test email gagal dikirim'
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Test email gagal: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Simulate document upload notification
     */
    public function simulateNotification()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/hrd/dokumen-umum');
        }

        try {
            $documentName = 'Test Document - ' . date('Y-m-d H:i:s');
            $uploaderName = session()->get('name') ?: 'Test User';
            $category = 'SOP';
            
            // Create fake document ID
            $documentId = time();
            
            $result = $this->notificationService->processDocumentUploadNotification(
                $documentId,
                $documentName,
                $uploaderName,
                $category
            );
            
            return $this->response->setJSON([
                'status' => $result ? 'success' : 'error',
                'message' => $result ? 'Simulasi notifikasi berhasil!' : 'Simulasi notifikasi gagal',
                'document_id' => $documentId
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Simulasi gagal: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/hrd/dokumen-umum');
        }

        try {
            $userId = session()->get('user_id');
            
            if (!$userId) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ]);
            }

            $notifications = $this->notificationService->getUserNotifications($userId);
            
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $notifications,
                'count' => count($notifications)
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal mengambil notifikasi: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/hrd/dokumen-umum');
        }

        try {
            $userId = session()->get('user_id');
            
            if (!$userId) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ]);
            }

            $result = $this->notificationService->markNotificationAsRead($notificationId, $userId);
            
            return $this->response->setJSON([
                'status' => $result ? 'success' : 'error',
                'message' => $result ? 'Notifikasi ditandai sebagai dibaca' : 'Gagal menandai notifikasi'
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menandai notifikasi: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show notification dashboard (for testing)
     */
    public function dashboard()
    {
        $data = [
            'title' => 'Dashboard Notifikasi',
            'websocket_status' => $this->checkWebSocketStatus(),
            'email_config' => $this->checkEmailConfig()
        ];

        return view('HRD/notification_dashboard', $data);
    }

    /**
     * Check WebSocket server status
     */
    private function checkWebSocketStatus()
    {
        $connection = @fsockopen('127.0.0.1', 8080, $errno, $errstr, 1);
        if ($connection) {
            fclose($connection);
            return 'running';
        }
        return 'stopped';
    }

    /**
     * Check email configuration
     */
    private function checkEmailConfig()
    {
        $config = [
            'host' => env('email.SMTPHost'),
            'user' => env('email.SMTPUser'),
            'port' => env('email.SMTPPort'),
            'configured' => !empty(env('email.SMTPHost')) && !empty(env('email.SMTPUser'))
        ];
        
        return $config;
    }
}
