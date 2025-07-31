<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\FolderModel;
use App\Models\FileModel;
use App\Models\RoleModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use App\Exceptions\AccessDeniedException;

class Staff extends BaseController
{
    protected $folderModel;
    protected $fileModel;
    protected $roleModel;
     protected $userModel;

    public function __construct()
    {
        $this->folderModel = new FolderModel();
        $this->fileModel = new FileModel();
        $this->roleModel = new RoleModel();
        $this->userModel = new \App\Models\UserModel();
    }

    /**
     * Method untuk menampilkan halaman root folder pribadi (personal)
     * atau isi dari folder pribadi tertentu.
     * @param int|null $folderId ID dari folder yang akan dilihat
     */
    public function personalFolder($folderId = null)
    {
        $userId = session()->get('user_id');

        // Ambil data subfolder dan file
        $subfolders = $this->folderModel
            ->where('owner_id', $userId)
            ->where('parent_id', $folderId)
            ->where('folder_type', 'personal')
            ->findAll();

        $files = $this->fileModel
            ->where('uploader_id', $userId)
            ->where('folder_id', $folderId)
            ->findAll();

        $data = [
            'folders' => $subfolders,
            'files' => $files,
            'currentFolderId' => $folderId,
            'currentFolderName' => 'Dokumen Pribadi',
            'breadcrumbs' => $this->getBreadcrumbs($folderId)
        ];

        if ($folderId) {
            $currentFolder = $this->folderModel->find($folderId);
            if ($currentFolder) {
                $data['currentFolderName'] = $currentFolder['name'];
            }
        }

        return view('dokumenStaff', $data);
    }

    /**
     * Method untuk menampilkan halaman root shared folder
     * atau isi dari shared folder tertentu, dengan otorisasi.
     * @param int|null $folderId ID dari shared folder yang akan dilihat
     */
    public function sharedFolder($folderId = null)
    {
        $session = session();
        $userId = $session->get('user_id');
        $userRole = $session->get('role_name');

        if ($folderId) {
            $currentFolder = $this->folderModel->find($folderId);
            if (!$currentFolder) {
                throw PageNotFoundException::forPageNotFound('Folder yang diminta tidak ditemukan.');
            }

            // Pengecekan otorisasi untuk shared folder
            if ($currentFolder['is_shared'] && $currentFolder['access_roles'] !== null) {
                $accessRoles = json_decode($currentFolder['access_roles'], true);
                if (!in_array($userRole, $accessRoles)) {
                    throw new AccessDeniedException('Anda tidak memiliki izin untuk mengakses folder ini.');
                }
            }

            $subfolders = $this->folderModel
                ->where('parent_id', $folderId)
                ->findAll();
            $files = $this->fileModel
                ->where('folder_id', $folderId)
                ->findAll();

            $data = [
                'sharedFolders' => $subfolders,
                'sharedFiles' => $files,
                'currentFolderId' => $folderId,
                'currentFolderName' => $currentFolder['name'],
                'userRoleName' => $userRole
            ];
        } else {
            $sharedFolders = $this->folderModel
                ->where('is_shared', 1)
                ->where('parent_id', null)
                ->findAll();

            $allowedFolders = [];
            foreach ($sharedFolders as $folder) {
                if ($folder['access_roles'] !== null) {
                    $accessRoles = json_decode($folder['access_roles'], true);
                    if (in_array($userRole, $accessRoles)) {
                        $allowedFolders[] = $folder;
                    }
                }
            }

            $data = [
                'sharedFolders' => $allowedFolders,
                'sharedFiles' => [],
                'currentFolderId' => null,
                'currentFolderName' => 'Dokumen Bersama',
                'userRoleName' => $userRole
            ];
        }

        return view('shared_folder', $data);
    }

    /**
     * Method untuk menampilkan isi shared folder tertentu.
     * Metode ini akan dipanggil dari tautan di halaman dokumen staff.
     * @param int $folderId ID dari shared folder yang akan dilihat
     */
    // app/Controllers/Staff.php

