<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'document_id', 
        'title',
        'message',
        'is_read',
        'is_emailed'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = null;
    protected $deletedField = null;

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer',
        'document_id' => 'required|integer',
        'title' => 'required|string|max_length[255]',
        'message' => 'required|string'
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Create notification for document upload
     */
    public function createDocumentNotification($documentId, $documentName, $uploaderName, $category = null)
    {
        // Get all active users except the uploader
        $userModel = new \App\Models\UserModel();
        $users = $userModel->where('is_active', 1)->findAll();
        
        $notifications = [];
        $title = "Dokumen Baru Diupload";
        $message = "Dokumen '{$documentName}'" . ($category ? " (kategori: {$category})" : "") . " telah diupload oleh {$uploaderName}";
        
        foreach ($users as $user) {
            $notifications[] = [
                'user_id' => $user['id'],
                'document_id' => $documentId,
                'title' => $title,
                'message' => $message,
                'is_read' => 0,
                'is_emailed' => 0
            ];
        }
        
        if (!empty($notifications)) {
            return $this->insertBatch($notifications);
        }
        
        return false;
    }

    /**
     * Get unread notifications for user
     */
    public function getUnreadNotifications($userId)
    {
        return $this->where('user_id', $userId)
                   ->where('is_read', 0)
                   ->orderBy('created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId = null)
    {
        $builder = $this->where('id', $notificationId);
        if ($userId) {
            $builder->where('user_id', $userId);
        }
        
        return $builder->set('is_read', 1)->update();
    }

    /**
     * Mark notification as emailed
     */
    public function markAsEmailed($notificationId)
    {
        return $this->where('id', $notificationId)
                   ->set('is_emailed', 1)
                   ->update();
    }

    /**
     * Get notifications that need to be emailed
     */
    public function getNotificationsToEmail()
    {
        return $this->select('notifications.*, users.email, users.name as user_name')
                   ->join('users', 'users.id = notifications.user_id')
                   ->where('notifications.is_emailed', 0)
                   ->where('users.is_active', 1)
                   ->where('users.email IS NOT NULL')
                   ->findAll();
    }
}
