<?php

namespace App\Controllers;

use App\Models\FolderModel;
use App\Models\FileModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Exceptions\AccessDeniedException; // Tambahkan ini jika belum ada

class Staff extends BaseController
{
    protected $folderModel;
    protected $fileModel;

    public function __construct()
    {
        $this->folderModel = new FolderModel();
        $this->fileModel = new FileModel();
    }

    public function dokumenStaff()
    {
        $personalFolders = $this->folderModel->where('owner_id', session()->get('user_id'))
                                             ->where('folder_type', 'personal')
                                             ->findAll();

        $totalFolders = $this->folderModel->countAllResults();

        $orphanFiles = $this->getOrphanFiles(); 

        $data = [
            'personalFolders' => $personalFolders,
            'folderId' => null, 
            'folderType' => null,
            'isShared' => null,
            'sharedType' => null,
            'orphanFiles' => $orphanFiles,
            'totalFolders' => $totalFolders 
        ];

        return view('dokumenStaff', $data);
    }

    /**
     * Metode untuk menampilkan halaman wrapper preview file (iframe).
     * Dipanggil melalui rute staff/view-file/{fileId}.
     *
     * @param int $fileId ID dari file yang akan dilihat
     */
    public function viewFile($fileId)
    {
        // Ambil metadata file untuk memastikan file ada dan mendapatkan nama file
        $file = $this->fileModel->find($fileId);

        if (!$file) {
            throw PageNotFoundException::forPageNotFound('File yang diminta tidak ditemukan.');
        }

        // Anda bisa menambahkan logika otorisasi di sini juga
        $session = session();
        $userRole = $session->get('role');
        $userId = $session->get('user_id');

        if ($userRole === 'hrd') {
            // HRD punya akses mutlak, boleh lihat semua file
        } elseif ($file['uploader_id'] == $userId) {
            // Pemilik file boleh melihat file-nya sendiri
        } else {
            // Jika tidak diizinkan, arahkan ke halaman akses ditolak
            throw new AccessDeniedException('Anda tidak memiliki izin untuk melihat file ini.');
        }

        $data = [
            'fileId' => $fileId,
            'fileName' => $file['file_name'],
            // Anda bisa meneruskan data lain yang dibutuhkan view_file_wrapper
        ];

        return view('Staff/view_file_wrapper', $data);
    }

    /**
     * Metode untuk melayani (serve) file fisik dari server.
     * Dipanggil melalui rute staff/serve-file/{fileId}.
     *
     * @param int $fileId ID dari file yang diminta
     */
    public function serveFile($fileId)
    {
        $session = session();
        $userRole = $session->get('role');     // Asumsikan role pengguna disimpan di session
        $userId = $session->get('user_id');   // Asumsikan ID pengguna disimpan di session

        // 1. Ambil metadata file dari database menggunakan $fileId
        $file = $this->fileModel->find($fileId);

        // Jika file tidak ditemukan di database, lemparkan error 404
        if (!$file) {
            throw PageNotFoundException::forPageNotFound('File tidak ditemukan di database.');
        }

        // Logika Otorisasi:
        // HRD memiliki akses mutlak.
        // Pengguna lain hanya bisa melihat file mereka sendiri.
        if ($userRole === 'hrd') {
            // HRD memiliki akses penuh, tidak perlu pemeriksaan lebih lanjut
        } elseif ($file['uploader_id'] == $userId) {
            // Pengguna adalah pemilik file, izinkan akses
        } else {
            // Jika tidak ada kondisi di atas yang terpenuhi, tolak akses
            throw new AccessDeniedException('Anda tidak memiliki izin untuk melihat file ini.');
        }

        // 2. Buat jalur lengkap ke lokasi fisik file di server
        // ✅ PENTING: Perhatikan konsistensi 'server_file_name' di database Anda.
        // Jika ada subfolder seperti 'hrd_documents/', pastikan itu ada di sini.
        // Contoh: WRITEPATH . 'uploads/hrd_documents/' . $file['server_file_name_tanpa_path_depan']
        // Atau, jika server_file_name sudah termasuk subpath (misal: "hrd_documents/namafile.png")
        // maka cukup:
        $filePath = WRITEPATH . 'uploads/' . $file['server_file_name'];

        // 3. Periksa apakah file fisik benar-benar ada di server
        if (!file_exists($filePath)) {
            throw PageNotFoundException::forPageNotFound('File fisik tidak ditemukan di server: ' . $filePath);
        }

        // 4. Tentukan Content-Type dari file untuk memberitahu browser bagaimana menanganinya
        $mimeType = $file['file_type'] ?? mime_content_type($filePath); 
        if (!$mimeType) {
            $mimeType = 'application/octet-stream';
        }

        // 5. Set header HTTP yang diperlukan
        $this->response->setContentType($mimeType);
        $this->response->setHeader('Content-Disposition', 'inline; filename="' . basename($file['file_name']) . '"');
        $this->response->setHeader('Content-Length', filesize($filePath));

        // 6. Baca file dan kirimkan isinya ke browser
        readfile($filePath);

        exit();
    }