     public function viewSharedFolder(int $folderId = null)
{
    helper(['form', 'url']);

    $userId = session()->get('user_id');
    if (!$userId) {
        log_message('error', 'User not authenticated when trying to access shared folder ' . $folderId);
        throw new Exception('Anda tidak terautentikasi. Silakan login kembali.');
    }

    $user = $this->userModel
        ->select('users.id, users.name, roles.name as role_name')
        ->join('roles', 'roles.id = users.role_id', 'left')
        ->find($userId);

    $userRole = $user['role_name'] ?? 'Guest';

    log_message('debug', 'DEBUG Staff::viewSharedFolder: User ID: ' . $userId . ', Role: ' . $userRole . ', Attempting to view folder ID: ' . ($folderId ?? 'root'));

    $folder = null;
    $isRoot = ($folderId === null); // Tentukan apakah ini folder root

    if (!$isRoot) {
        $folder = $this->folderModel->getFolderWithOwner($folderId); // Mengambil folder dengan info owner
        if (!$folder) {
            log_message('error', 'Folder not found or not accessible for user ' . $userId . ' (role: ' . $userRole . '). Folder ID: ' . $folderId);
            throw new Exception('Folder tidak ditemukan atau tidak dapat diakses.');
        }
    } else {
        // Ini adalah folder root
        $folder = [
            'id' => null, // ID null untuk root
            'name' => 'Dokumen Bersama', // Nama untuk tampilan
            'owner_id' => null, // Root tidak punya owner spesifik dalam konteks ini
            'is_shared' => 1, // Root dokumen bersama dianggap 'shared' secara default
            'shared_type' => 'public', // Root bisa diatur sebagai public atau hanya terotentikasi
            'access_roles' => null // Atau json_encode(['Super Admin', 'Staff']) jika hanya role tertentu bisa melihat root ini
        ];
    }
    
    // Logika akses untuk folder saat ini (atau root)
    $isAuthorized = false;

    // Jika ini adalah folder root, dan diizinkan untuk semua user login atau role tertentu
    if ($isRoot) {
        // Asumsi: Root 'Dokumen Bersama' bisa diakses oleh semua user yang login
        // Anda bisa menambahkan logika role di sini jika diperlukan (misal: hanya Staff & Admin)
        if ($userId) { // Cukup pastikan user terautentikasi
            $isAuthorized = true;
            log_message('debug', 'Root folder diakses oleh user terautentikasi. Authorized.');
        }
    } else {
        // Logika akses untuk sub-folder
        if ($folder['owner_id'] == $userId) {
            $isAuthorized = true;
            log_message('debug', 'Folder ' . $folderId . ' diakses sebagai owner. Authorized.');
        } else if ($folder['is_shared'] == 1) {
            if ($folder['shared_type'] === 'public') {
                $isAuthorized = true;
                log_message('debug', 'Folder ' . $folderId . ' diakses karena public. Authorized.');
            } else if (!empty($folder['access_roles'])) {
                $accessRoles = json_decode($folder['access_roles'], true);
                if (is_array($accessRoles) && in_array($userRole, $accessRoles)) {
                    $isAuthorized = true;
                    log_message('debug', 'Folder ' . $folderId . ' diakses karena role ' . $userRole . ' ada di access_roles. Authorized.');
                }
            }
            if (!$isAuthorized) {
                log_message('critical', 'Access denied for folder ' . $folderId . '. Shared but user role ' . $userRole . ' not allowed. User ID: ' . $userId);
                throw new Exception('Anda tidak memiliki izin untuk mengakses folder ini (dibagikan ke peran tertentu).');
            }
        } else {
            log_message('critical', 'Access denied for folder ' . $folderId . '. Not owner and not shared. User role: ' . $userRole);
            throw new Exception('Anda tidak memiliki izin untuk mengakses folder ini.');
        }
    }

    if (!$isAuthorized) {
        log_message('critical', 'Final access check failed for folder ' . $folderId . '. User ID: ' . $userId . ', Role: ' . $userRole);
        throw new Exception('Anda tidak memiliki izin untuk mengakses folder ini.');
    }

    // Ambil subfolder dan file berdasarkan folder saat ini (atau null untuk root)
    $subfolders = $this->folderModel->getSubfolders($folder['id'], $userId, $userRole);
    $files = $this->fileModel->getFilesByFolder($folder['id'], $userId, $userRole);

    // Breadcrumbs
    $breadcrumbs = [];
    if (!$isRoot) {
        $breadcrumbs = $this->folderModel->getBreadcrumbs($folder['id']);
    }
    // // Tambahkan link "Kembali" ke root Dokumen Bersama
    // array_unshift($breadcrumbs, ['id' => null, 'name' => 'Dokumen Bersama']);


    $data = [
        'folder' => $folder,
        'breadcrumbs' => $breadcrumbs,
        'sharedFolders' => $subfolders, // Ganti 'subfolders' agar cocok dengan nama di view
        'sharedFiles' => $files,      // Ganti 'files' agar cocok dengan nama di view
        'owner_id' => $folder['owner_id'],
        'userRole' => $userRole,
        'currentFolderId' => $folderId, // Ini yang akan dikirim ke JS
        'title' => ($isRoot ? 'Dokumen Bersama' : 'Isi Folder: ' . $folder['name']),
    ];

    return view('Umum/viewsharedfolder', $data);
}

