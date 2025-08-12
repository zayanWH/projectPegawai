<?php

namespace App\Controllers;

use App\Models\FolderModel; // Pastikan Anda mengimpor FolderModel
use App\Models\FileModel;   // Pastikan Anda mengimpor FileModel
use App\Models\UserModel;   // Pastikan Anda mengimpor UserModel
use CodeIgniter\Controller; // Jika DokumenControllerSPV bukan turunan BaseController
use CodeIgniter\Session\Session; // Import Session class jika belum
use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\RoleModel;
use CodeIgniter\Database\RawSql;


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
        $userId = $this->session->get('user_id');
        $userRoleId = $this->session->get('role_id');
        $userRoleName = $this->session->get('role_name');

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses dokumen pribadi Anda.');
        }

        $hrdRole = $this->roleModel->where('name', 'HRD')->first();
        $hrdRoleId = $hrdRole ? $hrdRole['id'] : null;

        $spvRole = $this->roleModel->where('name', 'Supervisor')->first(); // PERBAIKAN: Gunakan nama peran yang benar dari tabel roles
        $spvRoleId = $spvRole ? $spvRole['id'] : null;

        $hrdUserIds = [];
        if ($hrdRoleId) {
            $hrdUsers = $this->userModel->where('role_id', $hrdRoleId)->findAll();
            $hrdUserIds = array_column($hrdUsers, 'id');
        }

        // --- Mulai Builder Query untuk Mengambil Folder ---
        $builder = $this->folderModel
            ->select('folders.*, users.name as owner_display, roles.name as owner_role_name')
            ->join('users', 'users.id = folders.owner_id', 'left')
            ->join('roles', 'roles.id = folders.owner_role', 'left')
            ->where('folders.parent_id', NULL);

        // Tambahkan kondisi OR yang lebih jelas untuk menghindari kesalahan
        $builder->groupStart();
        // Kondisi 1: Folder personal milik SPV sendiri
        $builder->where('folders.owner_id', $userId);

        // Kondisi 2: Folder yang dibuat HRD (owner_id 2 atau 8) DAN owner_role-nya adalah SPV (5)
        $builder->orGroupStart()
            ->whereIn('folders.owner_id', $hrdUserIds)
            ->where('folders.owner_role', $spvRoleId)
            ->groupEnd();

        // Kondisi 3: Folder yang di-share ke peran SPV
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
            'title' => 'Dokumen Pribadi Saya (SPV)',
            'personalFolders' => $personalFolders,
            'orphanFiles' => $orphanFiles,
            'currentFolderId' => null,
            'currentUserId' => $userId,
            'userRoleName' => $userRoleName,
        ];

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
            $data['previewUrl'] = site_url('Supervisor/serve-file/' . $fileId); // Pastikan serve-file punya otorisasi
            return view('Supervisor/view_file_wrapper', $data);
        } else {
            // Untuk DOCX, PPTX, XLSX, dll.: tampilkan halaman info dan tombol unduh
            return view('Supervisor/view_file_khusus', $data);
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
        $hrdDocumentModel = new \App\Models\HrdDocumentModel();
        $data['documents'] = $hrdDocumentModel->getByParent(null); // Mengambil dokumen root level
        $data['parent_id'] = null;
        return view('Umum/dokumenUmum', $data);
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
        $staffRoleId = 5; // Mengasumsikan role_id untuk Staff adalah 6

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

        return view('Supervisor/dashboard', $data);
    }

    public function viewFolder($folderId = null)
    {
        // Cek folder ID
        if ($folderId === null) {
            throw PageNotFoundException::forPageNotFound('Folder ID tidak ditentukan.');
        }

        $userId = $this->session->get('user_id');
        $userRoleId = $this->session->get('role_id'); // Ambil role_id dari sesi
        $userRoleName = $this->session->get('role_name');

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses folder.');
        }

        $currentFolder = $this->folderModel->find($folderId);

        if (!$currentFolder) {
            throw PageNotFoundException::forPageNotFound('Folder tidak ditemukan.');
        }

        $ownerRoleId = $currentFolder['owner_role'] ?? null; // Dapatkan owner_role dari folder
        $hasAccess = false;
        $canManageFolder = false;

        // 🔥 LOGIKA OTORISASI UNIVERSAL DAN KONSISTEN 🔥

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

        $subFolders = $this->folderModel->getSubfoldersWithDetails($folderId, $userId, $userRoleId);
        $filesInFolder = $this->fileModel->getFilesByFolderWithUploader($folderId);
        $breadcrumbs = $this->folderModel->getBreadcrumbs($folderId);

        foreach ($breadcrumbs as &$crumb) {
            $crumb['url'] = base_url('supervisor/folder/' . $crumb['id']);
        }
        unset($crumb);

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
            'canManageFolder' => $canManageFolder,
            'userRoleName' => $userRoleName,
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

        // KASUS 1: Supervisor diizinkan mengunduh semua file
        if ($userRole === 'supervisor') {
            $allowedToDownload = true;
            log_message('info', 'Izin diberikan karena user adalah Supervisor.');
        }

        // KASUS 2: Logika standar untuk pengguna lain (bukan Supervisor)
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
}