<?php

namespace App\Controllers;

use App\Models\FolderModel;

class DashboardController extends BaseController
{
    public function index()
    {
        // Pastikan Anda sudah memiliki library atau helper untuk mendapatkan role user yang sedang login
        // Di sini saya asumsikan role user yang sedang login adalah 'Staff'
        // Jika Anda menggunakan library autentikasi, cara ini mungkin perlu disesuaikan.
        $userRole = 'Staff'; 

        $folderModel = new FolderModel();
        
        // Ambil data folder yang di-share ke role 'Staff'
        $sharedFolders = $folderModel->getSharedFoldersByRole($userRole);

        // Siapkan data untuk dikirim ke view
        $data = [
            'sharedFolders' => $sharedFolders,
            'userRole' => $userRole,
            'ownerRole' => 'Admin' // Contoh: Jabatan pengunggah. Anda bisa mendapatkan ini dari relasi tabel user.
        ];

        // Tampilkan view dengan data yang sudah disiapkan
        return view('dashboard/index', $data);
    }
}