    /**
     * Fungsi pembantu untuk membuat breadcrumbs dari folder saat ini ke atas.
     * @param int|null $folderId ID folder saat ini.
     * @return array
     */
    private function getBreadcrumbs($folderId = null)
    {
        $breadcrumbs = [];
        $currentFolder = null;

        if ($folderId) {
            $currentFolder = $this->folderModel->find($folderId);
        }

        // Perbaiki loop: Pastikan $currentFolder valid sebelum masuk ke loop
        while ($currentFolder) {
            // Tambahkan pengecekan null di sini untuk keamanan ekstra
            if (!is_array($currentFolder) || !isset($currentFolder['name']) || !isset($currentFolder['id'])) {
                // Jika data folder rusak, hentikan loop
                break;
            }

            array_unshift($breadcrumbs, [
                'name' => $currentFolder['name'],
                'id' => $currentFolder['id']
            ]);

            // Ambil folder induknya dan pastikan hasilnya valid sebelum melanjutkan
            $parentFolderId = $currentFolder['parent_id'];
            $currentFolder = ($parentFolderId) ? $this->folderModel->find($parentFolderId) : null;
        }

        return $breadcrumbs;
    }

    // /**
    //  * Helper function to build breadcrumbs.
    //  * @param int|null $folderId
    //  * @return array
    //  */
    // private function getBreadcrumbs($folderId = null)
    // {
    //     $breadcrumbs = [];
    //     $currentFolder = null;

    //     if ($folderId) {
    //         $currentFolder = $this->folderModel->find($folderId);
    //     }

    //     while ($currentFolder) {
    //         array_unshift($breadcrumbs, [
    //             'name' => $currentFolder['name'],
    //             'id' => $currentFolder['id']
    //         ]);
    //         $currentFolder = $this->folderModel->find($currentFolder['parent_id']);
    //     }

    //     return $breadcrumbs;
    // }

