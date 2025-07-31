<?php

namespace App\Controllers;

use App\Models\FolderModel; // Pastikan Anda mengimpor FolderModel
use App\Models\FileModel;   // Pastikan Anda mengimpor FileModel
use App\Models\UserModel;   // Pastikan Anda mengimpor UserModel
use CodeIgniter\Controller; // Jika DokumenControllerSPV bukan turunan BaseController
use CodeIgniter\Session\Session; // Import Session class jika belum
use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\RoleModel;


class DokumenControllerSPV extends BaseController // Atau extends Controller jika tidak pakai BaseController
{
    protected $folderModel;
    protected $fileModel;
    protected $userModel;
    protected $session;
    protected $roleModel;

    public function __construct()
    {
        $this->folderModel = new FolderModel();
        $this->fileModel = new FileModel();
        $this->userModel = new UserModel();
        $this->session = \Config\Services::session(); // Inisialisasi session
        $this->roleModel = new \App\Models\RoleModel();
    }

    public function index()
    {
        return view('Supervisor/dashboard');
    }

    public function dokumenSPV()
    {
        $userId = $this->session->get('user_id'); // Mengambil ID SPV yang sedang login

        if (!$userId) {
            // Jika ID pengguna tidak ditemukan di sesi, arahkan kembali ke login
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses dokumen pribadi Anda.');
        }

        // Mengambil folder pribadi milik SPV yang sedang login
        $personalFolders = $this->folderModel
            ->where('owner_id', $userId)      // Filter berdasarkan ID pemilik
            ->where('parent_id', NULL)      // Hanya folder root
            ->where('folder_type', 'personal') // Asumsi folder pribadi adalah 'personal'
            ->orderBy('name', 'ASC')         // Urutkan berdasarkan nama
            ->findAll();

        // Mengambil file tanpa folder yang diunggah oleh SPV yang sedang login
        $orphanFiles = $this->fileModel
            ->where('uploader_id', $userId) // Filter berdasarkan ID pengunggah
            ->where('folder_id', NULL)     // Hanya file tanpa folder
            ->orderBy('file_name', 'ASC')  // Urutkan berdasarkan nama file
            ->findAll();

        $data = [
            'title' => 'Dokumen Pribadi Saya (SPV)', // Judul halaman
            'personalFolders' => $personalFolders,
            'orphanFiles' => $orphanFiles,
        ];

        // Memuat view yang akan menampilkan dokumen pribadi SPV
        return view('Supervisor/dokumenSupervisor', $data);
    }

