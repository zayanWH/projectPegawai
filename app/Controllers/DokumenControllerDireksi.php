<?php

namespace App\Controllers;

use App\Models\FolderModel; // Pastikan Anda mengimpor FolderModel
use App\Models\FileModel;   // Pastikan Anda mengimpor FileModel
use App\Models\UserModel;   // Pastikan Anda mengimpor UserModel
use CodeIgniter\Controller; // Jika DokumenControllerSPV bukan turunan BaseController
use CodeIgniter\Session\Session; // Import Session class jika belum
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\I18n\Time;

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
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
        }

        $userId = $this->session->get('user_id'); // ID Manager yang sedang login
        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized. User not logged in.']);
        }

        $file = $this->request->getFile('file_upload');
        $folderId = $this->request->getPost('folder_id');
        $folderType = $this->request->getPost('folder_type');
        $targetUserId = $this->request->getPost('target_user_id') ?? $userId; // Owner file adalah target_user_id jika ada, atau user yang login

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal mengunggah file. Pastikan file valid dan belum dipindahkan.']);
        }

        // Validasi ukuran file (misalnya, dari role_id atau konfigurasi)
        // Anda perlu implementasi ini jika belum ada.
        // $maxUploadSize = $this->userModel->getMaxUploadSize($userId); // Contoh
        // if ($file->getSize() > $maxUploadSize) { ... }

        $originalName = $file->getName();
        $newName = $file->getRandomName(); // Generate unique name
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        // Dapatkan path folder fisik di server
        $targetFolderPath = WRITEPATH . 'uploads/';
        if ($folderId) {
            $currentFolder = $this->folderModel->find($folderId);
            if ($currentFolder) {
                $relativePath = $this->folderModel->getFolderPath($folderId); // Dapatkan path relatif
                $targetFolderPath .= $relativePath;
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Folder tujuan tidak ditemukan.']);
            }
        } else {
            // Jika tidak ada folderId, asumsikan upload ke root personal folder user yang punya targetUserId
            $targetFolderPath .= 'personal/' . $targetUserId . '/';
        }

        // Pastikan folder fisik ada
        if (!is_dir($targetFolderPath)) {
            if (!mkdir($targetFolderPath, 0777, true)) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membuat direktori upload.']);
            }
        }

        // Pindahkan file
        if ($file->move($targetFolderPath, $newName)) {
            $data = [
                'file_name'     => $originalName,
                'server_file_name' => $newName,
                'file_type'     => $mimeType,
                'file_size'     => $fileSize,
                'folder_id'     => $folderId,
                'uploader_id'   => $userId,      // Uploader adalah Manager yang login
                'owner_id'      => $targetUserId, // Owner bisa Staff/Supervisor jika di folder mereka
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ];

            if ($this->fileModel->insert($data)) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'File berhasil diunggah!']);
            } else {
                // Jika gagal insert ke DB, hapus file yang sudah terupload
                unlink($targetFolderPath . $newName);
                $errors = $this->fileModel->errors();
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan info file ke database.', 'errors' => $errors]);
            }
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memindahkan file ke direktori upload.']);
        }
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
        usort($recentItems, function($a, $b) {
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
            'personalFolders'    => $personalFolders,
            'folderId'           => null, // Sesuaikan jika ada logika untuk ini
            'folderType'         => null, // Sesuaikan jika ada logika untuk ini
            'isShared'           => null, // Sesuaikan jika ada logika untuk ini
            'sharedType'         => null, // Sesuaikan jika ada logika untuk ini
            'orphanFiles'        => $orphanFiles,
            'totalFolders'       => $totalFolders,
            'totalFiles'         => $totalFiles,
            'latestUploadDate'   => $formattedLatestUpload,
            'recentItems'        => $recentItems,
            'currentRoleName'    => $userRole
        ];

        return view('Direksi/dashboard', $data);
    }

    public function dokumenDireksi()
    {
        $userId = $this->session->get('user_id');

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses dokumen.');
        }

        $personalFolders = $this->folderModel->where('owner_id', $userId)
            ->where('parent_id', NULL)
            ->where('folder_type', 'personal')
            ->findAll();

        $orphanFiles = $this->fileModel->where('uploader_id', $userId)
            ->where('folder_id', NULL)
            ->findAll();

        $data = [
            'title' => 'Dokumen Saya',
            'personalFolders' => $personalFolders,
            'orphanFiles' => $orphanFiles,
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
        return view('Umum/dokumenUmum');
    }

    public function viewFolder($folderId = null) // Tambahkan parameter $folderId
    {
        if ($folderId === null) {
            throw PageNotFoundException::forPageNotFound('Folder ID tidak ditentukan.');
        }

        $userId = $this->session->get('user_id');

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses folder.');
        }

        $currentFolder = $this->folderModel->find($folderId);

        if (!$currentFolder) {
            throw PageNotFoundException::forPageNotFound('Folder tidak ditemukan.');
        }

        if ($currentFolder['folder_type'] === 'personal' && $currentFolder['owner_id'] !== $userId) {
            return redirect()->to(base_url('direksi/dokumen-direksi'))->with('error', 'Anda tidak memiliki akses ke folder personal ini.');
        }

        if ($currentFolder['folder_type'] === 'shared' && $currentFolder['owner_id'] !== $userId) {
            $userRole = $this->session->get('user_role');
            $accessRoles = json_decode($currentFolder['access_roles'] ?? '[]', true);

            if (empty($accessRoles) || !in_array($userRole, $accessRoles)) {
                return redirect()->to(base_url('direksi/dokumen-direksi'))->with('error', 'Anda tidak memiliki izin untuk folder shared ini.');
            }
        }

        $subFolders = $this->folderModel->where('parent_id', $folderId)->findAll();
        $filesInFolder = $this->fileModel->where('folder_id', $folderId)->findAll();
        $breadcrumbs = $this->folderModel->getBreadcrumbs($folderId);

        $data = [
            'title' => 'Folder: ' . $currentFolder['name'],
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
            'isManagerFolder' => false, // NEW: Add this if you want to use it in view
            'canDireksiFolder' => true, // Ini untuk memberi tahu view Direksi bisa mengelola fitur Direksi
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
}