    /**
     * Method untuk membuat folder baru.
     */
    public function createFolder()
{
    helper(['form', 'url']); // Pastikan helper form dan url di-load jika diperlukan

    // Ambil input JSON dari body request
    $input = $this->request->getJSON(true);

    $folderName = $input['name'] ?? null;
    $parentId = $input['parent_id'] ?? null;
    $folderType = $input['folder_type'] ?? 'shared'; // Default 'shared' jika tidak ada
    $isShared = $input['is_shared'] ?? 1; // Default 1 jika tidak ada

    // PENTING: owner_id HARUS diambil dari sesi user yang login, bukan dari input client
    $ownerId = session()->get('user_id');

    if (!$ownerId) {
        return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak terautentikasi.']);
    }

    // Rules untuk validasi
    $rules = [
        'name' => 'required|max_length[255]',
        // parent_id bisa null (root) atau integer
        'parent_id' => 'permit_empty|is_natural', 
        'folder_type' => 'required|in_list[personal,shared]',
        // is_shared bisa 0 atau 1
        'is_shared' => 'permit_empty|in_list[0,1]', 
    ];

    // Lakukan validasi terhadap input yang diterima dari JSON
    // Pastikan Anda memvalidasi variabel $folderName, $parentId, $folderType, $isShared yang sudah di-extract dari $input
    if (!$this->validateData($input, $rules)) { // Gunakan validateData untuk array
        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membuat folder: ' . $this->validator->listErrors()]);
    }

    $data = [
        'name' => $folderName,
        'owner_id' => $ownerId,
        'parent_id' => $parentId,
        'folder_type' => $folderType,
    ];

    // LOGIKA UNTUK FOLDER BERSAMA
    // Ini berlaku jika folder_type adalah 'shared' ATAU is_shared dikirim sebagai 1
    if ($folderType === 'shared' || $isShared == 1) {
        $data['is_shared'] = 1;

        // Dapatkan daftar role yang bisa diakses untuk staff (dari database atau hardcode jika konstan)
        // Di sini saya mengasumsikan 'Super Admin' dan 'Staff' adalah role yang bisa mengakses.
        // Anda mungkin perlu menyesuaikan ini berdasarkan kebutuhan Anda.
        $accessRoles = ['Super Admin', 'Staff']; 
        $data['access_roles'] = json_encode($accessRoles);
        $data['shared_type'] = 'read_write'; // Anda bisa membuat ini dinamis jika perlu
    } else {
        $data['is_shared'] = 0; // Pastikan default 0 jika bukan folder shared
        $data['shared_type'] = null;
        $data['access_roles'] = null;
    }


    if ($this->folderModel->insert($data)) {
        return $this->response->setJSON(['status' => 'success', 'message' => 'Folder berhasil dibuat!']);
    } else {
        // Log kesalahan model untuk debugging lebih lanjut
        log_message('error', 'Gagal menyimpan folder: ' . json_encode($this->folderModel->errors()));
        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan data folder ke database.']);
    }
}

