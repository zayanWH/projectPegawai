<?php

namespace App\Controllers;

use App\Models\FolderModel; // Pastikan Anda memiliki model FolderModel

class Staff extends BaseController
{
    public function dokumenStaff()
    {
        $folderModel = new FolderModel(); // Inisialisasi model Folder

        // Ambil semua personal folders untuk user yang sedang login
        $personalFolders = $folderModel->where('owner_id', session()->get('user_id'))
                                       ->where('folder_type', 'personal')
                                       ->findAll();

        // Hitung total folder (contoh sederhana: semua folder di tabel)
        // Jika Anda ingin menghitung hanya folder personal atau berdasarkan kriteria lain, sesuaikan kueri ini.
        $totalFolders = $folderModel->countAllResults(); // Menghitung total semua baris di tabel folders

        // Ambil file yang tidak terkait dengan folder (jika ada)
        $orphanFiles = $this->getOrphanFiles(); // Asumsi ada method ini di controller Anda

        $data = [
            'personalFolders' => $personalFolders,
            'folderId' => null, // Ini bisa disesuaikan jika Anda berada di dalam sub-folder
            'folderType' => null,
            'isShared' => null,
            'sharedType' => null,
            'orphanFiles' => $orphanFiles,
            'totalFolders' => $totalFolders // Teruskan jumlah total folder ke view
        ];

        return view('dokumenStaff', $data);
    }

    // ... method lain di controller Anda ...

    private function getOrphanFiles()
    {
        // Implementasi untuk mengambil file tanpa folder
        // Ini hanya contoh placeholder
        $fileModel = new \App\Models\FileModel(); // Asumsi Anda punya FileModel
        return $fileModel->where('folder_id IS NULL')->findAll();
    }
}