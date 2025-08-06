<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\FolderModel;
use App\Models\ActivityLogsModel;
use App\Models\FileModel; // Tambahkan ini
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Exception; // Tambahkan ini untuk error handling yang lebih baik

class File extends BaseController
{
    use ResponseTrait;

    protected $fileModel;
    protected $activityLogsModel;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    public function __construct()
    {
        // Pastikan model dimuat di sini, ini sudah benar
        $this->activityLogsModel = new ActivityLogsModel();
        $this->fileModel = new FileModel(); // Lebih baik menggunakan namespace penuh jika tidak ada 'use'
    }

    // File.php - Perbaikan renameFile()
public function renameFile()
{
    // Cek jika request bukan AJAX
    if (!$this->request->isAJAX()) {
        // Menggunakan failForbidden() dari ResponseTrait untuk konsistensi
        return $this->failForbidden('Akses ditolak: Hanya melalui AJAX.');
    }

    $rules = [
        'file_id' => 'required|numeric',
        'new_name' => 'required|min_length[1]|max_length[255]'
    ];

    if (!$this->validate($rules)) {
        // Menggunakan failValidationErrors() dari ResponseTrait
        return $this->failValidationErrors($this->validator->getErrors());
    }

    $fileId = $this->request->getPost('file_id');
    $newName = $this->request->getPost('new_name');
    
    $userId = session()->get('user_id');

    if (!$userId) {
        // Menggunakan failUnauthorized() dari ResponseTrait
        return $this->failUnauthorized('Pengguna tidak terautentikasi.');
    }
    
    $file = $this->fileModel->find($fileId);

    if (!$file || $file['uploader_id'] != $userId) {
        // Menggunakan failForbidden() dari ResponseTrait
        return $this->failForbidden('Anda tidak memiliki hak untuk mengganti nama file ini.');
    }

    try {
        $updated = $this->fileModel->update($fileId, ['file_name' => $newName]);
        
        if ($updated) {
            $this->activityLogsModel->logActivity($userId, 'rename_file', 'file', $fileId, $newName);
            
            // Menggunakan respond() dari ResponseTrait
            return $this->respond([
                'status' => 'success', 
                'message' => 'Nama file berhasil diubah.'
            ]);
        } else {
            return $this->fail('Gagal mengubah nama file. Data tidak berubah.', 500);
        }
    } catch (Exception $e) {
        log_message('error', $e->getMessage());
        return $this->fail('Terjadi kesalahan server saat mengubah nama file.', 500);
    }
}


// File.php - Perbaikan deleteFile()
public function deleteFile()
{
    if (!$this->request->isAJAX()) {
        return $this->failForbidden('Akses ditolak: Hanya melalui AJAX.');
    }

    $rules = [
        'file_id' => 'required|numeric'
    ];

    if (!$this->validate($rules)) {
        return $this->failValidationErrors($this->validator->getErrors());
    }

    $fileId = $this->request->getPost('file_id');
    $userId = session()->get('user_id');

    if (!$userId) {
        return $this->failUnauthorized('Pengguna tidak terautentikasi.');
    }

    $file = $this->fileModel->find($fileId);

    if (!$file || $file['uploader_id'] != $userId) {
        return $this->failForbidden('Anda tidak memiliki hak untuk menghapus file ini.');
    }
    
    // Hapus file fisik
    $filePath = WRITEPATH . 'uploads/' . $file['file_path'];
    if (file_exists($filePath)) {
        unlink($filePath);
    } else {
        // Opsi: Beri log jika file fisik tidak ada, tapi tetap lanjutkan proses hapus dari DB
        log_message('warning', 'File fisik tidak ditemukan: ' . $filePath);
    }
    
    try {
        $deleted = $this->fileModel->delete($fileId);
        if ($deleted) {
            $this->activityLogsModel->logActivity($userId, 'delete_file', 'file', $fileId, $file['file_name']);
            return $this->respond([
                'status' => 'success', 
                'message' => 'File berhasil dihapus.'
            ]);
        } else {
            return $this->fail('Gagal menghapus file dari database.', 500);
        }
    } catch (Exception $e) {
        log_message('error', $e->getMessage());
        return $this->fail('Terjadi kesalahan server saat menghapus file.', 500);
    }
}
}