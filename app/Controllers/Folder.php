<?php
namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use App\Models\FolderModel;

class Folder extends Controller
{
    use ResponseTrait;

    protected $folderModel;
    protected $activityLogsModel;

    // Tambahkan konstruktor untuk memuat model
    public function __construct()
    {
        // Pastikan Anda memuat model di sini
        $this->folderModel = new \App\Models\FolderModel();
        $this->activityLogsModel = new \App\Models\ActivityLogsModel(); // Memuat model ActivityLogsModel
        // Jika Anda memiliki model lain, bisa dimuat di sini juga, misalnya:
        // $this->fileModel = new \App\Models\FileModel();
    }

    public function create()
    {
        if (!$this->request->isAJAX()) {
            return $this->failForbidden('Akses ditolak: Hanya melalui AJAX.');
        }
        $json = $this->request->getJSON();
        if (empty($json)) {
            return $this->failValidationErrors(['data' => 'Tidak ada data JSON yang diterima.']);
        }
        $ownerId = session()->get('user_id');
        if (!$ownerId) {
            return $this->failUnauthorized('Pengguna tidak terautentikasi.');
        }
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'folder_type' => 'required|in_list[personal,shared]',
            'is_shared' => 'required|in_list[0,1]',
            'owner_id' => 'required|integer',
        ];

        if (isset($json->folder_type) && $json->folder_type === 'shared') {
            $rules['shared_type'] = 'required|in_list[full,read]';
            $rules['access_roles'] = 'permit_empty|array';
        } else {
            $rules['shared_type'] = 'permit_empty';
            $rules['access_roles'] = 'permit_empty';
        }
        $validationData = (array) $json;
        $validationData['owner_id'] = $ownerId;

        if (!$this->validate($rules, [], $validationData)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $dataToSave = [
            'name' => $json->name,
            'folder_type' => $json->folder_type,
            'is_shared' => $json->is_shared,
            'shared_type' => ($json->folder_type === 'shared') ? $json->shared_type : null,
            'owner_id' => $ownerId,
            'access_roles' => null,
        ];

        if ($json->folder_type === 'shared' && isset($json->access_roles) && is_array($json->access_roles)) {
            $dataToSave['access_roles'] = json_encode($json->access_roles);
        }

        $folderModel = new FolderModel();

        try {
            if ($folderModel->insert($dataToSave)) {
                // Setelah insert, buat folder fisik di server
                $newFolderId = $folderModel->getInsertID();
                $relativePath = $folderModel->getFolderPath($newFolderId);
                log_message('debug', 'getFolderPath menghasilkan: ' . $relativePath);
                $folderPath = WRITEPATH . 'uploads/' . $relativePath;
                log_message('debug', 'Akan membuat folder di: ' . $folderPath);
                if (!is_dir($folderPath)) {
                    if (!mkdir($folderPath, 0777, true)) {
                        log_message('error', 'mkdir gagal untuk: ' . $folderPath);
                        return $this->fail('Gagal membuat folder fisik di server: ' . $folderPath, 500);
                    } else {
                        log_message('debug', 'mkdir sukses untuk: ' . $folderPath);
                    }
                }

                // 🔥 LOG ACTIVITY - FOLDER CREATED
                // KODE DIPERBARUI: Menambahkan nama folder ($json->name) ke log
                $this->activityLogsModel->logActivity($ownerId, 'create_folder', 'folder', $newFolderId, $json->name);
                return $this->respondCreated(['status' => 'success', 'message' => 'Folder berhasil dibuat.']);
            } else {
                $errors = $folderModel->errors();
                return $this->fail('Gagal membuat folder: ' . implode(', ', $errors), 500);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error creating folder: ' . $e->getMessage());
            return $this->fail('Terjadi kesalahan server saat membuat folder.', 500);
        }
    }

    public function delete()
    {
        if (!$this->request->getMethod() === 'POST') {
            return $this->fail('Method tidak diizinkan.', 405);
        }

        // Ambil user_id dari session untuk activity log
        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->failUnauthorized('Pengguna tidak terautentikasi.');
        }

        $folderId = $this->request->getPost('folder_id');

        // Validasi input dasar
        if (empty($folderId)) {
            return $this->fail('ID folder harus diisi.', 400);
        }

        // Cek apakah folder exists
        $folder = $this->folderModel->find($folderId);
        if (!$folder) {
            return $this->fail('Folder tidak ditemukan.', 404);
        }

        try {
            $result = $this->folderModel->delete($folderId);

            if ($result) {
                // Log activity sekarang bisa dipanggil dengan benar
                $this->activityLogsModel->logActivity(
                    $userId,
                    'delete_folder',
                    'folder',
                    $folderId,
                    $folder['name']
                );

                return $this->respond([
                    'status' => 'success',
                    'message' => 'Folder berhasil dihapus.',
                    'data' => [
                        'deleted_id' => $folderId,
                        'deleted_name' => $folder['name']
                    ]
                ]);
            } else {
                return $this->fail('Gagal menghapus folder. Silakan coba lagi.', 500);
            }
        } catch (\Exception $e) {
            return $this->fail('Terjadi kesalahan saat menghapus folder: ' . $e->getMessage(), 500);
        }
    }