    private function getOrphanFiles()
    {
        return $this->fileModel->where('folder_id IS NULL')->findAll();
    }

    // ✅ Tambahkan method uploadFile jika belum ada, untuk menangani upload dari viewFolder.php
    // Ini adalah contoh placeholder, sesuaikan dengan logika upload Anda yang sebenarnya.
    public function uploadFile()
    {
        $validationRules = [
            'file_upload' => [
                'rules' => 'uploaded[file_upload]|max_size[file_upload,10240]|ext_in[file_upload,pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif]',
                'errors' => [
                    'uploaded' => 'Anda harus memilih file untuk diunggah.',
                    'max_size' => 'Ukuran file terlalu besar (maks 10MB).',
                    'ext_in'   => 'Format file tidak diizinkan. Hanya PDF, dokumen, spreadsheet, presentasi, dan gambar yang diperbolehkan.'
                ]
            ],
            'folder_id' => [
                'rules' => 'permit_empty|is_natural_no_zero',
                'errors' => [
                    'is_natural_no_zero' => 'ID folder tidak valid.'
                ]
            ]
        ];

        if (!$this->validate($validationRules)) {
            return $this->response->setJSON(['status' => 'error', 'message' => $this->validator->getErrors()]);
        }

        $uploadedFile = $this->request->getFile('file_upload');
        $folderId = $this->request->getPost('folder_id');
        $userId = session()->get('user_id'); // Pastikan user ID tersedia di session

        if (!$uploadedFile->isValid() || $uploadedFile->hasMoved()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal mengunggah file atau file sudah dipindahkan.']);
        }

        // Pastikan direktori uploads ada dan writable
        $uploadPath = WRITEPATH . 'uploads/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }
        
        // Buat nama file unik untuk disimpan di server
        $newName = $uploadedFile->getRandomName(); 
        $fileMovePath = $uploadPath . $newName;

        if ($uploadedFile->move($uploadPath, $newName)) {
            $data = [
                'folder_id'        => $folderId,
                'uploader_id'      => $userId,
                'file_name'        => $uploadedFile->getName(),
                'server_file_name' => $newName, // Simpan hanya nama unik, tanpa subfolder di sini untuk Staff
                'file_size'        => $uploadedFile->getSize(), // getSize() defaultnya byte
                'file_type'        => $uploadedFile->getMimeType(),
                'download_count'   => 0,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ];

            if ($this->fileModel->insert($data)) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'File berhasil diunggah!']);
            } else {
                // Hapus file yang sudah terupload jika gagal disimpan ke DB
                unlink($fileMovePath); 
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan data file ke database.']);
            }
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memindahkan file ke server.']);
        }
    }
}