    /**
     * Metode untuk mengunggah file.
     */
    public function uploadFile()
    {
        $rules = [
            'file' => [
                'rules' => 'uploaded[file]|max_size[file,10240]|ext_in[file,pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif]',
                'errors' => [
                    'uploaded' => 'Anda harus memilih file untuk diunggah.',
                    'max_size' => 'Ukuran file terlalu besar (maks 10MB).',
                    'ext_in' => 'Format file tidak diizinkan.'
                ]
            ],
            'parent_id' => 'permit_empty|is_natural_no_zero',
            'description' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()]);
        }

        $uploadedFile = $this->request->getFile('file');
        $folderId = $this->request->getPost('parent_id');
        $description = $this->request->getPost('description');
        $userId = session()->get('user_id');

        if ($folderId) {
            $parentFolder = $this->folderModel->find($folderId);
            if ($parentFolder && $parentFolder['is_shared']) {
                $userRole = session()->get('role_name');
                $accessRoles = json_decode($parentFolder['access_roles'], true);
                $sharedType = $parentFolder['shared_type'];

                if (!in_array($userRole, $accessRoles) || $sharedType === 'read') {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak memiliki izin untuk mengunggah file ke folder ini.']);
                }
            }
        }

        if (!$uploadedFile->isValid() || $uploadedFile->hasMoved()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal mengunggah file atau file sudah dipindahkan.']);
        }

        $uploadPath = WRITEPATH . 'uploads/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $newName = $uploadedFile->getRandomName();
        if ($uploadedFile->move($uploadPath, $newName)) {
            $data = [
                'folder_id' => $folderId,
                'uploader_id' => $userId,
                'file_name' => $uploadedFile->getName(),
                'server_file_name' => $newName,
                'file_size' => $uploadedFile->getSize(),
                'file_type' => $uploadedFile->getMimeType(),
                'description' => $description
            ];

            if ($this->fileModel->insert($data)) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'File berhasil diunggah!']);
            } else {
                unlink($uploadPath . $newName);
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan data file ke database.']);
            }
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memindahkan file ke server.']);
        }
    }

    /**
     * Metode untuk menampilkan halaman wrapper preview file (iframe).
     * @param int $fileId ID dari file yang akan dilihat
     */
    public function viewFile($fileId)
    {
        $file = $this->fileModel->find($fileId);

        if (!$file) {
            throw PageNotFoundException::forPageNotFound('File yang diminta tidak ditemukan.');
        }

        $session = session();
        $userRole = $session->get('role_name');
        $userId = $session->get('user_id');

        // Logic otorisasi diperluas untuk shared folder
        $isAuthorized = false;

        // Otorisasi berdasarkan role HRD
        if ($userRole === 'hrd') {
            $isAuthorized = true;
        }

        // Otorisasi berdasarkan kepemilikan
        if ($file['uploader_id'] == $userId) {
            $isAuthorized = true;
        }

        // Otorisasi berdasarkan shared folder
        if ($file['folder_id']) {
            $parentFolder = $this->folderModel->find($file['folder_id']);
            if ($parentFolder && $parentFolder['is_shared']) {
                $accessRoles = json_decode($parentFolder['access_roles'], true);
                if (in_array($userRole, $accessRoles)) {
                    $isAuthorized = true;
                }
            }
        }

        if (!$isAuthorized) {
            throw new AccessDeniedException('Anda tidak memiliki izin untuk melihat file ini.');
        }

        $data = [
            'fileId' => $fileId,
            'fileName' => $file['file_name'],
        ];

        return view('Staff/view_file_wrapper', $data);
    }

    /**
     * Metode untuk melayani (serve) file fisik dari server.
     * @param int $fileId ID dari file yang diminta
     */
    public function serveFile($fileId)
    {
        $session = session();
        $userRole = $session->get('role_name');
        $userId = $session->get('user_id');

        $file = $this->fileModel->find($fileId);

        if (!$file) {
            throw PageNotFoundException::forPageNotFound('File tidak ditemukan di database.');
        }

        $isAuthorized = false;

        if ($userRole === 'hrd') {
            $isAuthorized = true;
        } elseif ($file['uploader_id'] == $userId) {
            $isAuthorized = true;
        } elseif ($file['folder_id']) {
            $parentFolder = $this->folderModel->find($file['folder_id']);
            if ($parentFolder && $parentFolder['is_shared']) {
                $accessRoles = json_decode($parentFolder['access_roles'], true);
                if (in_array($userRole, $accessRoles)) {
                    $isAuthorized = true;
                }
            }
        }

        if (!$isAuthorized) {
            throw new AccessDeniedException('Anda tidak memiliki izin untuk melihat file ini.');
        }

        $filePath = WRITEPATH . 'uploads/' . $file['server_file_name'];

        if (!file_exists($filePath)) {
            throw PageNotFoundException::forPageNotFound('File fisik tidak ditemukan di server: ' . $filePath);
        }

        $mimeType = $file['file_type'] ?? mime_content_type($filePath);
        if (!$mimeType) {
            $mimeType = 'application/octet-stream';
        }

        $this->response->setContentType($mimeType);
        $this->response->setHeader('Content-Disposition', 'inline; filename="' . basename($file['file_name']) . '"');
        $this->response->setHeader('Content-Length', filesize($filePath));

        readfile($filePath);

        exit();
    }

    private function getOrphanFiles()
    {
        return $this->fileModel->where('folder_id IS NULL')->findAll();
    }

    // Ini adalah placeholder. Anda perlu mengimplementasikan logika sebenarnya
    // untuk menangani upload folder dan membuat struktur folder/file di database.
    public function uploadFromFolder()
    {
        // Placeholder
        return $this->response->setJSON(['status' => 'success', 'message' => 'Upload folder berhasil (placeholder)']);
    }
}