    public function rename()
    {
        // Validasi request method
        if ($this->request->getMethod() !== 'POST') {
            return $this->fail('Method tidak diizinkan.', 405);
        }

        // Ambil user_id dari session untuk activity log
        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->failUnauthorized('Pengguna tidak terautentikasi.');
        }

        $folderId = $this->request->getPost('folder_id');
        $newName = $this->request->getPost('new_name');

        // Validasi input dasar
        if (empty($folderId) || empty($newName)) {
            return $this->fail('ID folder dan nama baru harus diisi.', 400);
        }

        // Validasi nama folder
        $validation = \Config\Services::validation();
        $validation->setRules([
            'new_name' => [
                'rules' => 'required|min_length[1]|max_length[255]|regex_match[/^[a-zA-Z0-9\s\-_\.]+$/]',
                'errors' => [
                    'required' => 'Nama folder harus diisi.',
                    'min_length' => 'Nama folder minimal 1 karakter.',
                    'max_length' => 'Nama folder maksimal 255 karakter.',
                    'regex_match' => 'Nama folder hanya boleh mengandung huruf, angka, spasi, tanda hubung, underscore, dan titik.'
                ]
            ]
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->fail($validation->getErrors(), 400);
        }

        // Cek apakah folder exists
        $folder = $this->folderModel->find($folderId);
        if (!$folder) {
            return $this->fail('Folder tidak ditemukan.', 404);
        }

        // 🔥 KODE DIPERBARUI: Ambil nama lama SEBELUM diperbarui
        $oldName = $folder['name'];

        // Cek apakah nama sudah digunakan di folder yang sama level
        $existingFolder = $this->folderModel->where('name', trim($newName))
            ->where('parent_id', $folder['parent_id'])
            ->where('owner_id', $folder['owner_id'])
            ->where('id !=', $folderId)
            ->first();

        if ($existingFolder) {
            return $this->fail('Nama folder sudah digunakan di lokasi yang sama.', 409);
        }

        // Update nama folder
        $updateData = [
            'name' => trim($newName),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $result = $this->folderModel->update($folderId, $updateData);

        if ($result) {
            // 🔥 KODE DIPERBARUI: Siapkan detail log dengan nama lama dan baru
            $logDetails = [
                'old_name' => $oldName,
                'new_name' => $newName,
            ];

            // 🔥 KODE DIPERBARUI: Panggil logActivity dengan parameter nama baru dan details
            $this->activityLogsModel->logActivity(
                $userId,
                'rename_folder',
                'folder',
                $folderId,
                $newName, // Mencatat nama baru di kolom target_name
                $logDetails // Mengirim detail nama lama dan baru
            );

            // Ambil data folder yang sudah diupdate
            $updatedFolder = $this->folderModel->find($folderId);

            return $this->respond([
                'status' => 'success',
                'message' => 'Nama folder berhasil diubah.',
                'data' => [
                    'id' => $updatedFolder['id'],
                    'name' => $updatedFolder['name'],
                    'updated_at' => $updatedFolder['updated_at']
                ]
            ]);
        } else {
            return $this->fail('Gagal mengubah nama folder. Silakan coba lagi.', 500);
        }
    }

    /**
     * Download folder sebagai zip
     */
    public function download($folderId)
    {
        $folder = $this->folderModel->find($folderId);
        if (!$folder) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Folder tidak ditemukan');
        }
        // Dapatkan path folder relatif dari root uploads
        $relativePath = $this->folderModel->getFolderPath($folderId);
        $folderPath = WRITEPATH . 'uploads/' . $relativePath;
        if (!is_dir($folderPath)) {
            return $this->response->setStatusCode(404)->setBody('Folder tidak ditemukan di server.');
        }
        // Buat file zip sementara
        $zip = new \ZipArchive();
        $zipFile = tempnam(sys_get_temp_dir(), 'zip');
        if ($zip->open($zipFile, \ZipArchive::CREATE) !== TRUE) {
            return $this->response->setStatusCode(500)->setBody('Gagal membuat file zip');
        }
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folderPath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        $fileCount = 0;
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relative = substr($filePath, strlen($folderPath) + 1);
                $zip->addFile($filePath, $relative);
                $fileCount++;
            }
        }
        $zip->close();
        if ($fileCount === 0) {
            @unlink($zipFile);
            return $this->response->setStatusCode(400)->setBody('Folder kosong, tidak ada file untuk di-download.');
        }
        if (!file_exists($zipFile)) {
            return $this->response->setStatusCode(500)->setBody('Gagal membuat file zip.');
        }
        $zipName = $folder['name'] . '-' . date('Ymd_His') . '.zip';
        $response = $this->response->download($zipName, file_get_contents($zipFile));
        @unlink($zipFile);
        return $response;
    }
}