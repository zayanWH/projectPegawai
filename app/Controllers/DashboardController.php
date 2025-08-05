<?php

namespace App\Controllers;

use App\Models\FolderModel;
use App\Models\HrdDocumentModel;
use App\Models\NotificationModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class DashboardController extends BaseController
{
    public function index()
    {
        // Get current user info from session
        $session = session();
        $userRole = $session->get('role') ?? 'HRD';
        $userName = $session->get('name') ?? 'User';
        $userId = $session->get('user_id') ?? 1;

        // Get statistics
        $folderModel = new FolderModel();
        $documentModel = new HrdDocumentModel();
        $notificationModel = new NotificationModel();
        $userModel = new UserModel();
        
        // Get folder statistics
        $totalFolders = $folderModel->countAll();
        $userFolders = $folderModel->where('owner_id', $userId)->countAllResults();
        
        // Get document statistics
        $totalDocuments = $documentModel->countAll();
        $totalFiles = $documentModel->countAll(); // Same as totalDocuments for now
        $totalHrdFiles = $documentModel->countAll(); // Same as totalDocuments for now
        
        // Get user statistics
        $totalUser = $userModel->countAll();
        
        // Get recent documents (limit 5)
        $recentDocuments = $documentModel->orderBy('created_at', 'DESC')->limit(5)->findAll();
        
        // Get unread notifications for current user
        $unreadNotifications = $notificationModel->where('user_id', $userId)
                                                ->where('is_read', 0)
                                                ->countAllResults();
        
        // Prepare data for view
        $data = [
            'title' => 'Dashboard HRD',
            'userRole' => $userRole,
            'userName' => $userName,
            'userId' => $userId,
            'totalFolders' => $totalFolders,
            'userFolders' => $userFolders,
            'totalDocuments' => $totalDocuments,
            'totalFiles' => $totalDocuments, // Same as totalDocuments for now
            'totalHrdFiles' => $totalDocuments, // Same as totalDocuments for now
            'totalUser' => $totalUser,
            'recentDocuments' => $recentDocuments,
            'unreadNotifications' => $unreadNotifications
        ];

        // Show dashboard view
        return view('HRD/dashboard', $data);
    }
}