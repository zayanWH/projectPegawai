<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use App\Models\FolderModel; 

class Folder extends Controller
{
    use ResponseTrait;

    protected $folderModel;

    // Tambahkan konstruktor untuk memuat model
    public function __construct()
    {
        // Pastikan Anda memuat model di sini
        $this->folderModel = new \App\Models\FolderModel();
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
        $validationData = (array)$json;
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

    // Optional: Cek apakah user memiliki permission untuk menghapus folder
    // Misalnya hanya owner yang bisa menghapus
    /*
    $currentUserId = session()->get('user_id'); // Sesuaikan dengan session management Anda
    if ($folder['owner_id'] != $currentUserId) {
        return $this->fail('Anda tidak memiliki permission untuk menghapus folder ini.', 403);
    }
    */

    // Cek apakah folder memiliki subfolder atau file
    // $hasSubfolders = $this->folderModel->where('parent_id', $folderId)->countAllResults() > 0;
    
    // if ($hasSubfolders) {
    //     return $this->fail('Folder tidak dapat dihapus karena masih memiliki subfolder.', 400);
    // }

    // Optional: Cek apakah folder memiliki file
    // Jika Anda memiliki tabel files yang terkait dengan folder
    /*
    $fileModel = new \App\Models\FileModel();
    $hasFiles = $fileModel->where('folder_id', $folderId)->countAllResults() > 0;
    
    if ($hasFiles) {
        return $this->fail('Folder tidak dapat dihapus karena masih memiliki file.', 400);
    }
    */

    // Hapus folder dari database
    try {
        $result = $this->folderModel->delete($folderId);
        
        if ($result) {
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
    if (!$this->request->getMethod() === 'POST') {
        return $this->fail('Method tidak diizinkan.', 405);
    }

    $folderId = $this->request->getPost('folder_id');
    $newName = $this->request->getPost('new_name');

    // Validasi input dasar
    if (empty($folderId) || empty($newName)) {
        return $this->fail('ID folder dan nama baru harus diisi.', 400);
    }

    // Validasi menggunakan method dari model
    if (!$this->folderModel->validateRename(['name' => $newName])) {
        $validation = \Config\Services::validation();
        return $this->fail($validation->getErrors(), 400);
    }

    // Cek apakah folder exists
    $folder = $this->folderModel->find($folderId);
    if (!$folder) {
        return $this->fail('Folder tidak ditemukan.', 404);
    }

    // Update menggunakan method dari model
    try {
        $result = $this->folderModel->updateFolder($folderId, ['name' => trim($newName)]);
        
        if ($result) {
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
    } catch (\Exception $e) {
        return $this->fail($e->getMessage(), 409);
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