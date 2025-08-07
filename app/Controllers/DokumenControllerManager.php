<?php

namespace App\Controllers;

use App\Models\FolderModel;
use App\Models\FileModel;
use App\Models\UserModel;
use CodeIgniter\Controller;
use CodeIgniter\Session\Session;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\I18n\Time;

class DokumenControllerManager extends BaseController
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
        $staffRoleId = 4; // Mengasumsikan role_id untuk Staff adalah 6

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

        return view('Manager/dashboard', $data);
    }




    public function dokumenManager()
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
            'isStaffFolder' => false,      // Bukan folder staff
            'isSupervisorFolder' => false,   // Bukan folder supervisor
            'canManageFolder' => true,       // Manager bisa mengelola folder pribadinya
            'folderId' => null,       // Root folder, jadi tidak ada folderId spesifik
        ];

        return view('Manager/dokumenManager', $data);
    }

    public function dokumenBersama()
    {
        return view('Umum/dokumenBersama');
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
        if ($folderId === null) {
            throw PageNotFoundException::forPageNotFound('Folder ID tidak ditentukan.');
        }

        $userId = $this->session->get('user_id');

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses folder.');
        }

        // ✅ PERBAIKAN DI SINI: Dapatkan nama peran (role_name) dari sesi
        // atau dari database jika sesi hanya menyimpan role_id
        $userRoleName = $this->session->get('role_name');
        // Jika sesi hanya menyimpan role_id, Anda perlu mengambil nama dari database:
        // $userRoleId = $this->session->get('role_id');
        // $userRoleData = $this->roleModel->find($userRoleId);
        // $userRoleName = $userRoleData['name'] ?? 'Guest'; // Fallback jika tidak ditemukan

        $currentFolder = $this->folderModel->find($folderId);

        if (!$currentFolder) {
            throw PageNotFoundException::forPageNotFound('Folder tidak ditemukan.');
        }

        // Validasi akses untuk folder personal Manager
        if ($currentFolder['folder_type'] === 'personal' && $currentFolder['owner_id'] !== $userId) {
            return redirect()->to(base_url('manager/dokumen-manager'))->with('error', 'Anda tidak memiliki akses ke folder personal ini.');
        }

        // Validasi akses untuk folder shared
        if ($currentFolder['folder_type'] === 'shared') {
            $accessRoles = json_decode($currentFolder['access_roles'] ?? '[]', true);

            // ✅ Gunakan $userRoleName di sini
            if (empty($accessRoles) || !in_array($userRoleName, $accessRoles)) {
                return redirect()->to(base_url('manager/dokumen-manager'))->with('error', 'Anda tidak memiliki izin untuk folder shared ini.');
            }
        }

        // ✅ Gunakan $userRoleName saat memanggil model
        $subFolders = $this->folderModel->getSubfoldersWithDetails($folderId, $userId, $userRoleName);
        $filesInFolder = $this->fileModel->getFilesByFolderWithUploader($folderId);
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
            'isSupervisorFolder' => false, // Tambahkan ini jika belum ada
            'canManageFolder' => true,
        ];

        return view('Manager/viewFolder', $data);
    }

    public function dokumenStaffUntukManager()
    {
        $userId = $this->session->get('user_id');
        $roleId = $this->session->get('role_id');

        // Pastikan login dan role adalah Manager (role_id 4)
        if (!$userId || $roleId != 4) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // Ambil SEMUA user dengan role Staff (asumsi role_id 6)
        $staffUsers = $this->userModel->where('role_id', 6)->findAll();
        $staffUserIds = array_column($staffUsers, 'id');

        $data = [
            'title' => 'Dokumen Seluruh Staff',
            'personalFolders' => [],
            'orphanFiles' => [],
            'currentUserId' => $userId,
            'currentRole' => $this->session->get('user_role'),
            'isStaffFolder' => true, // Ini adalah halaman daftar dokumen staff
            'isSupervisorFolder' => false, // Bukan halaman daftar dokumen supervisor
            'canManageFolder' => false, // Manager tidak mengelola di halaman daftar ini
            'folderId' => null, // Tidak ada folder spesifik di halaman daftar
        ];

        if (!empty($staffUserIds)) {
            $data['personalFolders'] = $this->folderModel
                ->select('folders.*, users.name as owner_name, users.email as owner_email')
                ->join('users', 'users.id = folders.owner_id', 'left')
                ->whereIn('folders.owner_id', $staffUserIds)
                ->where('folders.parent_id', NULL)
                ->where('folders.folder_type', 'personal')
                ->orderBy('users.name', 'ASC')
                ->orderBy('folders.name', 'ASC')
                ->findAll();

            $data['orphanFiles'] = $this->fileModel
                ->select('files.*, users.name as uploader_name, users.email as uploader_email')
                ->join('users', 'users.id = files.uploader_id', 'left')
                ->whereIn('files.uploader_id', $staffUserIds)
                ->where('files.folder_id', NULL)
                ->orderBy('users.name', 'ASC')
                ->orderBy('files.file_name', 'ASC')
                ->findAll();
        }

        return view('Manager/dokumenStaff', $data);
    }

    public function dokumenSPVUntukManager()
    {
        $userId = $this->session->get('user_id');
        $roleId = $this->session->get('role_id');

        // Pastikan login dan role adalah Manager (role_id 4)
        if (!$userId || $roleId != 4) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // Ambil SEMUA user dengan role Supervisor (asumsi role_id 5)
        $spvUsers = $this->userModel->where('role_id', 5)->findAll();
        $spvUserIds = array_column($spvUsers, 'id');

        $data = [
            'title' => 'Dokumen Seluruh Supervisor',
            'personalFolders' => [],
            'orphanFiles' => [],
            'currentUserId' => $userId,
            'currentRole' => $this->session->get('user_role'),
            'isStaffFolder' => false, // Bukan halaman daftar dokumen staff
            'isSupervisorFolder' => true,  // Ini adalah halaman daftar dokumen supervisor
            'canManageFolder' => false, // Manager tidak mengelola di halaman daftar ini
            'folderId' => null, // Tidak ada folder spesifik di halaman daftar
        ];

        if (!empty($spvUserIds)) {
            $data['personalFolders'] = $this->folderModel
                ->select('folders.*, users.name as owner_name, users.email as owner_email')
                ->join('users', 'users.id = folders.owner_id', 'left')
                ->whereIn('folders.owner_id', $spvUserIds)
                ->where('folders.parent_id', NULL)
                ->where('folders.folder_type', 'personal')
                ->orderBy('users.name', 'ASC')
                ->orderBy('folders.name', 'ASC')
                ->findAll();

            $data['orphanFiles'] = $this->fileModel
                ->select('files.*, users.name as uploader_name, users.email as uploader_email')
                ->join('users', 'users.id = files.uploader_id', 'left')
                ->whereIn('files.uploader_id', $spvUserIds)
                ->where('files.folder_id', NULL)
                ->orderBy('users.name', 'ASC')
                ->orderBy('files.file_name', 'ASC')
                ->findAll();
        }

        return view('Manager/dokumenSupervisor', $data); // Pastikan Anda memiliki view ini
    }

    public function viewStaffFolder($folderId = null)
    {
        if ($folderId === null) {
            throw PageNotFoundException::forPageNotFound('Folder ID tidak ditentukan.');
        }

        $userId = $this->session->get('user_id'); // ID Manager yang sedang login
        $userRole = $this->session->get('role_id'); // Role ID Manager yang sedang login (seharusnya 4)

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses folder.');
        }

        $currentFolder = $this->folderModel->find($folderId);

        if (!$currentFolder) {
            throw PageNotFoundException::forPageNotFound('Folder Staff tidak ditemukan.');
        }

        $ownerUser = $this->userModel->find($currentFolder['owner_id']);

        // Validasi akses: Manager (role_id 4) melihat folder personal Staff (role_id 6)
        if ($currentFolder['folder_type'] === 'personal' && $ownerUser && $ownerUser['role_id'] == 6 && $userRole == 4) {
            // Akses diizinkan
        } else {
            // Jika bukan folder personal staff, atau owner bukan staff, atau yang akses bukan manager
            return redirect()->to(base_url('manager/dokumen-staff'))->with('error', 'Anda tidak memiliki izin untuk melihat folder ini.');
        }

        $subFolders = $this->folderModel->where('parent_id', $folderId)->findAll();
        $filesInFolder = $this->fileModel->where('folder_id', $folderId)->findAll();

        // Breadcrumbs untuk folder Staff:
        $breadcrumbs = [];
        // Root breadcrumb untuk Manager saat melihat dokumen Staff
        $breadcrumbs[] = ['name' => 'Dokumen Staff', 'id' => null, 'url' => base_url('manager/dokumen-staff')];

        // Dapatkan breadcrumbs dari model, yang akan memberikan path dari root personal folder staff hingga currentFolder
        $folderBreadcrumbs = $this->folderModel->getBreadcrumbs($folderId);
        foreach ($folderBreadcrumbs as $crumb) {
            $breadcrumbs[] = [
                'name' => $crumb['name'],
                'id' => $crumb['id'],
                // Pastikan URL mengarah ke route view-staff-folder untuk Manager
                'url' => base_url('manager/view-staff-folder/' . $crumb['id'])
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
            'isStaffFolder' => true,       // Flag ini menyatakan ini adalah folder Staff
            'isSupervisorFolder' => false,      // Bukan folder Supervisor
            'canManageFolder' => false,      // Manager tidak bisa mengelola (hanya lihat) folder Personal Staff
            'targetUserId' => $currentFolder['owner_id'], // ID Staff yang memiliki folder ini
        ];

        return view('Manager/viewFolder', $data);
    }

    public function viewSPVFolder($folderId = null)
    {
        if ($folderId === null) {
            throw PageNotFoundException::forPageNotFound('Folder ID tidak ditentukan.');
        }

        $userId = $this->session->get('user_id'); // ID Manager yang sedang login
        $userRole = $this->session->get('role_id'); // Role ID Manager yang sedang login (seharusnya 4)

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses folder.');
        }

        $currentFolder = $this->folderModel->find($folderId);

        if (!$currentFolder) {
            throw PageNotFoundException::forPageNotFound('Folder Supervisor tidak ditemukan.'); // Pesan error lebih spesifik
        }

        $ownerUser = $this->userModel->find($currentFolder['owner_id']);

        // Validasi akses: Manager (role_id 4) melihat folder personal Supervisor (role_id 5)
        if ($currentFolder['folder_type'] === 'personal' && $ownerUser && $ownerUser['role_id'] == 5 && $userRole == 4) {
            // Akses diizinkan
        } else {
            // Jika bukan folder personal supervisor, atau owner bukan supervisor, atau yang akses bukan manager
            return redirect()->to(base_url('manager/dokumen-supervisor'))->with('error', 'Anda tidak memiliki izin untuk melihat folder ini.'); // Arahkan ke daftar supervisor
        }

        $subFolders = $this->folderModel->where('parent_id', $folderId)->findAll();
        $filesInFolder = $this->fileModel->where('folder_id', $folderId)->findAll();

        // Breadcrumbs untuk folder Supervisor:
        $breadcrumbs = [];
        // Root breadcrumb untuk Manager saat melihat dokumen Supervisor
        $breadcrumbs[] = ['name' => 'Dokumen Supervisor', 'id' => null, 'url' => base_url('manager/dokumen-supervisor')];

        // Dapatkan breadcrumbs dari model, yang akan memberikan path dari root personal folder supervisor hingga currentFolder
        $folderBreadcrumbs = $this->folderModel->getBreadcrumbs($folderId);
        foreach ($folderBreadcrumbs as $crumb) {
            $breadcrumbs[] = [
                'name' => $crumb['name'],
                'id' => $crumb['id'],
                // Pastikan URL mengarah ke route view-supervisor-folder untuk Manager
                'url' => base_url('manager/view-supervisor-folder/' . $crumb['id'])
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
            'isStaffFolder' => false,      // Bukan folder Staff
            'isSupervisorFolder' => true,       // Flag ini menyatakan ini adalah folder Supervisor
            'canManageFolder' => false,      // Manager tidak bisa mengelola (hanya lihat) folder Personal Supervisor
            'targetUserId' => $currentFolder['owner_id'], // ID Supervisor yang memiliki folder ini
        ];

        return view('Manager/viewFolder', $data);
    }

    public function createFolder()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
        }

        $userId = $this->session->get('user_id'); // ID Manager yang sedang login
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
        $targetUserId = $input['target_user_id'] ?? $userId; // Default owner adalah user yang login, bisa di-override jika ada target_user_id

        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
        ];
        if (!$this->validate($rules, ['name' => ['required' => 'Nama folder tidak boleh kosong.']])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Validasi gagal.', 'errors' => $this->validator->getErrors()]);
        }

        // Jika parent_id ada, ambil info dari parent folder untuk menentukan folder_type, is_shared, dll.
        if ($parentId) {
            $parentFolder = $this->folderModel->find($parentId);
            if ($parentFolder) {
                // Jika membuat subfolder di dalam folder personal Staff/Supervisor,
                // maka owner_id dari subfolder baru harus mengikuti owner_id dari parent folder.
                // folder_type juga harus mengikuti parent.
                $targetUserId = $parentFolder['owner_id'];
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
            'owner_id' => $targetUserId, // Gunakan targetUserId (bisa ID Manager, Staff, atau SPV)
            'folder_type' => $folderType,
            'is_shared' => (int) $isShared,
            'shared_type' => ((int) $isShared === 1) ? $sharedType : null,
            'access_roles' => ((int) $isShared === 1 && !empty($accessRoles)) ? json_encode($accessRoles) : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->folderModel->insert($data)) {
            $newFolderId = $this->folderModel->insertID();
            // Perlu perbaikan di getFolderPath jika path bergantung pada owner_id
            // Untuk saat ini, asumsikan getFolderPath bisa menangani owner_id yang berbeda
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
                'file_name' => $originalName,
                'server_file_name' => $newName,
                'file_type' => $mimeType,
                'file_size' => $fileSize,
                'folder_id' => $folderId,
                'uploader_id' => $userId,      // Uploader adalah Manager yang login
                'owner_id' => $targetUserId, // Owner bisa Staff/Supervisor jika di folder mereka
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
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

        // KASUS 1: Manager diizinkan mengunduh semua file
        if ($userRole === 'manajer') {
            $allowedToDownload = true;
            log_message('info', 'Izin diberikan karena user adalah Manager.');
        }

        // KASUS 2: Logika standar untuk pengguna lain (bukan Manager)
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

        // Pastikan Anda mendapatkan data pembuat/user dari relasi atau model lain jika diperlukan
        // Contoh: $userModel = new \App\Models\UserModel();
        // $creator = $userModel->find($file['uploaded_by']); // Asumsi ada kolom 'uploaded_by' di tabel file

        $filePath = WRITEPATH . 'uploads/' . $file['file_path'];
        $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Daftar ekstensi yang bisa di-preview secara native oleh browser
        $isNativePreviewable = in_array($fileExtension, ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'txt', 'html']);

        $data = [
            'fileId' => $fileId,
            'fileName' => $file['file_name'],
            'file' => $file, // Kirim objek file lengkap untuk info lain
            // 'creator'    => $creator, // Jika Anda ingin menampilkan info pembuat
        ];

        if ($isNativePreviewable) {
            // Untuk PDF, Gambar, Teks: tampilkan di iframe
            $data['previewUrl'] = site_url('Manager/serve-file/' . $fileId); // Pastikan serve-file punya otorisasi
            return view('Manager/view_file_wrapper', $data);
        } else {
            // Untuk DOCX, PPTX, XLSX, dll.: tampilkan halaman info dan tombol unduh
            return view('Manager/view_file_khusus', $data);
        }
    }

    public function serveFile($fileId)
    {
        $file = $this->fileModel->find($fileId);
        $userId = $this->session->get('user_id');

        if (!$file) {
            throw PageNotFoundException::forPageNotFound('File tidak ditemukan.');
        }

        $filePath = WRITEPATH . 'uploads/' . $file['file_path'];

        if (!file_exists($filePath)) {
            throw PageNotFoundException::forPageNotFound('File tidak ada di server.');
        }

        // $this->logAkses($userId, $file, 'preview');

        // Tentukan Content-Type berdasarkan ekstensi file
        $mime = mime_content_type($filePath);

        // Atur header untuk menampilkan file di browser (inline)
        $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . basename($filePath) . '"')
            ->setBody(file_get_contents($filePath));

        return $this->response;
    }
}