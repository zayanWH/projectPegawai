<?php
namespace App\Controllers;

use App\Models\FolderModel;
use App\Models\FileModel;
use App\Models\LogAksesModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use App\Models\ActivityLogsModel;
// use CodeIgniter\Controller;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\API\ResponseTrait;

class DokumenControllerStaff extends BaseController
{
    use ResponseTrait;
    protected $folderModel;
    protected $fileModel;
    protected $roleModel;
    protected $userModel;

    protected $activityLogsModel;
    protected $logAksesModel;


    protected $session;

    public function __construct()
    {
        $this->folderModel = new FolderModel();
        $this->fileModel = new FileModel();
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->logAksesModel = new LogAksesModel();
        $this->activityLogsModel = new ActivityLogsModel();
        $this->session = \Config\Services::session();
        helper(['form', 'url']);
    }

    public function index()
    {
        return redirect()->to(base_url('staff/dokumen-staff'));
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
        $staffRoleId = 6; // Mengasumsikan role_id untuk Staff adalah 6

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

        return view('Staff/dashboard', $data);
    }

    public function search()
    {
        $query = $this->request->getVar('q');
        if (!$query) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Query pencarian tidak ditemukan.']);
        }

        $userId = $this->session->get('user_id');
        $userRoleName = $this->session->get('role_name');

