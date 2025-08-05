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

        // Pastikan pengguna sudah login
        if (!$userId) {
            // Sebaiknya redirect ke halaman login atau tampilkan pesan error
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses folder bersama.');
        }

        // Inisialisasi variabel untuk view
        $currentFolder = null;
        $allowedFolders = []; // Ini yang akan dikirim ke view
        $allowedFiles = [];   // Ini yang akan dikirim ke view
        $currentFolderName = 'Dokumen Bersama'; // Default untuk root

        // LOGIKA KETIKA AKSES FOLDER TERTENTU (BUKAN ROOT)
        if ($folderId) {
            $currentFolder = $this->folderModel->find($folderId);

            if (!$currentFolder) {
                // Log jika folder tidak ditemukan untuk debugging
                log_message('error', 'Folder ID ' . $folderId . ' not found for user ' . $userId . ' (Role: ' . $userRole . ').');
                throw PageNotFoundException::forPageNotFound('Folder yang diminta tidak ditemukan.');
            }

            // --- Pengecekan Otorisasi Kritis untuk FOLDER SAAT INI ---
            // Ini adalah cek pertama dan paling penting. Jika user tidak punya akses ke folder ini, langsung tolak.
            if ($currentFolder['is_shared'] && $currentFolder['access_roles'] !== null) {
                $accessRoles = json_decode($currentFolder['access_roles'], true);
                if (!is_array($accessRoles) || !in_array($userRole, $accessRoles)) {
                    // Log percobaan akses yang ditolak
                    log_message('critical', 'Access Denied: User ID ' . $userId . ' with role ' . $userRole . ' attempted to access shared folder ' . $folderId . ' (Access roles: ' . ($currentFolder['access_roles'] ?? 'N/A') . ').');
                    throw new AccessDeniedException('Anda tidak memiliki izin untuk mengakses folder ini.');
                }
            } else {
                // Jika folderId ada, tapi folder tersebut TIDAK di-shared (misalnya folder pribadi user lain)
                // maka user tidak boleh mengaksesnya melalui rute sharedFolder ini.
                // Asumsi: rute sharedFolder hanya untuk shared folders.
                // Jika folder ini adalah folder personal user yang login, izinkan.
                if ($currentFolder['owner_id'] != $userId) {
                    log_message('critical', 'Access Denied: User ID ' . $userId . ' (Role: ' . $userRole . ') attempted to access non-shared folder ' . $folderId . ' which is not theirs via shared route.');
                    throw new AccessDeniedException('Folder ini bukan folder bersama yang dapat Anda akses atau bukan milik Anda.');
                }
            }

            $currentFolderName = $currentFolder['name'];

            // Ambil subfolder di dalam currentFolder yang sudah diizinkan aksesnya
            $subfoldersRaw = $this->folderModel
                ->where('parent_id', $folderId)
                ->findAll();

            foreach ($subfoldersRaw as $subfolder) {
                // Jika subfolder ini juga shared, cek izinnya
                if ($subfolder['is_shared'] && $subfolder['access_roles'] !== null) {
                    $subfolderAccessRoles = json_decode($subfolder['access_roles'], true);
                    if (is_array($subfolderAccessRoles) && in_array($userRole, $subfolderAccessRoles)) {
                        $allowedFolders[] = $subfolder;
                    }
                } else {
                    // Jika subfolder tidak shared, ia akan mewarisi izin dari parent-nya.
                    // Karena kita sudah cek izin parent ($currentFolder), maka ini aman untuk ditambahkan.
                    // Ini penting agar subfolder pribadi di dalam folder shared tetap terlihat oleh user yang berhak.
                    $allowedFolders[] = $subfolder;
                }
            }

            // Ambil file di dalam currentFolder. Semua file di dalam folder yang sudah diizinkan boleh dilihat.
            $allowedFiles = $this->fileModel
                ->where('folder_id', $folderId)
                ->findAll();

        } else {
            // LOGIKA KETIKA AKSES ROOT DARI SHARED FOLDER (folderId is null)
            $topLevelSharedFolders = $this->folderModel
                ->where('is_shared', 1)
                ->where('parent_id', null) // Hanya folder top-level
                ->findAll();

            foreach ($topLevelSharedFolders as $folder) {
                if ($folder['access_roles'] !== null) {
                    $accessRoles = json_decode($folder['access_roles'], true);
                    if (is_array($accessRoles) && in_array($userRole, $accessRoles)) {
                        $allowedFolders[] = $folder;
                    }
                }
                // Jika access_roles null tapi is_shared = 1, ini perlu dipertimbangkan
                // Asumsi: Jika is_shared=1 tapi access_roles null, maka defaultnya tidak ada yang bisa akses
                // atau mungkin ini adalah "public shared" (shared_type = 'public') - sesuaikan logika Anda
                // jika ada shared_type 'public' yang harus terlihat oleh semua authenticated user.
            }
            // Di root shared, biasanya tidak ada file langsung yang ditampilkan.
            $allowedFiles = [];
        }

        $data = [
            'sharedFolders' => $allowedFolders, // Hanya folder yang diizinkan
            'sharedFiles' => $allowedFiles,     // Hanya file di dalam folder yang diizinkan (atau kosong di root)
            'currentFolderId' => $folderId,
            'currentFolderName' => $currentFolderName,
            'userRoleName' => $userRole // Penting untuk logika di view
        ];

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
            return redirect()->to(base_url('auth/login')); // Redirect ke login jika tidak terautentikasi
        }

        $user = $this->userModel
            ->select('users.id, users.name, roles.name as role_name')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->find($userId);

        $userRole = $user['role_name'] ?? 'Guest';

        log_message('debug', 'DEBUG Staff::viewSharedFolder: User ID: ' . $userId . ', Role: ' . $userRole . ', Attempting to view folder ID: ' . ($folderId ?? 'root'));

        $currentFolder = null;
        $isRoot = ($folderId === null);
        $effectiveSharedType = 'full'; // Default type for the current view.

        if (!$isRoot) {
            $currentFolder = $this->folderModel->getFolderWithOwner($folderId); // Mengambil folder dengan info owner
            if (!$currentFolder) {
                log_message('error', 'Folder not found or not accessible for user ' . $userId . ' (role: ' . $userRole . '). Folder ID: ' . $folderId);
                throw new Exception('Folder tidak ditemukan atau tidak dapat diakses.');
            }
        } else {
            // Ini adalah folder root 'Dokumen Bersama'
            $currentFolder = [
                'id' => null,
                'name' => 'Dokumen Bersama',
                'owner_id' => null,
                'is_shared' => 1,
                'shared_type' => 'full', // Default shared_type untuk root Dokumen Bersama (bisa disesuaikan jika root itu sendiri 'read')
                'access_roles' => null
            ];
        }

        // --- Logika Penentuan Effective Shared Type untuk Halaman Saat Ini ---
        // Jika folder saat ini adalah subfolder dari folder yang shared_type-nya 'read',
        // maka shared_type efektif untuk tampilan ini akan menjadi 'read'.
        // Kita akan mencari "ancestor" terdekat yang merupakan folder shared.
        
        $effectiveSharedTypeDetermined = false; // Flag untuk melacak apakah shared_type sudah ditemukan

        if (!$isRoot) {
            $currentParentId = $currentFolder['id'];
            // Loop ke atas melalui parent untuk menemukan folder shared terdekat
            while ($currentParentId !== null) {
                $ancestorFolder = $this->folderModel->find($currentParentId);
                if ($ancestorFolder && $ancestorFolder['is_shared'] == 1) {
                    if (isset($ancestorFolder['shared_type'])) {
                         $effectiveSharedType = $ancestorFolder['shared_type'];
                         $effectiveSharedTypeDetermined = true;
                         break; // Ditemukan ancestor shared, ambil shared_type-nya dan keluar loop
                    }
                }
                $currentParentId = $ancestorFolder['parent_id'] ?? null; // Lanjutkan ke parent berikutnya
            }
        } else {
            // Jika ini root 'Dokumen Bersama', shared_type-nya adalah 'full' secara default
            // Anda bisa mengubah ini jika root 'Dokumen Bersama' juga bisa menjadi 'read'
            $effectiveSharedType = 'full'; // Atau dari config jika 'public' berarti 'full'
            $effectiveSharedTypeDetermined = true;
        }

        // Jika setelah looping tidak ada ancestor shared yang ditemukan,
        // atau jika folder saat ini adalah folder pribadi yang tidak di-shared,
        // maka shared_type efektif adalah 'full'.
        if (!$effectiveSharedTypeDetermined) {
            $effectiveSharedType = 'full';
        }
        // Pastikan shared_type tetap 'full' jika folder tersebut owned by user sendiri
        // atau tidak di-share sama sekali.
        // HANYA jika folder current tidak shared (is_shared == 0) DAN dimiliki oleh user,
        // maka sharedType efektif-nya menjadi 'full' (user memiliki kontrol penuh atas foldernya sendiri)
        if (!$isRoot && $currentFolder['owner_id'] == $userId && $currentFolder['is_shared'] == 0) {
             $effectiveSharedType = 'full';
        }


        log_message('debug', 'Effective Shared Type for folder ' . ($folderId ?? 'root') . ': ' . $effectiveSharedType);

        // Logika akses untuk folder saat ini (atau root)
        $isAuthorized = false;

        // Jika ini adalah folder root, dan diizinkan untuk semua user login atau role tertentu
        if ($isRoot) {
            if ($userId) {
                $isAuthorized = true;
                log_message('debug', 'Root folder diakses oleh user terautentikasi. Authorized.');
            }
        } else {
            // Logika akses untuk sub-folder
            if ($currentFolder['owner_id'] == $userId) {
                $isAuthorized = true;
                log_message('debug', 'Folder ' . $folderId . ' diakses sebagai owner. Authorized.');
            } else if ($currentFolder['is_shared'] == 1) {
                if ($currentFolder['shared_type'] === 'public') {
                    $isAuthorized = true;
                    log_message('debug', 'Folder ' . $folderId . ' diakses karena public. Authorized.');
                } else if (!empty($currentFolder['access_roles'])) {
                    $accessRoles = json_decode($currentFolder['access_roles'], true);
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
        // PERBAIKAN DI SINI: Gunakan metode yang sudah ada di model Anda
        $subfolders = $this->folderModel->getSubfolders($currentFolder['id'], $userId, $userRole); 
        $files = $this->fileModel->getFilesByFolder($currentFolder['id'], $userId, $userRole);     

        // Breadcrumbs
        $breadcrumbs = [];
        if (!$isRoot) {
            $breadcrumbs = $this->getBreadcrumbs($currentFolder['id']);
        }

        $data = [
            'folder' => $currentFolder, // Menggunakan $currentFolder yang sudah ditentukan
            'breadcrumbs' => $breadcrumbs,
            'sharedFolders' => $subfolders,
            'sharedFiles' => $files,
            'owner_id' => $currentFolder['owner_id'],
            'userRole' => $userRole,
            'currentFolderId' => $folderId,
            'title' => ($isRoot ? 'Dokumen Bersama' : 'Isi Folder: ' . $currentFolder['name']),
            'currentFolderSharedType' => $effectiveSharedType, // INI YANG PENTING DIKIRIM KE VIEW
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
        $currentId = $folderId;

        while ($currentId) {
            $folder = $this->folderModel->find($currentId);
            if ($folder) {
                array_unshift($breadcrumbs, [
                    'name' => $folder['name'],
                    'id' => $folder['id']
                ]);
                $currentId = $folder['parent_id'] ?? null;
            } else {
                break; // Folder tidak ditemukan, hentikan
            }
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