    // Metode untuk menampilkan daftar folder dan file personal staff
    public function dokumenStaffUntukSPV()
    {
        $userId = $this->session->get('user_id');
        $roleId = $this->session->get('role_id');

        // Pastikan login dan role adalah Supervisor (role_id 5)
        if (!$userId || $roleId != 5) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // Ambil SEMUA user dengan role Staff (asumsi role_id 4)
        $staffUsers = $this->userModel->where('role_id', 6)->findAll();
        $staffUserIds = array_column($staffUsers, 'id'); // Ambil array ID staff

        $data = [
            'title' => 'Dokumen Seluruh Staff',
            'personalFolders' => [], // Untuk folder level atas dari staff
            'orphanFiles' => [], // Untuk file tanpa folder dari staff
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

        return view('Supervisor/dokumenStaff', $data);
    }

    // Metode baru: Untuk melihat isi folder STAFF secara detail oleh SPV
    public function viewStaffFolder($folderId = null)
    {
        if ($folderId === null) {
            throw PageNotFoundException::forPageNotFound('Folder ID tidak ditentukan.');
        }

        $userId = $this->session->get('user_id'); // User ID Supervisor
        $userRole = $this->session->get('role_id'); // Mengambil 'role_id' dari sesi

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses folder.');
        }

        $currentFolder = $this->folderModel->find($folderId);

        if (!$currentFolder) {
            throw PageNotFoundException::forPageNotFound('Folder Staff tidak ditemukan.');
        }

        $ownerUser = $this->userModel->find($currentFolder['owner_id']);

        // Debugging (opsional, bisa dinonaktifkan setelah testing)
        // dd([
        //     'currentFolder' => $currentFolder,
        //     'ownerUser' => $ownerUser,
        //     'folderType' => $currentFolder['folder_type'],
        //     'ownerUserRoleId' => $ownerUser ? $ownerUser['role_id'] : 'N/A',
        //     'userRole' => $userRole
        // ]);

        // Validasi akses untuk folder Staff
        if ($currentFolder['folder_type'] === 'personal' && $ownerUser && $ownerUser['role_id'] === '6' && $userRole === '5') {
            // Ini adalah folder personal Staff, dan yang mengakses adalah Supervisor, izinkan.
        } else {
            return redirect()->to(base_url('supervisor/dokumen-staff'))->with('error', 'Anda tidak memiliki izin untuk melihat folder ini.');
        }

        $subFolders = $this->folderModel->where('parent_id', $folderId)->findAll();
        $filesInFolder = $this->fileModel->where('folder_id', $folderId)->findAll();

        // Breadcrumbs untuk folder Staff:
        $breadcrumbs = [];
        $breadcrumbs[] = ['name' => 'Dokumen Staff', 'id' => null, 'url' => base_url('supervisor/dokumen-staff')];

        $folderBreadcrumbs = $this->folderModel->getBreadcrumbs($folderId);
        foreach ($folderBreadcrumbs as $crumb) {
            $breadcrumbs[] = [
                'name' => $crumb['name'],
                'id' => $crumb['id'],
                // Penting: Pastikan URL mengarah ke route view-staff-folder
                'url' => base_url('supervisor/view-staff-folder/' . $crumb['id'])
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
            'isStaffFolder' => true,      // Flag ini menyatakan ini adalah folder Staff
            'canManageFolder' => false    // SPV tidak bisa mengelola (hanya lihat) folder Staff
        ];

        return view('Supervisor/viewFolder', $data);
    }

     public function dokumenBersama()
    {
        $userId = $this->session->get('user_id');
        $roleId = $this->session->get('role_id'); // Dapatkan role ID

        // Panggil getRoleNameById dari RoleModel
        $userRoleName = $this->roleModel->getRoleNameById($roleId);

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses dokumen bersama.');
        }
        
        // Handle case where role name might not be found (though unlikely if roleId exists)
        if (!$userRoleName) {
            log_message('error', 'DokumenControllerSPV: Role name not found for role_id: ' . $roleId);
            return redirect()->to(base_url('login'))->with('error', 'Informasi peran pengguna tidak ditemukan.');
        }

        $sharedFolders = $this->folderModel->getSharedFoldersForUser($userId, $userRoleName);

        // Fetch user list for the filter, excluding 'Admin'
        $users = $this->userModel->getUsersExcludingAdmin();

        $data = [
            'title' => 'Dokumen Bersama',
            'sharedFolders' => $sharedFolders,
            'users' => $users,
        ];

        log_message('debug', 'DEBUG DokumenControllerSPV: Shared Folders passed to view: ' . json_encode($sharedFolders));

        return view('Umum/dokumenBersama', $data); // Pastikan ini mengarah ke view yang benar
    }

    public function dokumenUmum()
    {
        return view('Umum/dokumenUmum');
    }

