<?php

namespace App\Controllers;

use App\Models\FolderModel; // Pastikan Anda mengimpor FolderModel
use App\Models\FileModel;   // Pastikan Anda mengimpor FileModel
use App\Models\UserModel;   // Pastikan Anda mengimpor UserModel
use CodeIgniter\Controller; // Jika DokumenControllerSPV bukan turunan BaseController
use CodeIgniter\Session\Session; // Import Session class jika belum
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\I18n\Time;
use CodeIgniter\Database\RawSql;

class DokumenControllerDireksi extends BaseController
{

    protected $folderModel;
    protected $fileModel;
    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->folderModel = new FolderModel();
        $this->fileModel = new FileModel();
        $this->userModel = new UserModel();
        $this->session = \Config\Services::session();
        $this->roleModel = new \App\Models\RoleModel();
    }

    public function uploadFile()
    {
        $userId = $this->session->get('user_id');

        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized. User not logged in.']);
        }

        $validationRule = [
            'file_upload' => [
                'label' => 'File',
                'rules' => 'uploaded[file_upload]|max_size[file_upload,10240]|ext_in[file_upload,pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif]',
                'errors' => [
                    'uploaded' => 'Harus ada file yang diupload.',
                    'max_size' => 'Ukuran file terlalu besar (maks 10MB).',
                    'ext_in' => 'Format file tidak didukung.',
                ],
            ],
            'folder_id' => [
                'label' => 'Folder ID',
                'rules' => 'permit_empty|is_natural_no_zero',
            ],
        ];

        if (!$this->validate($validationRule)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $uploadedFile = $this->request->getFile('file_upload');
        $folderId = $this->request->getPost('folder_id');

        if ($uploadedFile->isValid() && !$uploadedFile->hasMoved()) {
            // Ambil info SEBELUM memindahkan file
            $fileMimeType = $uploadedFile->getMimeType();
            $fileSize = $uploadedFile->getSize();
            $fileName = $uploadedFile->getName();
            $newName = $uploadedFile->getRandomName();

            $targetDirectory = WRITEPATH . 'uploads/';

            if (!is_dir($targetDirectory)) {
                mkdir($targetDirectory, 0777, true);
            }

            if ($uploadedFile->move($targetDirectory, $newName)) {
                $data = [
                    'folder_id' => empty($folderId) ? null : $folderId,
                    'uploader_id' => $userId,
                    'file_name' => $fileName,
                    'file_path' => $newName,
                    'file_size' => $fileSize,
                    'file_type' => $fileMimeType,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                if ($this->fileModel->insert($data)) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'File berhasil diunggah.'
                    ]);
                } else {
                    // Hapus file jika insert DB gagal
                    unlink($targetDirectory . $newName);
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Gagal menyimpan data file ke database.',
                        'errors' => $this->fileModel->errors()
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal memindahkan file yang diunggah.',
                    'errors' => $uploadedFile->getErrorString() . '(' . $uploadedFile->getError() . ')'
                ]);
            }
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'File tidak valid atau sudah dipindahkan.',
            ]);
        }
    }

    public function uploadFromFolder()
    {
        $userId = $this->session->get('user_id');
        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $file = $this->request->getFile('file');
        $relativePath = $this->request->getPost('relativePath');
        $parentIdPost = $this->request->getPost('parent_id');
        $rootParentId = ($parentIdPost === 'null' || $parentIdPost === null || $parentIdPost === '') ? null : $parentIdPost;

        if (!$file || !$file->isValid() || empty($relativePath)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'File atau path tidak valid.'], 400);
        }

        $pathParts = explode('/', $relativePath);
        $fileName = array_pop($pathParts);
        $folderPath = implode('/', $pathParts);

        // Cari atau buat folder tujuan
        $targetFolderId = $this->folderModel->findOrCreateByPath($folderPath, $rootParentId, $userId);

        if ($targetFolderId === null) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membuat struktur folder di server.'], 500);
        }

        // Ambil info SEBELUM memindahkan file
        $fileMimeType = $file->getMimeType();
        $fileSize = $file->getSize();
        $newName = $file->getRandomName();

        // Simpan file
        if ($file->move(WRITEPATH . 'uploads', $newName)) {
            $data = [
                'folder_id' => $targetFolderId,
                'uploader_id' => $userId,
                'file_name' => $fileName,
                'file_path' => $newName,
                'file_size' => $fileSize,
                'file_type' => $fileMimeType,
            ];
            $this->fileModel->insert($data);
            return $this->response->setJSON(['status' => 'success']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memindahkan file.'], 500);
    }

    public function dashboard()
    {
        $session = session();
        $userId = $session->get('user_id'); // User ID yang sedang login (tetap diambil untuk personal folder jika ada)
        $userRole = $session->get('role'); // Nama role user yang sedang login

        // Pastikan user sudah login
        if (!$userId || !$userRole) {
            return redirect()->to('/login')->with('error', 'Silakan login untuk mengakses dashboard.');
        }

        $folderModel = new FolderModel();
        $fileModel = new FileModel();
        $userModel = new UserModel();

        // --- Tentukan role_id yang ingin difilter (Staff = 6) ---
        // Anda bisa langsung menetapkan nilai ini jika role 'Staff' selalu ID 6.
        // Atau, jika role ID bisa berubah, Anda bisa mencari ID role 'Staff' dari tabel roles.
        $staffRoleId = 3; // Mengasumsikan role_id untuk Staff adalah 6

        // Dapatkan semua ID user yang memiliki role_id = 6 (Staff)
        $staffUserIds = $userModel->select('id')->where('role_id', $staffRoleId)->findAll();
        $staffUserIds = array_column($staffUserIds, 'id');

        // Jika tidak ada user Staff, set array kosong untuk mencegah error query IN ()
        if (empty($staffUserIds)) {
            $staffUserIds = [0]; // Memberikan nilai default agar query WHERE IN tidak kosong
        }

        // --- Hitung Total Folder berdasarkan role_id = 6 ---
        $totalFolders = $folderModel->whereIn('owner_id', $staffUserIds)->countAllResults();

        // --- Hitung Total File berdasarkan role_id = 6 ---
        $totalFiles = $fileModel->whereIn('uploader_id', $staffUserIds)->countAllResults();

        // --- Ambil Tanggal Terakhir Upload berdasarkan role_id = 6 ---
        // Folder
        $latestFolderUpload = $folderModel->selectMax('created_at')
            ->whereIn('owner_id', $staffUserIds)
            ->first();
        $latestFolderDate = $latestFolderUpload['created_at'] ?? null;

        // File
        $latestFileUpload = $fileModel->selectMax('created_at')
            ->whereIn('uploader_id', $staffUserIds)
            ->first();
        $latestFileDate = $latestFileUpload['created_at'] ?? null;

        // Tentukan tanggal upload paling terbaru dari kedua jenis item
        $latestUploadDate = null;
        if ($latestFolderDate && $latestFileDate) {
            $latestUploadDate = (strtotime($latestFolderDate) > strtotime($latestFileDate)) ? $latestFolderDate : $latestFileDate;
        } elseif ($latestFolderDate) {
            $latestUploadDate = $latestFolderDate;
        } elseif ($latestFileDate) {
            $latestUploadDate = $latestFileDate;
        }

        // Format tanggal untuk tampilan
        $formattedLatestUpload = $latestUploadDate ? date('d M Y', strtotime($latestUploadDate)) : 'Belum ada upload';

        // --- Ambil 10 Item Terbaru (file dan folder) berdasarkan role_id = 6 ---
        // Folders
        $folders = $folderModel->select("id, name, created_at, owner_id as uploader_id, 'folder' as type")
            ->whereIn('owner_id', $staffUserIds)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Files
        $files = $fileModel->select("id, file_name as name, created_at, uploader_id, 'file' as type")
            ->whereIn('uploader_id', $staffUserIds)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $recentItems = array_merge($folders, $files);

        // Urutkan gabungan item berdasarkan tanggal pembuatan
        usort($recentItems, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        // Ambil hanya 10 item teratas
        $recentItems = array_slice($recentItems, 0, 10);

        // --- Ambil semua personal folders untuk user yang sedang login (Tidak berubah, tetap personal) ---
        $personalFolders = $folderModel->where('owner_id', $userId)
            ->where('folder_type', 'personal')
            ->findAll();

        // --- Ambil file yang tidak terkait dengan folder (orphan files) oleh user Staff ---
        $orphanFiles = $fileModel->where('folder_id IS NULL')
            ->whereIn('uploader_id', $staffUserIds) // Pastikan ini juga difilter
            ->findAll();

        $data = [
            'personalFolders' => $personalFolders,
            'folderId' => null, // Sesuaikan jika ada logika untuk ini
            'folderType' => null, // Sesuaikan jika ada logika untuk ini
            'isShared' => null, // Sesuaikan jika ada logika untuk ini
            'sharedType' => null, // Sesuaikan jika ada logika untuk ini
            'orphanFiles' => $orphanFiles,
            'totalFolders' => $totalFolders,
            'totalFiles' => $totalFiles,
            'latestUploadDate' => $formattedLatestUpload,
            'recentItems' => $recentItems,
            'currentRoleName' => $userRole
        ];

        return view('Direksi/dashboard', $data);
    }

    public function dokumenDireksi()
    {
        $userId = $this->session->get('user_id');
        $userRoleId = $this->session->get('role_id');
        $userRoleName = $this->session->get('role_name');

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses dokumen pribadi Anda.');
        }

        // Ambil ID peran HRD dan Manager dari database
        $hrdRole = $this->roleModel->where('name', 'HRD')->first();
        $hrdRoleId = $hrdRole ? $hrdRole['id'] : null;

        $direksiRole = $this->roleModel->where('name', 'Direksi')->first();
        $direksiRoleId = $direksiRole ? $direksiRole['id'] : null;

        // Ambil semua ID user yang memiliki peran HRD
        $hrdUserIds = [];
        if ($hrdRoleId) {
            $hrdUsers = $this->userModel->where('role_id', $hrdRoleId)->findAll();
            $hrdUserIds = array_column($hrdUsers, 'id');
        }

        // --- Mulai Builder Query untuk Mengambil Folder ---
        $builder = $this->folderModel
            ->select('folders.*, owner_user.name as owner_display, owner_role.name as owner_role_name')
            // ✅ Perbaikan: Gunakan alias yang unik untuk tabel users dan roles
            ->join('users owner_user', 'owner_user.id = folders.owner_id', 'left')
            ->join('roles owner_role', 'owner_role.id = folders.owner_role', 'left')
            ->where('folders.parent_id', NULL);

        $builder->groupStart();
        // Kondisi 1: Folder personal milik Manajer sendiri
        $builder->where('folders.owner_id', $userId);

        // Kondisi 2: Folder yang dibuat HRD DAN owner_role-nya adalah Manajer
        $builder->orGroupStart()
            ->whereIn('folders.owner_id', $hrdUserIds)
            ->where('folders.owner_role', $direksiRoleId)
            ->groupEnd();

        // Kondisi 3: Folder yang di-share ke peran Manajer
        $builder->orGroupStart()
            ->where('folders.is_shared', 1)
            ->where(new RawSql("JSON_CONTAINS(folders.access_roles, '\"{$userRoleId}\"')"))
            ->groupEnd();

        // Kondisi 4: Folder public
        $builder->orWhere('folders.folder_type', 'public');
        $builder->groupEnd();

        $personalFolders = $builder->findAll();

        $orphanFiles = $this->fileModel
            ->where('uploader_id', $userId)
            ->where('folder_id', NULL)
            ->orderBy('file_name', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Dokumen Pribadi Saya (Manager)',
            'personalFolders' => $personalFolders,
            'orphanFiles' => $orphanFiles,
            'currentFolderId' => null,
            'currentUserId' => $userId,
            'userRoleName' => $userRoleName,
        ];

        return view('Direksi/dokumenDireksi', $data);
    }

    public function dokumenBersama()
    {
        // dd("Controller Direksi Reached!"); // <--- HAPUS BARIS INI!

        $session = session();
        $userId = $session->get('user_id');
        $roleId = $session->get('role_id');

        // Pastikan pengguna login
        if (!$userId) {
            log_message('warning', 'CONTROLLER DIREKSI DEBUG: User ID tidak ditemukan di sesi. Redirecting ke login.');
            return redirect()->to(base_url('login'));
        }

        // Ambil nama peran berdasarkan roleId
        $userRoleData = $this->roleModel->find($roleId); // Menggunakan $this->roleModel
        $userRoleName = $userRoleData['name'] ?? 'Unknown';

        log_message('debug', 'CONTROLLER DIREKSI DEBUG: Memasuki DokumenControllerDireksi::dokumenBersama().');
        log_message('debug', 'CONTROLLER DIREKSI DEBUG: User ID: ' . $userId . ', Role ID: ' . $roleId . ', Nama Role: ' . $userRoleName);

        // Panggil method untuk mendapatkan folder bersama dari model
        log_message('debug', 'CONTROLLER DIREKSI DEBUG: Memanggil FolderModel->getSharedFoldersForUser() dengan User ID: ' . $userId . ' dan Nama Role: ' . $userRoleName);
        $sharedFolders = $this->folderModel->getSharedFoldersForUser($userId, $userRoleName); // Menggunakan $this->folderModel

        log_message('debug', 'CONTROLLER DIREKSI DEBUG: Data sharedFolders yang diterima dari FolderModel: ' . json_encode($sharedFolders));

        // Perhatikan: Jika Anda memiliki logika tambahan untuk memproses $sharedFolders di controller,
        // seperti menambahkan kolom 'access_roles' = '[]' jika null, itu juga harus ada di sini.
        // Contoh (sesuaikan jika ada di DokumenControllerStaff sebelumnya):
        foreach ($sharedFolders as &$folder) {
            if ($folder['access_roles'] !== null && !json_decode($folder['access_roles'])) {
                $folder['access_roles'] = '[]';
            }
            // Anda juga mungkin ingin menambahkan owner_name dan owner_role dari model jika belum lengkap
            // Ini seharusnya sudah datang dari model, tapi cek lagi jika perlu.
        }
        unset($folder); // Penting untuk memutuskan referensi terakhir

        // Persiapan data untuk view
        $data = [
            'title' => 'Dokumen Bersama',
            'sharedFolders' => $sharedFolders,
            'sharedFiles' => [], // Sesuaikan jika Anda juga menampilkan file
            'currentFolderId' => null, // Untuk root level
            'currentUserId' => $userId,
            'userRoleName' => $userRoleName,
            'breadcrumbs' => []
        ];

        log_message('debug', 'CONTROLLER DIREKSI DEBUG: Data akhir yang akan dikirim ke view dokumenBersama: ' . json_encode($data));

        return view('Umum/dokumenBersama', $data); // Sesuaikan dengan path yang Anda berikan
    }


    public function dokumenUmum()
    {
        $hrdDocumentModel = new \App\Models\HrdDocumentModel();
        $data['documents'] = $hrdDocumentModel->getByParent(null); // Mengambil dokumen root level
        $data['parent_id'] = null;
        return view('Umum/dokumenUmum', $data);
    }

    public function viewFolder($folderId = null)
    {
        // Cek folder ID
        if ($folderId === null) {
            throw PageNotFoundException::forPageNotFound('Folder ID tidak ditentukan.');
        }

        $userId = $this->session->get('user_id');
        $userRoleId = $this->session->get('role_id'); // Ambil role_id dari sesi
        $userRoleName = $this->session->get('role_name');// Fallback jika tidak ditemukan

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses folder.');
        }

        $currentFolder = $this->folderModel->find($folderId);

        if (!$currentFolder) {
            throw PageNotFoundException::forPageNotFound('Folder tidak ditemukan.');
        }

        $ownerRoleId = $currentFolder['owner_role'] ?? null;
        $hasAccess = false;
        $canManageFolder = false;

        // --- LOGIKA OTORISASI UNIVERSAL ---

        // Kondisi 1: Pengguna adalah pemilik folder itu sendiri, tidak peduli folder_type-nya apa
        if ((int) $currentFolder['owner_id'] === (int) $userId) {
            $hasAccess = true;
            $canManageFolder = true; // Pemilik selalu bisa manage
        }
        // Kondisi 2: Folder dibagikan (is_shared = 1) dan peran pengguna ada di access_roles
        else if ((int) $currentFolder['is_shared'] === 1) {
            $accessRoles = json_decode($currentFolder['access_roles'] ?? '[]', true);
            $canManageFolder = true;

            // Pastikan semua role ID di access_roles adalah string untuk perbandingan yang konsisten
            $stringAccessRoles = is_array($accessRoles) ? array_map('strval', $accessRoles) : [];

            // Cek apakah userRoleId (sebagai string) ada di access_roles
            if (in_array((string) $userRoleId, $stringAccessRoles)) {
                $hasAccess = true;

                // Logika canManageFolder untuk shared folder
                // Asumsi: shared_type 'write' atau 'full_access' memungkinkan manajemen
                if ($currentFolder['shared_type'] === 'write' || $currentFolder['shared_type'] === 'full_access') {
                    $canManageFolder = true;
                }
            }
        }
        // Kondisi 3: Folder bersifat public
        else if ($currentFolder['folder_type'] === 'public') {
            $hasAccess = true;
            $canManageFolder = false; // Public folder tidak bisa dimanage oleh semua orang
        }

        // Jika tidak ada akses, redirect ke halaman dokumen utama role tersebut
        if (!$hasAccess) {
            // Redirect ke halaman dokumen utama SPV
            return redirect()->to(base_url('supervisor/dokumen-supervisor'))->with('error', 'Anda tidak memiliki akses ke folder ini.');
        }

        // --- AKHIR LOGIKA OTORISASI ---

        // Mengambil data untuk folder dan file
        // Menggunakan $userRoleId karena fungsi model membutuhkan ID peran (integer)
        $subFolders = $this->folderModel->getSubfoldersWithDetails($folderId, $userId, $userRoleId);
        $filesInFolder = $this->fileModel->getFilesByFolderWithUploader($folderId);
        $breadcrumbs = $this->folderModel->getBreadcrumbs($folderId);

        // Menyesuaikan URL breadcrumbs untuk Direksi
        foreach ($breadcrumbs as &$crumb) {
            $crumb['url'] = base_url('direksi/folder/' . $crumb['id']);
        }
        unset($crumb); // Sangat penting untuk unset reference setelah loop

        $data = [
            'title'              => 'Folder: ' . $currentFolder['name'],
            'folderName'         => $currentFolder['name'],
            'folderId'           => $currentFolder['id'],
            'isShared'           => (bool) $currentFolder['is_shared'],
            'sharedType'         => $currentFolder['shared_type'],
            'folderType'         => $currentFolder['folder_type'],
            'subFolders'         => $subFolders,
            'filesInFolder'      => $filesInFolder,
            'breadcrumbs'        => $breadcrumbs,
            'isStaffFolder'      => false,
            'isSupervisorFolder' => false,
            'isManagerFolder'    => false,// Menandakan ini adalah folder untuk Direksi
            'canDireksiFolder'    => $canManageFolder,
            'userRoleName'       => $userRoleName,
        ];

        return view('Direksi/viewFolder', $data);
    }

    public function dokumenStaffUntukDireksi()
    {
        $userId = $this->session->get('user_id');
        $roleId = $this->session->get('role_id');

        // Pastikan login dan role adalah Manager (role_id 4)
        if (!$userId || $roleId != 3) { // <-- Perbaikan di sini
            return redirect()->to(base_url('dashboard'))->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // Ambil SEMUA user dengan role Staff (asumsi role_id 6)
        $staffUsers = $this->userModel->where('role_id', 6)->findAll();
        $staffUserIds = array_column($staffUsers, 'id'); // Ambil array ID staff

        $data = [
            'title' => 'Dokumen Seluruh Staff',
            'personalFolders' => [], // Untuk folder level atas dari staff
            'orphanFiles' => [], // Untuk file tanpa folder dari staff
            'currentUserId' => $userId, // Tambahkan ini
            'currentRole' => $this->session->get('user_role'), // Tambahkan ini
        ];

        if (!empty($staffUserIds)) {
            // Mengambil folder personal level paling atas (parent_id NULL) dari semua staff
            $data['personalFolders'] = $this->folderModel
                ->select('folders.*, users.name as owner_name, users.email as owner_email')
                ->join('users', 'users.id = folders.owner_id', 'left')
                ->whereIn('folders.owner_id', $staffUserIds)
                ->where('folders.parent_id', NULL) // Hanya ambil folder root
                ->where('folders.folder_type', 'personal') // Pastikan hanya folder personal
                ->orderBy('users.name', 'ASC')
                ->orderBy('folders.name', 'ASC')
                ->findAll();

            // Mengambil file tanpa folder (orphan files) dari semua staff
            $data['orphanFiles'] = $this->fileModel
                ->select('files.*, users.name as uploader_name, users.email as uploader_email')
                ->join('users', 'users.id = files.uploader_id', 'left')
                ->whereIn('files.uploader_id', $staffUserIds)
                ->where('files.folder_id', NULL) // File yang tidak terasosiasi dengan folder
                ->orderBy('users.name', 'ASC')
                ->orderBy('files.file_name', 'ASC')
                ->findAll();
        }

        return view('Direksi/dokumenStaff', $data);
    }

    public function dokumenSPVUntukDireksi()
    {
        $userId = $this->session->get('user_id');
        $roleId = $this->session->get('role_id');

        // Pastikan login dan role adalah Manager (role_id 4)
        if (!$userId || $roleId != 3) { // <-- Perbaikan di sini
            return redirect()->to(base_url('dashboard'))->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // Ambil SEMUA user dengan role Supervisor (asumsi role_id 5)
        $spvUsers = $this->userModel->where('role_id', 5)->findAll(); // <-- Perbaikan di sini
        $spvUserIds = array_column($spvUsers, 'id'); // Ambil array ID Supervisor

        $data = [
            'title' => 'Dokumen Seluruh Supervisor', // Judul disesuaikan
            'personalFolders' => [], // Untuk folder level atas dari Supervisor
            'orphanFiles' => [], // Untuk file tanpa folder dari Supervisor
            'currentUserId' => $userId, // Tambahkan ini
            'currentRole' => $this->session->get('user_role'), // Tambahkan ini
        ];

        if (!empty($spvUserIds)) { // Menggunakan $spvUserIds
            // Mengambil folder personal level paling atas (parent_id NULL) dari semua Supervisor
            $data['personalFolders'] = $this->folderModel
                ->select('folders.*, users.name as owner_name, users.email as owner_email')
                ->join('users', 'users.id = folders.owner_id', 'left')
                ->whereIn('folders.owner_id', $spvUserIds) // Menggunakan $spvUserIds
                ->where('folders.parent_id', NULL) // Hanya ambil folder root
                ->where('folders.folder_type', 'personal') // Pastikan hanya folder personal
                ->orderBy('users.name', 'ASC')
                ->orderBy('folders.name', 'ASC')
                ->findAll();

            // Mengambil file tanpa folder (orphan files) dari semua Supervisor
            $data['orphanFiles'] = $this->fileModel
                ->select('files.*, users.name as uploader_name, users.email as uploader_email')
                ->join('users', 'users.id = files.uploader_id', 'left')
                ->whereIn('files.uploader_id', $spvUserIds) // Menggunakan $spvUserIds
                ->where('files.folder_id', NULL) // File yang tidak terasosiasi dengan folder
                ->orderBy('users.name', 'ASC')
                ->orderBy('files.file_name', 'ASC')
                ->findAll();
        }

        return view('Direksi/dokumenSupervisor', $data);
    }

    public function dokumenManagerUntukDireksi()
    {
        $userId = $this->session->get('user_id');
        $roleId = $this->session->get('role_id');

        // Pastikan login dan role adalah Manager (role_id 4)
        if (!$userId || $roleId != 3) { // <-- Perbaikan di sini
            return redirect()->to(base_url('dashboard'))->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // Ambil SEMUA user dengan role Supervisor (asumsi role_id 5)
        $spvUsers = $this->userModel->where('role_id', 4)->findAll(); // <-- Perbaikan di sini
        $spvUserIds = array_column($spvUsers, 'id'); // Ambil array ID Supervisor

        $data = [
            'title' => 'Dokumen Seluruh Supervisor', // Judul disesuaikan
            'personalFolders' => [], // Untuk folder level atas dari Supervisor
            'orphanFiles' => [], // Untuk file tanpa folder dari Supervisor
            'currentUserId' => $userId, // Tambahkan ini
            'currentRole' => $this->session->get('user_role'), // Tambahkan ini
        ];

        if (!empty($spvUserIds)) { // Menggunakan $spvUserIds
            // Mengambil folder personal level paling atas (parent_id NULL) dari semua Supervisor
            $data['personalFolders'] = $this->folderModel
                ->select('folders.*, users.name as owner_name, users.email as owner_email')
                ->join('users', 'users.id = folders.owner_id', 'left')
                ->whereIn('folders.owner_id', $spvUserIds) // Menggunakan $spvUserIds
                ->where('folders.parent_id', NULL) // Hanya ambil folder root
                ->where('folders.folder_type', 'personal') // Pastikan hanya folder personal
                ->orderBy('users.name', 'ASC')
                ->orderBy('folders.name', 'ASC')
                ->findAll();

            // Mengambil file tanpa folder (orphan files) dari semua Supervisor
            $data['orphanFiles'] = $this->fileModel
                ->select('files.*, users.name as uploader_name, users.email as uploader_email')
                ->join('users', 'users.id = files.uploader_id', 'left')
                ->whereIn('files.uploader_id', $spvUserIds) // Menggunakan $spvUserIds
                ->where('files.folder_id', NULL) // File yang tidak terasosiasi dengan folder
                ->orderBy('users.name', 'ASC')
                ->orderBy('files.file_name', 'ASC')
                ->findAll();
        }

        return view('Direksi/dokumenManager', $data);
    }

    // --- VIEW STAFF FOLDER UNTUK DIREKSI ---
    public function viewStaffFolder($folderId = null)
    {
        if ($folderId === null) {
            throw PageNotFoundException::forPageNotFound('Folder ID tidak ditentukan.');
        }

        $userId = $this->session->get('user_id'); // ID Direksi yang sedang login
        $userRole = $this->session->get('role_id'); // Role ID Direksi yang sedang login (seharusnya 3)

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses folder.');
        }

        $currentFolder = $this->folderModel->find($folderId);

        if (!$currentFolder) {
            throw PageNotFoundException::forPageNotFound('Folder Staff tidak ditemukan.');
        }

        $ownerUser = $this->userModel->find($currentFolder['owner_id']);

        // Validasi akses untuk Direksi (role_id 3)
        // Direksi bisa melihat folder personal Staff (role_id 6)
        // Direksi juga bisa melihat folder shared/public milik Staff, SPV, atau Manager
        $hasAccess = false;
        if ($userRole == 3) {
            // Jika folder adalah personal dan pemiliknya adalah Staff
            if ($currentFolder['folder_type'] === 'personal' && $ownerUser && $ownerUser['role_id'] == 6) {
                $hasAccess = true;
            }
            // Tambahkan kondisi jika Direksi juga bisa melihat folder shared/public
            // Sesuaikan dengan kebutuhan bisnis Anda
            if ($currentFolder['is_shared'] == 1 || $currentFolder['folder_type'] === 'public') {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            return redirect()->to(base_url('direksi/dokumen-staff'))->with('error', 'Anda tidak memiliki izin untuk melihat folder ini.');
        }

        $subFolders = $this->folderModel->where('parent_id', $folderId)->findAll();
        $filesInFolder = $this->fileModel->where('folder_id', $folderId)->findAll();

        $breadcrumbs = [];
        $breadcrumbs[] = ['name' => 'Dokumen Staff', 'id' => null, 'url' => base_url('direksi/dokumen-staff')];

        $folderBreadcrumbs = $this->folderModel->getBreadcrumbs($folderId);
        foreach ($folderBreadcrumbs as $crumb) {
            $breadcrumbs[] = [
                'name' => $crumb['name'],
                'id' => $crumb['id'],
                'url' => base_url('direksi/view-staff-folder/' . $crumb['id']) // Sesuaikan URL
            ];
        }

        $data = [
            'title' => 'Folder Staff: ' . $currentFolder['name'],
            'folderName' => $currentFolder['name'],
            'folderId' => $currentFolder['id'],
            'isShared' => (bool) $currentFolder['is_shared'],
            'sharedType' => $currentFolder['shared_type'],
            'folderType' => $currentFolder['folder_type'],
            'subFolders' => $subFolders,
            'filesInFolder' => $filesInFolder,
            'breadcrumbs' => $breadcrumbs,
            'isStaffFolder' => true,
            'isSupervisorFolder' => false,
            'isManagerFolder' => false, // NEW: Add this if you want to use it in view
            'canDireksiFolder' => false, // Ini untuk memberi tahu view Direksi bisa mengelola fitur Direksi
            'targetUserId' => $currentFolder['owner_id'],
        ];

        return view('Direksi/viewFolder', $data); // Pastikan view-nya benar (Direksi/viewFolder)
    }

    // --- VIEW SPV FOLDER UNTUK DIREKSI ---
    public function viewSPVFolder($folderId = null)
    {
        if ($folderId === null) {
            throw PageNotFoundException::forPageNotFound('Folder ID tidak ditentukan.');
        }

        $userId = $this->session->get('user_id'); // ID Direksi yang sedang login
        $userRole = $this->session->get('role_id'); // Role ID Direksi yang sedang login (seharusnya 3)

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses folder.');
        }

        $currentFolder = $this->folderModel->find($folderId);

        if (!$currentFolder) {
            throw PageNotFoundException::forPageNotFound('Folder Supervisor tidak ditemukan.');
        }

        $ownerUser = $this->userModel->find($currentFolder['owner_id']);

        // Validasi akses untuk Direksi (role_id 3)
        $hasAccess = false;
        if ($userRole == 3) {
            // Jika folder adalah personal dan pemiliknya adalah Supervisor
            if ($currentFolder['folder_type'] === 'personal' && $ownerUser && $ownerUser['role_id'] == 5) {
                $hasAccess = true;
            }
            // Tambahkan kondisi jika Direksi juga bisa melihat folder shared/public
            if ($currentFolder['is_shared'] == 1 || $currentFolder['folder_type'] === 'public') {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            return redirect()->to(base_url('direksi/dokumen-supervisor'))->with('error', 'Anda tidak memiliki izin untuk melihat folder ini.');
        }

        $subFolders = $this->folderModel->where('parent_id', $folderId)->findAll();
        $filesInFolder = $this->fileModel->where('folder_id', $folderId)->findAll();

        $breadcrumbs = [];
        $breadcrumbs[] = ['name' => 'Dokumen Supervisor', 'id' => null, 'url' => base_url('direksi/dokumen-supervisor')];

        $folderBreadcrumbs = $this->folderModel->getBreadcrumbs($folderId);
        foreach ($folderBreadcrumbs as $crumb) {
            $breadcrumbs[] = [
                'name' => $crumb['name'],
                'id' => $crumb['id'],
                'url' => base_url('direksi/view-supervisor-folder/' . $crumb['id']) // Sesuaikan URL
            ];
        }

        $data = [
            'title' => 'Folder Supervisor: ' . $currentFolder['name'],
            'folderName' => $currentFolder['name'],
            'folderId' => $currentFolder['id'],
            'isShared' => (bool) $currentFolder['is_shared'],
            'sharedType' => $currentFolder['shared_type'],
            'folderType' => $currentFolder['folder_type'],
            'subFolders' => $subFolders,
            'filesInFolder' => $filesInFolder,
            'breadcrumbs' => $breadcrumbs,
            'isStaffFolder' => false,
            'isSupervisorFolder' => true,
            'isManagerFolder' => false, // NEW: Add this if you want to use it in view
            'canDireksiFolder' => false, // Ini untuk memberi tahu view Direksi bisa mengelola fitur Direksi
            'targetUserId' => $currentFolder['owner_id'],
        ];

        return view('Direksi/viewFolder', $data);
    }

    // --- VIEW MANAGER FOLDER UNTUK DIREKSI ---
    public function viewManagerFolder($folderId = null)
    {
        if ($folderId === null) {
            throw PageNotFoundException::forPageNotFound('Folder ID tidak ditentukan.');
        }

        $userId = $this->session->get('user_id'); // ID Direksi yang sedang login
        $userRole = $this->session->get('role_id'); // Role ID Direksi yang sedang login (seharusnya 3)

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses folder.');
        }

        $currentFolder = $this->folderModel->find($folderId);

        if (!$currentFolder) {
            throw PageNotFoundException::forPageNotFound('Folder Manager tidak ditemukan.');
        }

        $ownerUser = $this->userModel->find($currentFolder['owner_id']);

        // Validasi akses untuk Direksi (role_id 3)
        $hasAccess = false;
        if ($userRole == 3) {
            // Jika folder adalah personal dan pemiliknya adalah Manager
            if ($currentFolder['folder_type'] === 'personal' && $ownerUser && $ownerUser['role_id'] == 4) {
                $hasAccess = true;
            }
            // Tambahkan kondisi jika Direksi juga bisa melihat folder shared/public
            if ($currentFolder['is_shared'] == 1 || $currentFolder['folder_type'] === 'public') {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            return redirect()->to(base_url('direksi/dokumen-manager'))->with('error', 'Anda tidak memiliki izin untuk melihat folder ini.');
        }

        $subFolders = $this->folderModel->where('parent_id', $folderId)->findAll();
        $filesInFolder = $this->fileModel->where('folder_id', $folderId)->findAll();

        $breadcrumbs = [];
        $breadcrumbs[] = ['name' => 'Dokumen Manager', 'id' => null, 'url' => base_url('direksi/dokumen-manager')];

        $folderBreadcrumbs = $this->folderModel->getBreadcrumbs($folderId);
        foreach ($folderBreadcrumbs as $crumb) {
            $breadcrumbs[] = [
                'name' => $crumb['name'],
                'id' => $crumb['id'],
                'url' => base_url('direksi/view-manager-folder/' . $crumb['id']) // Sesuaikan URL
            ];
        }

        $data = [
            'title' => 'Folder Manager: ' . $currentFolder['name'],
            'folderName' => $currentFolder['name'],
            'folderId' => $currentFolder['id'],
            'isShared' => (bool) $currentFolder['is_shared'],
            'sharedType' => $currentFolder['shared_type'],
            'folderType' => $currentFolder['folder_type'],
            'subFolders' => $subFolders,
            'filesInFolder' => $filesInFolder,
            'breadcrumbs' => $breadcrumbs,
            'isStaffFolder' => false,
            'isSupervisorFolder' => false,
            'isManagerFolder' => true, // NEW: Flag ini menandakan folder Manager
            'canDireksiFolder' => false, // Ini untuk memberi tahu view Direksi bisa mengelola fitur Direksi
            'targetUserId' => $currentFolder['owner_id'],
        ];

        return view('Direksi/viewFolder', $data);
    }


    public function createFolder()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
        }

        $userId = $this->session->get('user_id');
        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized. User not logged in.']);
        }

        $input = $this->request->getJSON(true);

        $folderName = $input['name'] ?? null;
        $parentId = $input['parent_id'] ?? null;
        $folderType = $input['folder_type'] ?? 'personal';
        $isShared = $input['is_shared'] ?? 0;
        $sharedType = $input['shared_type'] ?? null;
        $accessRoles = $input['access_roles'] ?? null;

        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
        ];
        if (!$this->validate($rules, ['name' => ['required' => 'Nama folder tidak boleh kosong.']])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Validasi gagal.', 'errors' => $this->validator->getErrors()]);
        }

        if ($parentId) {
            $parentFolder = $this->folderModel->find($parentId);
            if ($parentFolder) {
                $folderType = $parentFolder['folder_type'];
                $isShared = $parentFolder['is_shared'];
                $sharedType = $parentFolder['shared_type'];
                $accessRoles = json_decode($parentFolder['access_roles'] ?? '[]', true);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Parent folder tidak ditemukan.']);
            }
        }

        $data = [
            'name' => $folderName,
            'parent_id' => $parentId,
            'owner_id' => $userId,
            'folder_type' => $folderType,
            'is_shared' => (int) $isShared,
            'shared_type' => ((int) $isShared === 1) ? $sharedType : null,
            'access_roles' => ((int) $isShared === 1 && !empty($accessRoles)) ? json_encode($accessRoles) : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->folderModel->insert($data)) {
            // Setelah insert, buat folder fisik di server
            $newFolderId = $this->folderModel->insertID();
            $relativePath = $this->folderModel->getFolderPath($newFolderId);
            $folderPath = WRITEPATH . 'uploads/' . $relativePath;
            if (!is_dir($folderPath)) {
                if (!mkdir($folderPath, 0777, true)) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membuat folder fisik di server.']);
                }
            }
            return $this->response->setJSON(['status' => 'success', 'message' => 'Folder berhasil dibuat!', 'new_folder_id' => $newFolderId]);
        } else {
            $errors = $this->folderModel->errors();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membuat folder.', 'errors' => $errors]);
        }
    }

    public function search()
    {
        $query = $this->request->getVar('q');
        if (!$query) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Query is missing.']);
        }

        $userId = $this->session->get('user_id');

        $folders = $this->folderModel
            ->where('owner_id', $userId)
            ->like('name', $query)
            ->select("id, name, 'folder' as type")
            ->findAll();

        $files = $this->fileModel
            ->where('uploader_id', $userId)
            ->like('file_name', $query)
            ->select("id, file_name as name, 'file' as type, folder_id")
            ->findAll();

        $results = array_merge($folders, $files);

        return $this->response->setJSON($results);
    }

    public function searchStaff()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request.']);
        }

        $query = $this->request->getVar('q');

        if (!$query) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Query pencarian tidak boleh kosong.']);
        }

        $supervisorRoleId = $this->session->get('role_id');
        $staffRoleId = 6; // Ganti dengan ID role Staff yang sesuai di database Anda.

        // --- Perbaikan Query Folder ---
        $folderModel = new FolderModel();
        $folderBuilder = $folderModel->builder();
        $folderBuilder->select("folders.id, folders.name, 'folder' as type, users.id as owner_id");
        $folderBuilder->like('folders.name', $query); // Cek nama folder mengandung query
        $folderBuilder->join('users', 'users.id = folders.owner_id');
        $folderBuilder->where('users.role_id', $staffRoleId); // Hanya folder milik Staff

        // Coba untuk melihat query yang dihasilkan untuk folder
        // dd($folderBuilder->getCompiledSelect()); 

        $folders = $folderBuilder->get()->getResultArray();

        // --- Perbaikan Query File ---
        $fileModel = new FileModel();
        $fileBuilder = $fileModel->builder();
        $fileBuilder->select("files.id, files.file_name as name, 'file' as type, files.folder_id");
        $fileBuilder->like('files.file_name', $query); // Cek nama file mengandung query
        $fileBuilder->join('users', 'users.id = files.uploader_id');
        $fileBuilder->where('users.role_id', $staffRoleId); // Hanya file milik Staff

        // Coba untuk melihat query yang dihasilkan untuk file
        // dd($fileBuilder->getCompiledSelect()); 

        $files = $fileBuilder->get()->getResultArray();

        $results = array_merge($folders, $files);
        $formattedResults = [];
        foreach ($results as $item) {
            $formattedResults[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'type' => $item['type'],
                'folder_id' => $item['type'] === 'file' ? $item['folder_id'] : null,
            ];
        }

        return $this->response->setJSON($formattedResults);
    }

    public function searchSPV()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request.']);
        }

        $query = $this->request->getVar('q');

        if (!$query) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Query pencarian tidak boleh kosong.']);
        }

        $supervisorRoleId = $this->session->get('role_id');
        $staffRoleId = 5; // Ganti dengan ID role Staff yang sesuai di database Anda.

        // --- Perbaikan Query Folder ---
        $folderModel = new FolderModel();
        $folderBuilder = $folderModel->builder();
        $folderBuilder->select("folders.id, folders.name, 'folder' as type, users.id as owner_id");
        $folderBuilder->like('folders.name', $query); // Cek nama folder mengandung query
        $folderBuilder->join('users', 'users.id = folders.owner_id');
        $folderBuilder->where('users.role_id', $staffRoleId); // Hanya folder milik Staff

        // Coba untuk melihat query yang dihasilkan untuk folder
        // dd($folderBuilder->getCompiledSelect()); 

        $folders = $folderBuilder->get()->getResultArray();

        // --- Perbaikan Query File ---
        $fileModel = new FileModel();
        $fileBuilder = $fileModel->builder();
        $fileBuilder->select("files.id, files.file_name as name, 'file' as type, files.folder_id");
        $fileBuilder->like('files.file_name', $query); // Cek nama file mengandung query
        $fileBuilder->join('users', 'users.id = files.uploader_id');
        $fileBuilder->where('users.role_id', $staffRoleId); // Hanya file milik Staff

        // Coba untuk melihat query yang dihasilkan untuk file
        // dd($fileBuilder->getCompiledSelect()); 

        $files = $fileBuilder->get()->getResultArray();

        $results = array_merge($folders, $files);
        $formattedResults = [];
        foreach ($results as $item) {
            $formattedResults[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'type' => $item['type'],
                'folder_id' => $item['type'] === 'file' ? $item['folder_id'] : null,
            ];
        }

        return $this->response->setJSON($formattedResults);
    }

    public function searchManager()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request.']);
        }

        $query = $this->request->getVar('q');

        if (!$query) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Query pencarian tidak boleh kosong.']);
        }

        $supervisorRoleId = $this->session->get('role_id');
        $staffRoleId = 4; // Ganti dengan ID role Staff yang sesuai di database Anda.

        // --- Perbaikan Query Folder ---
        $folderModel = new FolderModel();
        $folderBuilder = $folderModel->builder();
        $folderBuilder->select("folders.id, folders.name, 'folder' as type, users.id as owner_id");
        $folderBuilder->like('folders.name', $query); // Cek nama folder mengandung query
        $folderBuilder->join('users', 'users.id = folders.owner_id');
        $folderBuilder->where('users.role_id', $staffRoleId); // Hanya folder milik Staff

        // Coba untuk melihat query yang dihasilkan untuk folder
        // dd($folderBuilder->getCompiledSelect()); 

        $folders = $folderBuilder->get()->getResultArray();

        // --- Perbaikan Query File ---
        $fileModel = new FileModel();
        $fileBuilder = $fileModel->builder();
        $fileBuilder->select("files.id, files.file_name as name, 'file' as type, files.folder_id");
        $fileBuilder->like('files.file_name', $query); // Cek nama file mengandung query
        $fileBuilder->join('users', 'users.id = files.uploader_id');
        $fileBuilder->where('users.role_id', $staffRoleId); // Hanya file milik Staff

        // Coba untuk melihat query yang dihasilkan untuk file
        // dd($fileBuilder->getCompiledSelect()); 

        $files = $fileBuilder->get()->getResultArray();

        $results = array_merge($folders, $files);
        $formattedResults = [];
        foreach ($results as $item) {
            $formattedResults[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'type' => $item['type'],
                'folder_id' => $item['type'] === 'file' ? $item['folder_id'] : null,
            ];
        }

        return $this->response->setJSON($formattedResults);
    }

    public function downloadFile($fileId)
    {
        log_message('info', 'Mencoba mengunduh file dengan ID: ' . $fileId);

        $userId = $this->session->get('user_id');
        $userRole = $this->session->get('role'); // Kunci session diubah menjadi 'role'
        log_message('info', 'User ID yang login: ' . $userId . ', Role: ' . $userRole);

        if (!$userId) {
            log_message('error', 'Gagal: User tidak login.');
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengunduh file.');
        }

        $file = $this->fileModel->find($fileId);
        if (!$file) {
            log_message('error', 'Gagal: File tidak ditemukan di database dengan ID: ' . $fileId);
            throw PageNotFoundException::forPageNotFound('File tidak ditemukan.');
        }

        // Ambil informasi folder jika file berada di dalam folder
        $parentFolder = null;
        if ($file['folder_id']) {
            $parentFolder = $this->folderModel->find($file['folder_id']);
            if ($parentFolder) {
                log_message('info', 'Parent folder type: ' . $parentFolder['folder_type'] . ' | Owner ID: ' . $parentFolder['owner_id']);
            }
        }

        // Logika Izin Unduh
        $allowedToDownload = false;

        // KASUS 1: Direksi diizinkan mengunduh semua file
        if ($userRole === 'direksi') {
            $allowedToDownload = true;
            log_message('info', 'Izin diberikan karena user adalah Direksi.');
        }

        // KASUS 2: Logika standar untuk pengguna lain (bukan Direksi)
        else {
            // Izin untuk folder personal milik sendiri
            if ($parentFolder && $parentFolder['folder_type'] === 'personal' && $parentFolder['owner_id'] === $userId) {
                $allowedToDownload = true;
                log_message('info', 'Izin diberikan karena user memiliki folder personal.');
            }

            // Izin untuk folder shared
            elseif ($parentFolder && $parentFolder['folder_type'] === 'shared') {
                $accessRoles = json_decode($parentFolder['access_roles'] ?? '[]', true);
                if (in_array($userRole, $accessRoles) || $parentFolder['owner_id'] === $userId) {
                    $allowedToDownload = true;
                    log_message('info', 'Izin diberikan karena user memiliki akses ke folder shared.');
                }
            }

            // Izin untuk file yang tidak dalam folder dan diunggah oleh user
            elseif (!$parentFolder && $file['uploader_id'] === $userId) {
                $allowedToDownload = true;
                log_message('info', 'Izin diberikan karena user mengunggah file.');
            }
        }

        if (!$allowedToDownload) {
            log_message('error', 'Gagal: User tidak memiliki izin untuk mengunduh file ini.');
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengunduh file ini.');
        }

        // ... (lanjutan kode download di bawah ini sama seperti sebelumnya) ...
        $filePath = WRITEPATH . 'uploads/' . $file['file_path'];

        if (!file_exists($filePath)) {
            log_message('error', 'Gagal: File tidak ditemukan di server pada path: ' . $filePath);
            throw PageNotFoundException::forPageNotFound('File tidak ditemukan di server.');
        }

        $this->fileModel->update($fileId, ['download_count' => ($file['download_count'] ?? 0) + 1]);

        log_message('info', 'Berhasil: File siap diunduh.');
        return $this->response->download($filePath, null)->setFileName($file['file_name']);
    }

    public function viewFile($fileId)
    {
        $file = $this->fileModel->find($fileId);

        if (!$file) {
            throw PageNotFoundException::forPageNotFound('File tidak ditemukan.');
        }

        // Ambil ekstensi file dari nama file asli ($file['file_name'])
        // Ini adalah perbaikan utamanya
        $fileExtension = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));

        // Daftar ekstensi yang bisa di-preview secara native oleh browser
        $isNativePreviewable = in_array($fileExtension, ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'txt', 'html']);

        $data = [
            'fileId' => $fileId,
            'fileName' => $file['file_name'],
            'file' => $file,
        ];

        if ($isNativePreviewable) {
            // Untuk PDF, Gambar, Teks: tampilkan di iframe
            $data['previewUrl'] = site_url('Direksi/serve-file/' . $fileId);
            return view('Direksi/view_file_wrapper', $data);
        } else {
            // Untuk DOCX, PPTX, XLSX, dll.: tampilkan halaman info dan tombol unduh
            return view('Direksi/view_file_khusus', $data);
        }
    }

    public function serveFile($fileId)
    {
        $file = $this->fileModel->find($fileId);
        $userId = $this->session->get('user_id');

        if (!$file) {
            throw PageNotFoundException::forPageNotFound('File tidak ditemukan.');
        }

        // Pastikan data file_path ada
        if (empty($file['file_path'])) {
            throw new \Exception('Data path file tidak ditemukan di database.');
        }

        $filePath = WRITEPATH . 'uploads/' . $file['file_path'];

        if (!file_exists($filePath)) {
            throw PageNotFoundException::forPageNotFound('File tidak ada di server.');
        }

        // Tentukan Content-Type berdasarkan ekstensi file
        $mime = mime_content_type($filePath);

        // Atur header untuk menampilkan file di browser (inline)
        $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . basename($file['file_name']) . '"') // Gunakan file_name untuk nama file
            ->setBody(file_get_contents($filePath));

        return $this->response;
    }
}