        // Ini akan tetap kosong di log jika masalah sesi belum diperbaiki
        log_message('debug', 'DEBUG: userRoleName from session: "' . $userRoleName . '"');

        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Anda harus login untuk melakukan pencarian.']);
        }

        $hrdRole = $this->roleModel->where('name', 'HRD')->first();
        $hrdRoleId = $hrdRole ? $hrdRole['id'] : null;

        $hrdUserIds = [];
        if ($hrdRoleId) {
            $hrdUsers = $this->userModel->where('role_id', $hrdRoleId)->findAll();
            $hrdUserIds = array_column($hrdUsers, 'id');
        }

        // --- Pencarian Folder ---
        $folderBuilder = $this->folderModel->select("folders.id, folders.name, 'folder' as type");

        $folderBuilder->groupStart();
        $folderBuilder->groupStart()
            ->where('folders.owner_id', $userId)
            ->where('folders.folder_type', 'personal');
        $folderBuilder->groupEnd();

        // --- DEBUGGING SEMENTARA: Kondisi ini diaktifkan jika ada HRD Users ---
        // Biasanya ada 'if ($userRoleName == "Staff" && ...)' di sini.
        // Untuk debugging, kita abaikan dulu cek $userRoleName.
        if (!empty($hrdUserIds)) {
            $folderBuilder->orGroupStart()
                ->whereIn('folders.owner_id', $hrdUserIds);
            $folderBuilder->groupEnd();
        }
        $folderBuilder->groupEnd();

        $folderBuilder->like('folders.name', $query);

        $folders = $folderBuilder->findAll();
        log_message('debug', 'DEBUG: Folder SQL Query: ' . $this->folderModel->getLastQuery()->getQuery());


        // --- Pencarian File ---
        $fileBuilder = $this->fileModel->select("files.id, files.file_name as name, 'file' as type, files.folder_id");

        $fileBuilder->groupStart();
        $fileBuilder->where('files.uploader_id', $userId);

        // --- DEBUGGING SEMENTARA: Kondisi ini diaktifkan jika ada HRD Users ---
        // Biasanya ada 'if ($userRoleName == "Staff" && ...)' di sini.
        // Untuk debugging, kita abaikan dulu cek $userRoleName.
        if (!empty($hrdUserIds)) {
            $fileBuilder->orWhereIn('files.folder_id', function ($builder) use ($hrdUserIds) {
                $builder->select('id')
                    ->from('folders')
                    ->whereIn('owner_id', $hrdUserIds);
            });
        }
        $fileBuilder->groupEnd();

        $fileBuilder->like('files.file_name', $query);

        $files = $fileBuilder->findAll();
        log_message('debug', 'DEBUG: File SQL Query: ' . $this->fileModel->getLastQuery()->getQuery());

        $results = array_merge($folders, $files);

        log_message('debug', 'Combined search results: ' . json_encode($results));

        return $this->response->setJSON($results);
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
            $data['previewUrl'] = site_url('staff/serve-file/' . $fileId); // Pastikan serve-file punya otorisasi
            return view('Staff/view_file_wrapper', $data);
        } else {
            // Untuk DOCX, PPTX, XLSX, dll.: tampilkan halaman info dan tombol unduh
            return view('Staff/view_file_khusus', $data);
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

        $this->logAkses($userId, $file, 'preview');

        // Tentukan Content-Type berdasarkan ekstensi file
        $mime = mime_content_type($filePath);

        // Atur header untuk menampilkan file di browser (inline)
        $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . basename($filePath) . '"')
            ->setBody(file_get_contents($filePath));

        return $this->response;
    }

    protected function logAkses(?int $userId, array $fileData, string $aksi)
    {
        $roleName = 'Guest'; // Default value jika user tidak ditemukan atau tidak login

        if ($userId) {
            // Ambil data user beserta role_id-nya
            $user = $this->userModel->find($userId);

            if ($user && $user['role_id']) {
                // Ambil nama role berdasarkan role_id
                $role = $this->roleModel->find($user['role_id']);
                if ($role) {
                    $roleName = $role['name'];
                }
            }
        }

        $dataToLog = [
            'user_id' => $userId, // Bisa null jika Guest
            'role' => $roleName, // Nama role yang sudah didapatkan
            'file_id' => $fileData['id'],
            'file_name' => $fileData['file_name'],
            'aksi' => $aksi,
            // 'timestamp' akan otomatis diisi oleh model karena useTimestamps = true
        ];

        // Simpan data log
        $this->logAksesModel->insert($dataToLog);
    }


    public function dokumenStaff()
    {
        $userId = $this->session->get('user_id');
        $userRoleName = $this->session->get('role_name'); // Misal: 'Staff'

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses dokumen.');
        }

        // Dapatkan ID role 'HRD'
        $hrdRole = $this->roleModel->where('name', 'HRD')->first();
        $hrdRoleId = $hrdRole ? $hrdRole['id'] : null;

        // Dapatkan semua user_id yang memiliki role_id 'HRD'
        $hrdUserIds = [];
        if ($hrdRoleId) {
            $hrdUsers = $this->userModel->where('role_id', $hrdRoleId)->findAll();
            $hrdUserIds = array_column($hrdUsers, 'id');
        }

        // --- Mulai Builder Query untuk Mengambil Folder ---
        $builder = $this->folderModel
            ->select('folders.*, users.name as owner_display, roles.name as owner_role_name')
            ->join('users', 'users.id = folders.owner_id', 'left')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->where('folders.parent_id', NULL); // Hanya di root

        // Kondisi untuk Staff:
        // 1. Folder personal milik Staff yang sedang login
        // 2. ATAU Folder yang owner_id-nya adalah HRD
        $builder->groupStart()
            ->groupStart() // KELOMPOK 1: Folder Personal Milik Staff
            ->where('folders.owner_id', $userId)
            ->where('folders.folder_type', 'personal')
            ->groupEnd()
            ->orGroupStart() // KELOMPOK 2: Folder Milik HRD
            ->whereIn('folders.owner_id', $hrdUserIds) // Folder yang owner_id-nya adalah ID HRD
            // Anda bisa menambahkan filter lain di sini jika ingin hanya folder personal HRD atau shared HRD
            // ->where('folder.folder_type', 'personal') // Jika hanya ingin personal folder HRD
            ->groupEnd()
            ->groupEnd();

        $personalFolders = $builder->findAll();


        // Query untuk file yatim (orphan files) milik Staff yang sedang login (tidak ada di folder manapun)
        $orphanFiles = $this->fileModel->where('uploader_id', $userId)
            ->where('folder_id', NULL)
            ->findAll();

        $data = [
            'title' => 'Dokumen Saya',
            'personalFolders' => $personalFolders,
            'orphanFiles' => $orphanFiles,
            // Data untuk JavaScript frontend (penting!)
            'currentFolderId' => null, // Karena ini root folder
            'currentUserId' => $userId,
            'userRoleName' => $userRoleName,
        ];

        return view('staff/dokumenStaff', $data);
    }

    public function viewFolder($folderId = null)
    {
        // --- DEBUG: Mulai fungsi viewFolder ---
        log_message('debug', 'DokumenControllerStaff::viewFolder: Fungsi dimulai untuk Folder ID: ' . ($folderId ?? 'NULL'));

        if ($folderId === null) {
            log_message('error', 'DokumenControllerStaff::viewFolder: Folder ID tidak ditentukan.');
            throw PageNotFoundException::forPageNotFound('Folder ID tidak ditentukan.');
        }

        $userId = $this->session->get('user_id'); // Menggunakan $this->session
        log_message('debug', 'DokumenControllerStaff::viewFolder: User ID dari sesi: ' . ($userId ?? 'NULL'));

        if (!$userId) {
            log_message('warning', 'DokumenControllerStaff::viewFolder: Pengguna tidak login, redirect ke halaman login.');
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses folder.');
        }

        $currentFolder = $this->folderModel->find($folderId);
        log_message('debug', 'DokumenControllerStaff::viewFolder: Folder ditemukan: ' . json_encode($currentFolder));

        if (!$currentFolder) {
            log_message('error', 'DokumenControllerStaff::viewFolder: Folder dengan ID ' . $folderId . ' tidak ditemukan.');
            throw PageNotFoundException::forPageNotFound('Folder tidak ditemukan.');
        }

        // --- PERHATIKAN INI: Ganti 'user_role' menjadi 'role_id' jika itu yang Anda set di LoginController ---
        $userRole = $this->session->get('role_id'); // Kunci sesi yang benar untuk role_id
        log_message('debug', 'DokumenControllerStaff::viewFolder: User Role dari sesi: ' . ($userRole ?? 'NULL'));

        // --- Ambil role_id dari pemilik folder ---
        // Pilihan 1: Langsung dari kolom 'owner_role' di tabel folders (ini yang ideal jika sudah terisi)
        $ownerRoleId = $currentFolder['owner_role'] ?? null;
        log_message('debug', 'DokumenControllerStaff::viewFolder: Owner Role ID dari folder (kolom owner_role): ' . ($ownerRoleId ?? 'NULL'));

        // Pilihan 2: Fallback ke tabel users jika 'owner_role' di folder NULL
        if ($ownerRoleId === null && isset($currentFolder['owner_id'])) {
            $ownerUser = $this->userModel->find($currentFolder['owner_id']);
            if ($ownerUser) {
                $ownerRoleId = $ownerUser['role_id'] ?? null;
                log_message('debug', 'DokumenControllerStaff::viewFolder: Owner Role ID (fallback dari userModel): ' . ($ownerRoleId ?? 'NULL'));
            } else {
                log_message('warning', 'DokumenControllerStaff::viewFolder: Owner user dengan ID ' . $currentFolder['owner_id'] . ' tidak ditemukan.');
            }
        }

        // Pastikan ownerRoleId masih NULL setelah mencoba kedua cara, maka mungkin ada masalah dengan data.
        if ($ownerRoleId === null) {
            log_message('error', 'DokumenControllerStaff::viewFolder: Owner Role ID tidak dapat ditentukan untuk folder ini.');
            // Anda mungkin ingin mengembalikan error atau redirect jika role_id tidak bisa ditemukan.
            // Misalnya: return redirect()->to(base_url('staff/dokumen-staff'))->with('error', 'Data folder tidak lengkap.');
        }

        // Logika akses untuk folder 'personal'
        if ($currentFolder['folder_type'] === 'personal') {
            log_message('debug', 'DokumenControllerStaff::viewFolder: Tipe folder: PERSONAL');
            $hasAccess = false;

            // Pemilik folder selalu punya akses
            if ((int) $currentFolder['owner_id'] === (int) $userId) {
                $hasAccess = true;
                log_message('debug', 'DokumenControllerStaff::viewFolder: Akses diberikan: Pengguna adalah pemilik folder.');
            }
            // Admin (role 1) bisa mengakses folder Staff (role 6)
            else if ((int) $userRole === 1 && (int) $ownerRoleId === 6) {
                $hasAccess = true;
                log_message('debug', 'DokumenControllerStaff::viewFolder: Akses diberikan: Admin (' . $userRole . ') mengakses folder Staff (' . $ownerRoleId . ').');
            }
            // Staff (role 6) bisa mengakses folder Admin (role 1)
            else if ((int) $userRole === 6 && (int) $ownerRoleId === 1) {
                $hasAccess = true;
                log_message('debug', 'DokumenControllerStaff::viewFolder: Akses diberikan: Staff (' . $userRole . ') mengakses folder Admin (' . $ownerRoleId . ').');
            }
            // --- TAMBAHKAN LOGIKA INI UNTUK MEMUNGKINKAN STAFF MELIHAT FOLDER PERSONAL HRD ---
            else if ((int) $userRole === 6 && (int) $ownerRoleId === 2) { // Asumsi ID role HRD adalah 2
                $hasAccess = true;
                log_message('debug', 'DokumenControllerStaff::viewFolder: Akses diberikan: Staff (' . $userRole . ') mengakses folder personal HRD (' . $ownerRoleId . ').');
            }
            // --- Opsional: Tambahkan logika agar HRD bisa melihat folder personal Staff ---
            else if ((int) $userRole === 2 && (int) $ownerRoleId === 6) { // Asumsi ID role HRD adalah 2, ID role Staff adalah 6
                $hasAccess = true;
                log_message('debug', 'DokumenControllerStaff::viewFolder: Akses diberikan: HRD (' . $userRole . ') mengakses folder personal Staff (' . $ownerRoleId . ').');
            }
            // --- End Tambahan ---
            else {
                log_message('debug', 'DokumenControllerStaff::viewFolder: Tidak ada akses berdasarkan owner_id atau role khusus untuk folder personal. UserRole: ' . $userRole . ', OwnerRoleId: ' . $ownerRoleId);
            }

            if (!$hasAccess) {
                log_message('warning', 'DokumenControllerStaff::viewFolder: Akses ditolak untuk folder personal ini. User ID: ' . $userId . ', User Role: ' . $userRole . ', Folder Owner ID: ' . $currentFolder['owner_id'] . ', Folder Owner Role: ' . $ownerRoleId);
                return redirect()->to(base_url('staff/dokumen-staff'))->with('error', 'Anda tidak memiliki akses ke folder personal ini.');
            }
        }

        // --- Logika akses untuk folder 'shared' ---
        else if ($currentFolder['folder_type'] === 'shared') {
            log_message('debug', 'DokumenControllerStaff::viewFolder: Tipe folder: SHARED');

            // Cek apakah pengguna adalah pemilik folder shared
            if ((int) $currentFolder['owner_id'] === (int) $userId) {
                log_message('debug', 'DokumenControllerStaff::viewFolder: Akses diberikan: Pengguna adalah pemilik folder shared.');
                $hasAccess = true; // Set hasAccess untuk shared folder jika pemilik
            } else {
                // Jika bukan pemilik, cek access_roles
                $accessRoles = json_decode($currentFolder['access_roles'] ?? '[]', true);
                log_message('debug', 'DokumenControllerStaff::viewFolder: Shared folder. Access Roles di folder: ' . json_encode($accessRoles));
                log_message('debug', 'DokumenControllerStaff::viewFolder: User Role: ' . $userRole);

                // Pastikan $accessRoles adalah array dan $userRole ada di dalamnya
                if (is_array($accessRoles) && !empty($accessRoles) && in_array((int) $userRole, array_map('intval', $accessRoles))) {
                    $hasAccess = true;
                    log_message('debug', 'DokumenControllerStaff::viewFolder: Akses diberikan: User Role ditemukan di Access Roles folder shared.');
                } else {
                    $hasAccess = false;
                    log_message('warning', 'DokumenControllerStaff::viewFolder: Akses ditolak untuk folder shared. User Role (' . $userRole . ') tidak ada dalam Access Roles (' . json_encode($accessRoles) . ').');
                }
            }

            if (!$hasAccess) {
                return redirect()->to(base_url('staff/dokumen-staff'))->with('error', 'Anda tidak memiliki izin untuk folder shared ini.');
            }
        }

        // --- Logika akses untuk folder 'public' ---
        else if ($currentFolder['folder_type'] === 'public') {
            log_message('debug', 'DokumenControllerStaff::viewFolder: Tipe folder: PUBLIC. Akses diberikan.');
            // Folder public secara default dapat diakses oleh siapa saja yang login
            // Tidak perlu ada logika akses tambahan di sini, sudah diasumsikan bisa diakses
        }


        $subFolders = $this->folderModel->where('parent_id', $folderId)->findAll();
        $filesInFolder = $this->fileModel->where('folder_id', $folderId)->findAll();
        $breadcrumbs = $this->folderModel->getBreadcrumbs($folderId);

        log_message('debug', 'DokumenControllerStaff::viewFolder: Subfolders ditemukan: ' . count($subFolders));
        log_message('debug', 'DokumenControllerStaff::viewFolder: Files ditemukan: ' . count($filesInFolder));
        log_message('debug', 'DokumenControllerStaff::viewFolder: Breadcrumbs: ' . json_encode($breadcrumbs));


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
            // Tambahkan data debug ke view jika perlu
            'debugUserId' => $userId,
            'debugUserRole' => $userRole,
            'debugOwnerRoleId' => $ownerRoleId,
            'debugCurrentFolder' => $currentFolder,
        ];

        log_message('debug', 'DokumenControllerStaff::viewFolder: Mengirim data ke view staff/viewFolder.');
        return view('staff/viewFolder', $data);
    }
    public function createFolder()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
        }

        $json = $this->request->getJSON();
        if (empty($json)) {
            return $this->failValidationErrors(['data' => 'Tidak ada data JSON yang diterima.']);
        }

        $userId = $this->session->get('user_id');
        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized. User not logged in.']);
        }

        $input = $this->request->getJSON(true);

        $folderName = $input['name'] ?? null;
        $parentId = $input['parent_id'] ?? null;
        // Ambil folder_type, is_shared, shared_type, access_roles dari parent jika ada
        // atau gunakan default jika di root.
        // PENTING: Jangan ambil langsung dari input client jika parent_id ada,
        // karena parent_id akan menentukan tipe folder.
        $folderType = 'personal';
        $isShared = 0;
        $sharedType = null;
        $accessRoles = null;

        if ($parentId) {
            $parentFolder = $this->folderModel->find($parentId);
            if ($parentFolder) {
                $folderType = $parentFolder['folder_type'];
                $isShared = (int) $parentFolder['is_shared'];
                $sharedType = $parentFolder['shared_type'];
                // Pastikan access_roles di-decode
                $accessRoles = json_decode($parentFolder['access_roles'] ?? '[]', true);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Parent folder tidak ditemukan.']);
            }
        } else {
            // Jika di root, dan user memilih 'shared', set defaultnya
            // Ini akan mengganti default 'personal' di atas
            if (($input['folder_type'] ?? 'personal') === 'shared') {
                $folderType = 'shared';
                $isShared = 1;
                $sharedType = $input['shared_type'] ?? 'read_write'; // Anda bisa sesuaikan default
                $accessRoles = $input['access_roles'] ?? ['Super Admin', 'Staff']; // Default role untuk shared
            }
        }

        // Aturan validasi (gunakan validateData jika input adalah array)
        // Karena Anda sudah extract ke variabel terpisah, $this->validate() bisa saja bekerja,
        // tapi validateData($input, $rules) lebih eksplisit untuk JSON.
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
        ];
        if (!$this->validateData(['name' => $folderName], $rules, ['name' => ['required' => 'Nama folder tidak boleh kosong.']])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Validasi gagal.', 'errors' => $this->validator->getErrors()]);
        }

        $data = [
            'name' => $folderName,
            'parent_id' => $parentId,
            'owner_id' => $userId, // Owner selalu user yang login
            'folder_type' => $folderType,
            'is_shared' => $isShared,
            'shared_type' => $sharedType,
            'access_roles' => !empty($accessRoles) ? json_encode($accessRoles) : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // --- Mulai proses insert dan pembuatan folder fisik ---
        if ($this->folderModel->insert($data)) {
            $newFolderId = $this->folderModel->insertID();
            $relativePath = $this->folderModel->getFolderPath($newFolderId); // Ambil path relatif dari model
            $folderPath = WRITEPATH . 'uploads/' . $relativePath;

            try {
                if (!is_dir($folderPath)) {
                    // Coba buat folder fisik dengan izin 0777 (read, write, execute untuk semua)
                    if (!mkdir($folderPath, 0777, true)) {
                        // Jika mkdir gagal tapi tidak melempar Exception (sangat jarang)
                        log_message('error', 'Gagal membuat direktori fisik (mkdir() mengembalikan false): ' . $folderPath);
                        $this->folderModel->delete($newFolderId); // Rollback DB
                        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membuat folder fisik di server (mkdir() false).']);
                    }
                }

                // 🔥 LOG ACTIVITY - FOLDER CREATED
                // KODE DIPERBARUI: Menambahkan nama folder ($json->name) ke log
                $this->activityLogsModel->logActivity($userId, 'create_folder', 'folder', $newFolderId, $json->name);

                // Jika semua sukses (DB insert dan folder fisik berhasil dibuat)
                return $this->response->setJSON(['status' => 'success', 'message' => 'Folder berhasil dibuat!', 'new_folder_id' => $newFolderId]);

            } catch (\Throwable $e) { // Tangkap Throwable untuk Error dan Exception
                log_message('critical', 'EXCEPTION: Gagal membuat folder fisik. ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());

                // Rollback database: Hapus entri folder yang sudah dibuat jika folder fisik gagal
                $this->folderModel->delete($newFolderId);

                // Kirim response error ke frontend
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membuat folder fisik (izin ditolak atau kesalahan lain).', 'debug_info' => $e->getMessage()]);
            }
        } else {
            // Ini adalah blok untuk kegagalan insert database
            $errors = $this->folderModel->errors();
            log_message('error', 'Gagal insert folder ke database: ' . json_encode($errors));
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan data folder ke database.', 'errors' => $errors]);
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

    public function downloadFile($fileId)
    {
        $userId = $this->session->get('user_id');

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengunduh file.');
        }

        $file = $this->fileModel->find($fileId);

        if (!$file) {
            throw PageNotFoundException::forPageNotFound('File tidak ditemukan.');
        }

        $allowedToDownload = false;
        if ($file['folder_id']) {
            $parentFolder = $this->folderModel->find($file['folder_id']);
            if ($parentFolder) {
                if ($parentFolder['folder_type'] === 'personal' && $parentFolder['owner_id'] === $userId) {
                    $allowedToDownload = true;
                } elseif ($parentFolder['folder_type'] === 'shared') {
                    $userRole = $this->session->get('user_role');
                    $accessRoles = json_decode($parentFolder['access_roles'] ?? '[]', true);

                    if (in_array($userRole, $accessRoles) || $parentFolder['owner_id'] === $userId) {
                        $allowedToDownload = true;
                    }
                }
            }
        } else {
            if ($file['uploader_id'] === $userId) {
                $allowedToDownload = true;
            }
        }

        if (!$allowedToDownload) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengunduh file ini.');
        }

        $this->fileModel->update($fileId, ['download_count' => ($file['download_count'] ?? 0) + 1]);

        $filePath = WRITEPATH . 'uploads/' . $file['file_path'];

        if (!file_exists($filePath)) {
            throw PageNotFoundException::forPageNotFound('File tidak ditemukan di server.');
        }

        return $this->response->download($filePath, null)->setFileName($file['file_name']);
    }

    public function dokumenBersama()
    {
        $session = session();
        $userId = $session->get('user_id');
        $roleId = $session->get('role_id');

        if (!$userId) {
            log_message('warning', 'DokumenControllerStaff::dokumenBersama(): User ID tidak ditemukan di sesi. Redirecting ke login.');
            return redirect()->to(base_url('login'));
        }

        $userRoleData = $this->roleModel->find($roleId);
        $userRoleName = $userRoleData['name'] ?? 'Unknown';

        log_message('debug', 'DokumenControllerStaff::dokumenBersama(): User ID: ' . $userId . ', Role ID: ' . $roleId . ', Nama Role: ' . $userRoleName);

        // PENTING: Implementasi getSharedFoldersForUser harus ada di FolderModel Anda
        // Contoh implementasi di FolderModel:
        // public function getSharedFoldersForUser($userId, $userRoleName) {
        //     return $this->where('is_shared', 1)
        //                 ->groupStart()
        //                 ->where('owner_id', $userId) // Folder miliknya sendiri
        //                 ->orWhere("JSON_CONTAINS(access_roles, '\"{$userRoleName}\"')") // Folder di-share ke role
        //                 ->orWhere('shared_type', 'public') // Folder di-share publik (jika ada)
        //                 ->groupEnd()
        //                 ->findAll();
        // }

        $sharedFolders = $this->folderModel->getSharedFoldersForUser($userId, $userRoleName);

        // Membersihkan access_roles jika null atau bukan JSON valid
        foreach ($sharedFolders as &$folder) {
            if ($folder['access_roles'] !== null) {
                $decodedRoles = json_decode($folder['access_roles'], true);
                if (!is_array($decodedRoles)) {
                    $folder['access_roles'] = '[]'; // Set ke array kosong jika decode gagal
                }
            } else {
                $folder['access_roles'] = '[]'; // Set ke array kosong jika null
            }
        }
        unset($folder); // Penting untuk unset referensi setelah loop

        $data = [
            'title' => 'Dokumen Bersama',
            'sharedFolders' => $sharedFolders, // Ini yang akan dikirim ke view
            'sharedFiles' => [], // Isi jika Anda ingin menampilkan file di halaman ini
            'currentFolderId' => null,
            'currentUserId' => $userId,
            'userRoleName' => $userRoleName,
            'breadcrumbs' => []
        ];

        return view('Umum/dokumenBersama', $data);
    }

    public function viewSharedFolder($folderId = null)
    {
        return $this->dokumenBersama($folderId); // Lewatkan $folderId ke dokumenBersama
    }

    /**
     * Helper function untuk membangun breadcrumbs.
     * @param int|null $folderId
     * @param string $baseRoute
     * @return array
     */
    private function getBreadcrumbs(string $baseRoute, $folderId = null)
    {
        $breadcrumbs = [];
        $currentFolder = null;

        if ($folderId) {
            $currentFolder = $this->folderModel->find($folderId);
        }

        // Loop akan berjalan mundur dari folder saat ini ke atas
        while ($currentFolder) {
            // Periksa apakah data folder valid sebelum mengaksesnya
            if (!isset($currentFolder['name'])) {
                // Jika data tidak valid (misalnya, folder induk tidak ada),
                // hentikan perulangan untuk mencegah error.
                break;
            }

            array_unshift($breadcrumbs, [
                'name' => $currentFolder['name'],
                'id' => $currentFolder['id'],
                'url' => base_url("staff/{$baseRoute}/{$currentFolder['id']}")
            ]);

            // Ambil parent_id dari folder saat ini
            $parentId = $currentFolder['parent_id'];

            // Hentikan perulangan jika sudah sampai di root (parent_id adalah null)
            if ($parentId === null) {
                break;
            }

            // Cari folder induk berdasarkan parent_id
            $currentFolder = $this->folderModel->find($parentId);

            // Periksa lagi jika folder induk tidak ditemukan, lalu hentikan loop
            if ($currentFolder === null) {
                break;
            }
        }

        return $breadcrumbs;
    }

    public function dokumenUmum()
    {
        $userId = $this->session->get('user_id');

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses dokumen umum.');
        }
        $publicFolders = $this->folderModel
            ->where('folder_type', 'public')
            ->findAll();

        $data = [
            'title' => 'Dokumen Umum',
            'publicFolders' => $publicFolders,
        ];

        return view('Umum/dokumenUmum', $data);
    }
}