    public function dashboard()
    {
        $userId = $this->session->get('user_id');
        $userRoleId = $this->session->get('role_id');
        $userRoleName = $this->session->get('user_role'); // Ambil nama role

        if (!$userId || $userRoleId != 5) { // Pastikan hanya Supervisor yang bisa mengakses
            return redirect()->to(base_url('login'))->with('error', 'Anda tidak memiliki izin untuk mengakses dashboard ini.');
        }

        // --- Statistik Sistem Global ---
        $totalFolders = $this->folderModel->countAllResults();
        $totalFiles = $this->fileModel->countAllResults();

        $latestFolderUpload = $this->folderModel->selectMax('created_at')->first();
        $latestFolderDate = $latestFolderUpload['created_at'] ?? null;

        $latestFileUpload = $this->fileModel->selectMax('created_at')->first();
        $latestFileDate = $latestFileUpload['created_at'] ?? null;

        $latestUploadDate = null;
        if ($latestFolderDate && $latestFileDate) {
            $latestUploadDate = (strtotime($latestFolderDate) > strtotime($latestFileDate)) ? $latestFolderDate : $latestFileDate;
        } elseif ($latestFolderDate) {
            $latestUploadDate = $latestFolderDate;
        } elseif ($latestFileDate) {
            $latestUploadDate = $latestFileDate;
        }
        $formattedLatestUpload = $latestUploadDate ? date('d M Y', strtotime($latestUploadDate)) : 'Belum ada upload';

        // --- 10 Item Terbaru (Folder & File) dari SELURUH SISTEM ---
        $foldersGlobal = $this->folderModel
            ->select("folders.id, folders.name, folders.created_at, folders.owner_id as uploader_id, users.name as uploader_name, 'folder' as type")
            ->join('users', 'users.id = folders.owner_id', 'left')
            ->orderBy('folders.created_at', 'DESC')
            ->findAll();

        $filesGlobal = $this->fileModel
            ->select("files.id, files.file_name as name, files.created_at, files.uploader_id, users.name as uploader_name, 'file' as type")
            ->join('users', 'users.id = files.uploader_id', 'left')
            ->orderBy('files.created_at', 'DESC')
            ->findAll();

        $recentItems = array_merge($foldersGlobal, $filesGlobal);

        usort($recentItems, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        $recentItems = array_slice($recentItems, 0, 10);

        // --- Folder Personal Supervisor ---
        $personalFoldersSPV = $this->folderModel
            ->where('owner_id', $userId)
            ->where('parent_id', null) // Hanya root personal folders
            ->where('folder_type', 'personal')
            ->findAll();

        // --- File Tanpa Folder Pribadi Supervisor ---
        $orphanFilesSPV = $this->fileModel
            ->where('uploader_id', $userId) // Filter berdasarkan uploader_id Supervisor
            ->where('folder_id IS NULL')
            ->findAll();

        $data = [
            'title' => 'Dashboard Supervisor',
            'totalFolders' => $totalFolders, // Total global
            'totalFiles' => $totalFiles,   // Total global
            'latestUploadDate' => $formattedLatestUpload, // Tanggal upload terbaru global
            'recentItems' => $recentItems,           // 10 item terbaru global
            'personalFolders' => $personalFoldersSPV,   // Folder personal milik SPV
            'orphanFiles' => $orphanFilesSPV,       // File tanpa folder milik SPV
            'userRoleName' => $userRoleName,
            'currentUserId' => $userId, // Kirim ID user ke view
        ];

        return view('Supervisor/dashboard', $data);
    }

    public function viewFolder($folderId = null) // Tambahkan parameter $folderId
    {
        if ($folderId === null) {
            throw PageNotFoundException::forPageNotFound('Folder ID tidak ditentukan.');
        }

        $userId = $this->session->get('user_id');
        $userRole = $this->session->get('role_id'); // Mengambil 'role_id' dari sesi

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses folder.');
            (log_message('info', 'Redirecting to login: User ID not found.'));
        }

        $currentFolder = $this->folderModel->find($folderId);

        if (!$currentFolder) {
            throw PageNotFoundException::forPageNotFound('Folder tidak ditemukan.');
        }

        // --- Logic akses folder personal SPV dan shared folder ---
        $canManageFolder = false; // Default: tidak bisa mengelola

        if ($currentFolder['folder_type'] === 'personal') {
            if ($currentFolder['owner_id'] !== $userId) {
                return redirect()->to(base_url('supervisor/dokumen-supervisor'))->with('error', 'Anda tidak memiliki akses ke folder personal ini.');
            }
            $canManageFolder = true; // Bisa mengelola folder personalnya sendiri
        } elseif ($currentFolder['folder_type'] === 'shared') {
            $accessRoles = json_decode($currentFolder['access_roles'] ?? '[]', true);

            if (empty($accessRoles) || !in_array($userRole, $accessRoles)) {
                return redirect()->to(base_url('supervisor/dokumen-supervisor'))->with('error', 'Anda tidak memiliki izin untuk folder shared ini.');
            }

            // Cek shared_type untuk hak manajemen
            if ($currentFolder['shared_type'] === 'write' || $currentFolder['shared_type'] === 'full_access') {
                $canManageFolder = true;
            } else {
                $canManageFolder = false; // Hanya baca
            }
        }
        // --- Akhir Logic akses ---

        $subFolders = $this->folderModel->where('parent_id', $folderId)->findAll();
        $filesInFolder = $this->fileModel->where('folder_id', $folderId)->findAll();

        // Breadcrumbs untuk folder Supervisor/Shared:
        $breadcrumbs = $this->folderModel->getBreadcrumbs($folderId);
        foreach ($breadcrumbs as &$crumb) {
            if ($crumb['id'] !== null) {
                // Penting: Pastikan URL mengarah ke route folder SPV
                $crumb['url'] = base_url('supervisor/folder/' . $crumb['id']);
            } else {
                // Contoh: crumb pertama 'Home' atau 'My Documents' bisa ke dashboard SPV
                $crumb['url'] = base_url('supervisor/dashboard');
            }
        }
        unset($crumb); // Putuskan referensi


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
            'isStaffFolder' => false,      // Flag ini menyatakan ini bukan folder Staff
            'canManageFolder' => $canManageFolder // Disesuaikan berdasarkan hak akses
        ];

        return view('Supervisor/viewFolder', $data);
    }


    // Tambahkan method createFolder, uploadFile, downloadFile, deleteFile sesuai dengan kebutuhan SPV
    // Contoh:
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

    public function uploadFile()
    {
        // Logic untuk upload file
        // Pastikan validasi, penyimpanan file ke server, dan penyimpanan info ke database
        return redirect()->back()->with('success', 'File berhasil diunggah.');
    }

    public function downloadFile($fileId)
    {
        // Logic untuk download file
        $file = $this->fileModel->find($fileId);
        if ($file) {
            return $this->response->download(WRITEPATH . 'uploads/' . $file['server_file_name'], null)->setFileName($file['file_name']);
        }
        return redirect()->back()->with('error', 'File tidak ditemukan.');
    }

    public function deleteFile($fileId)
    {
        // Logic untuk menghapus file dan entri di database
        $file = $this->fileModel->find($fileId);
        if ($file) {
            unlink(WRITEPATH . 'uploads/' . $file['server_file_name']); // Hapus file fisik
            $this->fileModel->delete($fileId); // Hapus dari database
            return redirect()->back()->with('success', 'File berhasil dihapus.');
        }
        return redirect()->back()->with('error', 'File tidak ditemukan.');
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

    public function searchStaff() // Fungsi baru untuk Supervisor mencari dokumen Staff
    {
        $query = $this->request->getVar('q');
        // Anda mungkin juga perlu parameter untuk ID Staff yang ingin dicari dokumennya
        $staffIdToSearch = $this->request->getVar('staff_id');

        if (!$query || !$staffIdToSearch) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Query atau ID Staff target tidak ditemukan.']);
        }

        // Pastikan user yang melakukan pencarian adalah Supervisor dan memiliki hak
        $supervisorId = $this->session->get('user_id');
        $supervisorRoleId = $this->session->get('role_id');

        if ($supervisorRoleId != 5) { // Asumsi role_id Supervisor adalah 5
            return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak memiliki izin untuk melakukan pencarian ini.']);
        }

        // Lakukan pencarian folder dan file berdasarkan $staffIdToSearch
        $folders = $this->folderModel
            ->where('owner_id', $staffIdToSearch) // Cari berdasarkan ID Staff
            ->like('name', $query)
            ->select("id, name, 'folder' as type")
            ->findAll();

        $files = $this->fileModel
            ->where('uploader_id', $staffIdToSearch) // Cari berdasarkan ID Staff
            ->like('file_name', $query)
            ->select("id, file_name as name, 'file' as type, folder_id")
            ->findAll();

        $results = array_merge($folders, $files);

        return $this->response->setJSON($results);
    }
}