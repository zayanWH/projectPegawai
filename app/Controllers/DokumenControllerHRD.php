<?php

namespace App\Controllers;

use App\Models\FolderModel;
use App\Models\FileModel;
use App\Models\HrdDocumentModel;
use App\Models\UserModel;
use App\Models\NotificationsModel;
use App\Models\ActivityLogsModel;
use App\Models\RoleModel;
use CodeIgniter\Files\File;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Exceptions\AccessDeniedException;

class DokumenControllerHRD extends BaseController
{
    protected $folderModel;
    protected $fileModel;
    protected $hrdDocumentModel;
    protected $userModel;

    protected $activityLogsModel; // Model untuk aktivitas log
    protected $roleModel; // Deklarasi properti
    protected $helpers = ['form', 'url', 'filesystem', 'session'];
    protected $session;

    public function __construct()
    {
        $this->folderModel = new FolderModel();
        $this->fileModel = new FileModel();
        $this->hrdDocumentModel = new HrdDocumentModel();
        $this->activityLogsModel = new ActivityLogsModel();
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel(); // Inisialisasi properti
        $this->session = \Config\Services::session();
        helper('session');
    }



    /**
     * Melayani file (menampilkan atau mengunduh) berdasarkan ID file.
     */
    public function serveFile($fileId)
    {
        $session = session();
        $userRole = $session->get('role');
        $userId = $session->get('user_id');

        $file = $this->fileModel->find($fileId);

        if (!$file) {
            throw PageNotFoundException::forPageNotFound('File tidak ditemukan.');
        }

        // Logika Otorisasi:
        if ($userRole === 'hrd') {
            // HRD memiliki akses penuh, lanjutkan
        } elseif (isset($file['uploader_id']) && $file['uploader_id'] == $userId) { // Pastikan uploader_id ada
            // Pengguna adalah pemilik file, lanjutkan
        } else {
            // Jika AccessDeniedException Anda adalah custom class, pastikan di-import di atas.
            // Jika tidak, gunakan generic Exception atau PageNotFoundException untuk menyembunyikan informasi.
            throw new Exception('Anda tidak memiliki izin untuk melihat file ini.');
            // Atau: throw PageNotFoundException::forPageNotFound('Akses ditolak.');
        }

        // --- INI ADALAH BAGIAN UTAMA YANG DIPERBAIKI ---
        // Pastikan kolom-kolom ini ada dan terisi dari database
        if (
            !isset($file['file_path']) || empty($file['file_path']) ||
            !isset($file['server_file_name']) || empty($file['server_file_name'])
        ) {
            log_message('error', 'File ID ' . $fileId . ' ditemukan di DB, tetapi "file_path" atau "server_file_name" tidak ada atau kosong.');
            throw PageNotFoundException::forPageNotFound('Informasi path file tidak lengkap.');
        }

        // Mengkonstruksi path fisik lengkap menggunakan WRITEPATH, file_path (direktori), dan server_file_name (nama file unik)
        $filePath = WRITABLE_PATH . $file['file_path'] . $file['server_file_name'];
        if (!file_exists($filePath)) {
            log_message('error', 'File fisik tidak ditemukan di: ' . $filePath . ' untuk File ID: ' . $fileId);
            throw PageNotFoundException::forPageNotFound('File fisik tidak ditemukan di server.');
        }

        // Pastikan 'type' dan 'file_name' ada
        if (!isset($file['type']) || empty($file['type']) || !isset($file['file_name']) || empty($file['file_name'])) {
            log_message('error', 'File ID ' . $fileId . ' ditemukan, tetapi "type" atau "file_name" tidak lengkap.');
            throw PageNotFoundException::forPageNotFound('Informasi MIME type atau nama file asli tidak lengkap.');
        }

        $mimeType = $file['type']; // Menggunakan 'type' dari DB
        $originalFileName = $file['file_name']; // Menggunakan 'file_name' dari DB

        if (strpos($mimeType, 'image/') === 0 || $mimeType === 'application/pdf') {
            header('Content-Type: ' . $mimeType);
            readfile($filePath);
            exit;
        } else {
            return $this->response->download($filePath, null)->setFileName($originalFileName);
        }
    }


    public function index()
    {
        // 1. Mendapatkan total jumlah folder
        $totalFolders = $this->folderModel->countAllResults();

        // 2. Mendapatkan total jumlah file HRD
        $totalHrdFiles = $this->hrdDocumentModel->countAllResults();

        // 3. Mendapatkan total jumlah user
        $totalUsers = $this->userModel->countAllResults();

        // 4. Mendapatkan total jumlah file dari tabel files
        $totalFiles = $this->fileModel->countAllResults();

        // --- Bagian untuk Dokumen Terbaru ---

        // Ambil 10 folder terbaru
        $recentFolders = $this->folderModel
            ->select('id, name, parent_id, owner_id, created_at, "folder" as type')
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->findAll();

        // Ambil 10 file terbaru
        $recentFiles = $this->fileModel
            ->select('id, file_name as name, folder_id as parent_id, uploader_id as owner_id, created_at, "file" as type, server_file_name') // Menambahkan server_file_name
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->findAll();

        // Ambil 10 dokumen HRD terbaru
        $recentHrdDocuments = $this->hrdDocumentModel
            ->select('id, category as name, created_at, "hrd_doc" as type, file_id')
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->findAll();

        // Gabungkan semua hasil
        $allRecentDocuments = array_merge($recentFolders, $recentFiles, $recentHrdDocuments);

        // Urutkan ulang berdasarkan created_at (terbaru pertama)
        usort($allRecentDocuments, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        // Ambil hanya 10 data teratas setelah diurutkan
        $latestDocuments = array_slice($allRecentDocuments, 0, 10);

        // Ambil informasi tambahan seperti nama folder induk dan nama pengunggah
        foreach ($latestDocuments as &$doc) {
            // Tentukan ikon berdasarkan tipe
            if ($doc['type'] === 'folder') {
                $doc['icon_class'] = 'w-5 h-5 text-gray-500 mr-2';
                $doc['icon_path'] = '<path d="M2 6a2 2 0 012-2h5l2 2h7a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />';
                $doc['display_name'] = $doc['name'];
            } elseif ($doc['type'] === 'file') {
                $doc['icon_class'] = 'w-5 h-5 text-red-500 mr-2';
                $doc['icon_path'] = '<path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>';
                $doc['display_name'] = $doc['name'];
            } elseif ($doc['type'] === 'hrd_doc') {
                $doc['icon_class'] = 'w-5 h-5 text-blue-500 mr-2';
                $doc['icon_path'] = '<path d="M10 2a2 2 0 00-2 2v2H6a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2V8a2 2 0 00-2-2h-2V4a2 2 0 00-2-2zM9 4a1 1 0 011-1h2a1 1 0 011 1v2H9V4z" />';
                $doc['display_name'] = $doc['name'];
            }

            // Dapatkan nama folder induk
            $doc['parent_folder_name'] = 'N/A';
            if (isset($doc['parent_id']) && $doc['parent_id']) {
                $parentFolder = $this->folderModel->find($doc['parent_id']);
                if ($parentFolder) {
                    $doc['parent_folder_name'] = $parentFolder['name'];
                }
            } elseif ($doc['type'] === 'hrd_doc') {
                $doc['parent_folder_name'] = 'HRD Documents';
            }

            // Dapatkan nama pengunggah/pemilik
            $doc['uploader_name'] = 'System';
            $ownerId = null;
            // Cek owner_id di folder dan file
            if (isset($doc['owner_id']) && $doc['owner_id']) {
                $ownerId = $doc['owner_id'];
            }
            // Jika dokumen adalah dokumen HRD dan memiliki file_id, coba dapatkan pengunggah dari file yang terkait
            // Bagian ini mengasumsikan bahwa hrd_documents.file_id terhubung ke files.id dan files.uploader_id adalah pengunggah
            if ($doc['type'] === 'hrd_doc' && isset($doc['file_id'])) {
                $linkedFile = $this->fileModel->find($doc['file_id']);
                if ($linkedFile && isset($linkedFile['uploader_id'])) {
                    $ownerId = $linkedFile['uploader_id'];
                }
            }

            if ($ownerId) {
                $user = $this->userModel->find($ownerId);
                if ($user) {
                    $doc['uploader_name'] = $user['name'];
                }
            } else if ($doc['type'] === 'hrd_doc') {
                // Jika tidak ditemukan owner_id untuk dokumen HRD, default ke HRD Admin atau pengguna sesi saat ini
                $doc['uploader_name'] = session()->get('name') ?? 'HRD Admin';
            }
        }

        // Mengirim semua data yang diperlukan ke view
        $data['totalFolders'] = $totalFolders;
        $data['totalHrdFiles'] = $totalHrdFiles;
        $data['totalUser'] = $totalUsers;
        $data['totalFiles'] = $totalFiles;
        $data['latestDocuments'] = $latestDocuments;

        return view('HRD/dashboard', $data);
    }

    public function dokumenStaff()
    {
        $session = session();
        $hrdUserId = $session->get('user_id');
        $hrdRoleId = $session->get('role_id');
        $staffRoleId = 6;
        $folders = $this->folderModel->getHRDViewForRole($hrdUserId, $hrdRoleId, $staffRoleId);
        $data['personalFolders'] = $folders;

        return view('HRD/dokumenStaff', $data);
    }
    public function search()
    {
        $query = $this->request->getVar('q');
        if (!$query) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Query is missing.']);
        }

        // --- TAMBAH INI UNTUK SESI YANG JELAS ---
        $session = \Config\Services::session(); // Menggunakan Services untuk mendapatkan instance session
        // ATAU gunakan helper session() yang lebih ringkas:
        // $session = session();
        // ----------------------------------------

        $userId = $session->get('user_id'); // User yang sedang melakukan pencarian (HRD)
        $userRoleName = $session->get('role_name'); // Peran user yang sedang mencari (misal: 'HRD')

        // Dapatkan ID role 'Staff'
        $staffRole = $this->roleModel->where('name', 'Staff')->first();
        $staffRoleId = $staffRole ? $staffRole['id'] : null;

        // Dapatkan semua user_id yang memiliki role_id 'Staff'
        $staffUserIds = [];
        if ($staffRoleId) {
            $staffUsers = $this->userModel->where('role_id', $staffRoleId)->findAll();
            $staffUserIds = array_column($staffUsers, 'id');
        }

        // --- Pencarian Folder ---
        $folderBuilder = $this->folderModel
            ->select('folders.id, folders.name, folders.owner_id, folders.folder_type, folders.is_shared, folders.shared_type, folders.access_roles, users.name as owner_display, roles.name as owner_role_name, "folder" as type');

        // Join untuk mendapatkan info owner
        $folderBuilder->join('users', 'users.id = folders.owner_id', 'left');
        $folderBuilder->join('roles', 'roles.id = users.role_id', 'left');

        // Kondisi Pencarian untuk Folder (Mirip dengan dokumenStaff HRD)
        $folderBuilder->groupStart()
            // A. Folder milik HRD yang sedang login
            ->where('folders.owner_id', $userId) // Menggunakan 'folder.owner_id' karena tabel aliased 'folder'
            // B. Folder yang dibuat oleh Staff
            ->orWhereIn('folders.owner_id', $staffUserIds) // Menggunakan 'folder.owner_id'
            ->groupEnd();

        // Tambahkan kondisi pencarian berdasarkan nama
        $folderBuilder->like('folders.name', $query); // Menggunakan 'folder.name'

        $folders = $folderBuilder->findAll();

        // --- Pencarian File ---
        // Asumsi struktur tabel 'files' memiliki kolom 'uploader_id' dan 'folder_id'
        $fileBuilder = $this->fileModel
            ->select('files.id, files.file_name as name, files.uploader_id, files.folder_id, "file" as type, users.name as uploader_display, roles.name as uploader_role_name, folders.name as folder_name');

        // Join untuk mendapatkan info uploader dan folder
        $fileBuilder->join('users', 'users.id = files.uploader_id', 'left');
        $fileBuilder->join('roles', 'roles.id = users.role_id', 'left');
        $fileBuilder->join('folders', 'folders.id = files.folder_id', 'left'); // Untuk mendapatkan nama folder

        // Kondisi Pencarian untuk File (Mirip dengan folder)
        $fileBuilder->groupStart()
            // A. File yang diupload oleh HRD yang sedang login
            ->where('files.uploader_id', $userId)
            // B. File yang diupload oleh Staff
            ->orWhereIn('files.uploader_id', $staffUserIds)
            // C. File di dalam folder yang di-share dan relevan untuk HRD
            // (Ini agak kompleks karena izin file biasanya mengikuti izin folder induk)
            // Untuk kesederhanaan, kita bisa asumsikan jika folder-nya bisa dilihat HRD, maka file-nya juga.
            // Atau, tambahkan logika spesifik untuk shared files jika Anda punya kolom is_shared di tabel files.
            // Jika tidak, kita akan mengandalkan izin folder.

            // OPSI: Jika files juga punya kolom is_shared dan access_roles:
            // ->orGroupStart()
            //     ->where('files.is_shared', 1);
            //     if ($userRoleName) {
            //         $fileBuilder->like('files.access_roles', '"' . $userRoleName . '"');
            //     } else {
            //         $fileBuilder->where('1=0');
            //     }
            // ->groupEnd()

            ->groupEnd();

        // Tambahkan kondisi pencarian berdasarkan nama file
        $fileBuilder->like('files.file_name', $query); // Asumsi kolom nama file adalah 'name'

        $files = $fileBuilder->findAll();

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

        $hrdUserId = $this->session->get('user_id'); // User ID HRD yang sedang login
        $hrdRoleId = $this->session->get('role_id'); // Role ID HRD yang sedang login

        // Asumsi ID role Staff
        $staffRoleId = 6; // Ganti dengan ID role Staff yang sesuai di database Anda.

        // --- Perbaikan Query Folder ---
        $folderModel = new FolderModel();
        $folderBuilder = $folderModel->builder();
        $folderBuilder->select("folders.id, folders.name, 'folder' as type, folders.owner_id, folders.folder_type, folders.is_shared, folders.shared_type, folders.access_roles");
        $folderBuilder->like('folders.name', $query);

        // A. Folder yang pemiliknya adalah Staff
        $folderBuilder->groupStart();
        $folderBuilder->where('folders.owner_role', $staffRoleId); // Menggunakan owner_role

        // B. Folder yang dibuat oleh HRD (atau role lain) TAPI dibagikan ke Staff
        // Ini termasuk folder yang dibuat HRD di bawah struktur Staff dan shared ke Staff
        $folderBuilder->orGroupStart()
            ->where('folders.is_shared', 1)
            ->where('folders.access_roles IS NOT NULL')
            // Cek jika ID role Staff ada di access_roles
            ->where("JSON_CONTAINS(folders.access_roles, '\"{$staffRoleId}\"')")
            ->groupEnd();

        // C. Opsional: Jika ada folder tipe 'staff' yang dibuat oleh HRD tetapi tidak di-shared secara eksplisit
        // Jika Anda memiliki folder_type khusus 'staff' untuk folder yang dibuat oleh HRD di bagian Staff
        // dan ingin folder tersebut tetap muncul, Anda bisa tambahkan kondisi ini.
        // Misalnya: $folderBuilder->orWhere('folders.folder_type', 'staff'); 

        $folderBuilder->groupEnd(); // Menutup group A dan B (dan C jika ada)

        $folders = $folderBuilder->get()->getResultArray();

        // --- Perbaikan Query File ---
        $fileModel = new FileModel();
        $fileBuilder = $fileModel->builder();
        $fileBuilder->select("files.id, files.file_name as name, 'file' as type, files.folder_id");
        $fileBuilder->like('files.file_name', $query);

        // A. File yang diunggah oleh Staff
        $fileBuilder->groupStart();
        $fileBuilder->join('users', 'users.id = files.uploader_id'); // Join ke users untuk mendapatkan role_id uploader
        $fileBuilder->where('users.role_id', $staffRoleId);

        $fileBuilder->groupEnd(); // Menutup group A

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

        $hrdUserId = $this->session->get('user_id'); // User ID HRD yang sedang login
        $hrdRoleId = $this->session->get('role_id'); // Role ID HRD yang sedang login

        $spvRoleId = 5; // Ganti dengan ID role SPV yang sesuai di database Anda.

        // --- Perbaikan Query Folder ---
        $folderModel = new FolderModel();
        $folderBuilder = $folderModel->builder();
        $folderBuilder->select("folders.id, folders.name, 'folder' as type, folders.owner_id, folders.folder_type, folders.is_shared, folders.shared_type, folders.access_roles");
        $folderBuilder->like('folders.name', $query);

        $folderBuilder->groupStart();
        $folderBuilder->where('folders.owner_role', $spvRoleId); // Folder yang pemiliknya adalah SPV

        $folderBuilder->orGroupStart() // Folder yang dibagikan ke SPV
            ->where('folders.is_shared', 1)
            ->where('folders.access_roles IS NOT NULL')
            ->where("JSON_CONTAINS(folders.access_roles, '\"{$spvRoleId}\"')")
            ->groupEnd();
        $folderBuilder->groupEnd();

        $folders = $folderBuilder->get()->getResultArray();

        // --- Perbaikan Query File ---
        $fileModel = new FileModel();
        $fileBuilder = $fileModel->builder();
        $fileBuilder->select("files.id, files.file_name as name, 'file' as type, files.folder_id");
        $fileBuilder->like('files.file_name', $query);
        $fileBuilder->join('users', 'users.id = files.uploader_id');
        $fileBuilder->where('users.role_id', $spvRoleId); // Hanya file milik SPV

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

        $hrdUserId = $this->session->get('user_id');
        $hrdRoleId = $this->session->get('role_id');

        $managerRoleId = 4; // Ganti dengan ID role Manager yang sesuai di database Anda.

        // --- Perbaikan Query Folder ---
        $folderModel = new FolderModel();
        $folderBuilder = $folderModel->builder();
        $folderBuilder->select("folders.id, folders.name, 'folder' as type, folders.owner_id, folders.folder_type, folders.is_shared, folders.shared_type, folders.access_roles");
        $folderBuilder->like('folders.name', $query);

        $folderBuilder->groupStart();
        $folderBuilder->where('folders.owner_role', $managerRoleId); // Folder yang pemiliknya adalah Manager

        $folderBuilder->orGroupStart() // Folder yang dibagikan ke Manager
            ->where('folders.is_shared', 1)
            ->where('folders.access_roles IS NOT NULL')
            ->where("JSON_CONTAINS(folders.access_roles, '\"{$managerRoleId}\"')")
            ->groupEnd();
        $folderBuilder->groupEnd();

        $folders = $folderBuilder->get()->getResultArray();

        // --- Perbaikan Query File ---
        $fileModel = new FileModel();
        $fileBuilder = $fileModel->builder();
        $fileBuilder->select("files.id, files.file_name as name, 'file' as type, files.folder_id");
        $fileBuilder->like('files.file_name', $query);
        $fileBuilder->join('users', 'users.id = files.uploader_id');
        $fileBuilder->where('users.role_id', $managerRoleId); // Hanya file milik Manager

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

    public function searchDireksi()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request.']);
        }

        $query = $this->request->getVar('q');

        if (!$query) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Query pencarian tidak boleh kosong.']);
        }

        $hrdUserId = $this->session->get('user_id');
        $hrdRoleId = $this->session->get('role_id');

        $direksiRoleId = 3; // Ganti dengan ID role Direksi yang sesuai di database Anda.

        // --- Perbaikan Query Folder ---
        $folderModel = new FolderModel();
        $folderBuilder = $folderModel->builder();
        $folderBuilder->select("folders.id, folders.name, 'folder' as type, folders.owner_id, folders.folder_type, folders.is_shared, folders.shared_type, folders.access_roles");
        $folderBuilder->like('folders.name', $query);

        $folderBuilder->groupStart();
        $folderBuilder->where('folders.owner_role', $direksiRoleId); // Folder yang pemiliknya adalah Direksi

        $folderBuilder->orGroupStart() // Folder yang dibagikan ke Direksi
            ->where('folders.is_shared', 1)
            ->where('folders.access_roles IS NOT NULL')
            ->where("JSON_CONTAINS(folders.access_roles, '\"{$direksiRoleId}\"')")
            ->groupEnd();
        $folderBuilder->groupEnd();

        $folders = $folderBuilder->get()->getResultArray();

        // --- Perbaikan Query File ---
        $fileModel = new FileModel();
        $fileBuilder = $fileModel->builder();
        $fileBuilder->select("files.id, files.file_name as name, 'file' as type, files.folder_id");
        $fileBuilder->like('files.file_name', $query);
        $fileBuilder->join('users', 'users.id = files.uploader_id');
        $fileBuilder->where('users.role_id', $direksiRoleId); // Hanya file milik Direksi

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

    public function viewStaffFolder($folderId)
    {
        $folder = $this->folderModel->find($folderId);

        if (!$folder) {
            throw PageNotFoundException::forPageNotFound('Folder tidak ditemukan.');
        }

        $subFolders = $this->folderModel->where('parent_id', $folderId)->findAll();
        $filesInFolder = $this->fileModel->where('folder_id', $folderId)->findAll();

        $breadcrumbs = $this->getBreadcrumbs($folderId);

        $data = [
            'folder' => $folder,
            'subFolders' => $subFolders,
            'filesInFolder' => $filesInFolder,
            'breadcrumbs' => $breadcrumbs,
            'folderId' => $folderId,
            'folderType' => $folder['folder_type'] ?? 'personal',
            'isShared' => $folder['is_shared'] ?? 0,
            'isStaffFolder' => true,
            'isSupervisorFolder' => false,
            'isManagerFolder' => false,
            'isDireksiFolder' => false,
            
        ];

        return view('HRD/viewFolderContent', $data);
    }

    public function viewSupervisorFolder($folderId)
    {
        $folder = $this->folderModel->find($folderId);
        if (!$folder) {
            throw PageNotFoundException::forPageNotFound('Folder tidak ditemukan.');
        }
        $subFolders = $this->folderModel->where('parent_id', $folderId)->findAll();
        $filesInFolder = $this->fileModel->where('folder_id', $folderId)->findAll();
        $breadcrumbs = $this->getBreadcrumbs($folderId);
        $data = [
            'folder' => $folder,
            'subFolders' => $subFolders,
            'filesInFolder' => $filesInFolder,
            'breadcrumbs' => $breadcrumbs,
            'folderId' => $folderId,
            'folderType' => $folder['folder_type'] ?? 'personal',
            'isShared' => $folder['is_shared'] ?? 0,
            'isStaffFolder' => false,
            'isSupervisorFolder' => true,
            'isManagerFolder' => false,
            'isDireksiFolder' => false,
        ];
        return view('HRD/viewFolderContent', $data);
    }

    public function viewManagerFolder($folderId)
    {
        $folder = $this->folderModel->find($folderId);
        if (!$folder) {
            throw PageNotFoundException::forPageNotFound('Folder tidak ditemukan.');
        }
        $subFolders = $this->folderModel->where('parent_id', $folderId)->findAll();
        $filesInFolder = $this->fileModel->where('folder_id', $folderId)->findAll();
        $breadcrumbs = $this->getBreadcrumbs($folderId);
        $data = [
            'folder' => $folder,
            'subFolders' => $subFolders,
            'filesInFolder' => $filesInFolder,
            'breadcrumbs' => $breadcrumbs,
            'folderId' => $folderId,
            'folderType' => $folder['folder_type'] ?? 'personal',
            'isShared' => $folder['is_shared'] ?? 0,
            'isStaffFolder' => false,
            'isSupervisorFolder' => false,
            'isManagerFolder' => true,
            'isDireksiFolder' => false,
        ];
        return view('HRD/viewFolderContent', $data);
    }

    public function viewDireksiFolder($folderId)
    {
        $folder = $this->folderModel->find($folderId);
        if (!$folder) {
            throw PageNotFoundException::forPageNotFound('Folder tidak ditemukan.');
        }
        $subFolders = $this->folderModel->where('parent_id', $folderId)->findAll();
        $filesInFolder = $this->fileModel->where('folder_id', $folderId)->findAll();
        $breadcrumbs = $this->getBreadcrumbs($folderId);
        $data = [
            'folder' => $folder,
            'subFolders' => $subFolders,
            'filesInFolder' => $filesInFolder,
            'breadcrumbs' => $breadcrumbs,
            'folderId' => $folderId,
            'folderType' => $folder['folder_type'] ?? 'personal',
            'isShared' => $folder['is_shared'] ?? 0,
            'isStaffFolder' => false,
            'isSupervisorFolder' => false,
            'isManagerFolder' => false,
            'isDireksiFolder' => true,
        ];
        return view('HRD/viewFolderContent', $data);
    }

    public function createFolder()
    {
        // Pastikan request adalah AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
        }

        // Properti $this->session sekarang sudah terdefinisi dan bisa digunakan
        $userId = $this->session->get('user_id'); 
        $userRoleId = $this->session->get('role_id'); // ID role user yang sedang login

        // Periksa apakah user sudah login
        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized. User not logged in.']);
        }

        $input = $this->request->getJSON(true);
        $folderName = $input['name'] ?? null;
        $parentId = $input['parent_id'] ?? null;
        
        // Ambil folder_type dan access_roles dari input frontend.
        // Asumsi folder_type bisa 'personal', 'shared', atau 'public'.
        // access_roles diharapkan berupa array role ID (misal: [5, 2]).
        $initialFolderType = $input['folder_type'] ?? 'personal'; 
        $initialAccessRoles = $input['access_roles'] ?? []; 

        // Aturan validasi untuk nama folder
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            // Pastikan folder_type yang dikirim frontend sesuai dengan validasi model
            'folder_type' => 'permit_empty|in_list[personal,shared,public]' 
        ];
        
        // Jalankan validasi
        if (!$this->validate($rules, ['name' => ['required' => 'Nama folder tidak boleh kosong.']])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Validasi gagal.', 'errors' => $this->validator->getErrors()]);
        }

        // Variabel untuk menyimpan nilai akhir
        $newOwnerRole = $userRoleId;
        $isShared = 0;
        $sharedType = null;
        $finalAccessRoles = []; // Array untuk access_roles final (akan berisi string role IDs)
        $folderType = $initialFolderType; // Default, akan di-override jika ada parent

        // Logika penentuan properti folder berdasarkan apakah ini folder root atau subfolder
        if ($parentId === null) {
            // Ini adalah folder root
            if ($folderType === 'shared') {
                $isShared = 1;
                $sharedType = 'role_based';
                // Konversi role ID dari input ke string untuk disimpan
                $finalAccessRoles = array_values(array_unique(array_map('strval', $initialAccessRoles)));
            } else if ($folderType === 'personal') {
                $isShared = 0;
                $finalAccessRoles = [];
            } else if ($folderType === 'public') {
                $isShared = 1;
                $sharedType = 'public';
                $finalAccessRoles = []; // Folder public tidak butuh role spesifik di access_roles
            }
        } else {
            // Ini adalah subfolder, mewarisi properti dari folder induk
            $parentFolder = $this->folderModel->find($parentId);
            if (!$parentFolder) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Parent folder tidak ditemukan.']);
            }
            
            // Mewarisi semua properti penting dari folder induk
            $folderType = $parentFolder['folder_type']; 
            $isShared = (int) $parentFolder['is_shared'];
            $sharedType = $parentFolder['shared_type'];
            
            // Decode access_roles dari induk, lalu pastikan elemennya string
            $decodedParentAccessRoles = json_decode($parentFolder['access_roles'] ?? '[]', true);
            $finalAccessRoles = array_values(array_unique(array_map('strval', $decodedParentAccessRoles)));
            
            $newOwnerRole = (int) $parentFolder['owner_role'];
        }

        // Pengecekan nama folder duplikat di lokasi yang sama (parent_id dan owner_id)
        if ($this->folderModel->isNameExists($folderName, $parentId, $userId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Nama folder sudah ada di lokasi yang sama.']);
        }

        // Siapkan data untuk disimpan ke database
        $data = [
            'name' => $folderName,
            'parent_id' => $parentId,
            'owner_id' => $userId,
            'owner_role' => $newOwnerRole,
            'folder_type' => $folderType, 
            'is_shared' => $isShared,
            'shared_type' => ($isShared === 1) ? ($sharedType ?? 'role_based') : null,
            // access_roles sudah berupa array of string, langsung encode ke JSON
            'access_roles' => !empty($finalAccessRoles) ? json_encode($finalAccessRoles) : null,
        ];

        // Coba insert data folder ke database
        if ($this->folderModel->insert($data)) {
            $newFolderId = $this->folderModel->insertID();
            $relativePath = $this->folderModel->getFolderPath($newFolderId); 
            $folderPath = WRITEPATH . 'uploads/' . $relativePath;
            
            // Buat folder fisik di server jika belum ada
            if (!is_dir($folderPath)) {
                if (!mkdir($folderPath, 0777, true)) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membuat folder fisik di server.']);
                }
            }
            
            return $this->response->setJSON(['status' => 'success', 'message' => 'Folder berhasil dibuat!', 'new_folder_id' => $newFolderId, 'created_data' => $data]);
        } else {
            // Jika ada error saat insert ke database
            $errors = $this->folderModel->errors();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membuat folder.', 'errors' => $errors]);
        }
    }

    // FUNGSI KHUSUS UNTUK MEMBUAT FOLDER PERSONAL UNTUK JABATAN LAIN
    public function createPersonalFolderForStaff()
    {
        return $this->_createPersonalFolderForRole(6); // 6 adalah Role ID untuk Staff
    }

    public function createPersonalFolderForSPV()
    {
        return $this->_createPersonalFolderForRole(5); // 5 adalah Role ID untuk SPV
    }

    public function createPersonalFolderForManager()
    {
        return $this->_createPersonalFolderForRole(4); // 4 adalah Role ID untuk Manager
    }

    public function createPersonalFolderForDireksi()
    {
        return $this->_createPersonalFolderForRole(3); // 3 adalah Role ID untuk Direksi
    }

    private function _createPersonalFolderForRole(int $targetRoleId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
        }

        $session = session();
        $userId = $session->get('user_id'); // HRD yang sedang login
        $userRoleId = $session->get('role_id'); // ID Role HRD yang sedang login
        $userRoleData = $this->roleModel->find($userRoleId);
        $userRoleName = $userRoleData['name'] ?? null;

        if (!$userId || $userRoleName !== 'HRD') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak memiliki izin untuk membuat folder dengan peran ini.']);
        }

        $input = $this->request->getJSON(true);
        $folderName = $input['name'] ?? null;
        $parentId = $input['parent_id'] ?? null;
        $rules = ['name' => 'required|min_length[3]|max_length[255]'];

        if (!$this->validate($rules, ['name' => ['required' => 'Nama folder tidak boleh kosong.']])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Validasi gagal.', 'errors' => $this->validator->getErrors()]);
        }

        // 🔥 PERBAIKAN: Owner adalah HRD yang membuat. Folder ini dibagikan ke target role.
        $data = [
            'name' => $folderName,
            'parent_id' => $parentId,
            'owner_id' => $userId, // Owner ID adalah HRD
            'owner_role' => $userRoleId, // Owner Role adalah HRD
            'folder_type' => 'personal',
            'is_shared' => 1, // Harus di-share
            'shared_type' => 'role_based',
            'access_roles' => json_encode([(string) $targetRoleId, (string) $userRoleId]), // Dibagikan ke Staff dan HRD
        ];
        // -------------------------------

        if ($this->folderModel->insert($data)) {
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

    public function uploadFromFolder()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $file = $this->request->getFile('file');
        $relativePath = $this->request->getPost('relativePath');
        $parentIdPost = $this->request->getPost('parent_id');
        $rootParentId = ($parentIdPost === 'null' || $parentIdPost === null || $parentIdPost === '') ? null : (int) $parentIdPost;

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'File tidak valid.'], 400);
        }

        // Cek jika relativePath kosong
        if (empty($relativePath)) {
            // Jika relativePath kosong, artinya user mengunggah file langsung, bukan folder
            // Simpan file di root folder.
            $targetFolderId = $rootParentId;
            $fileName = $file->getName(); // Gunakan nama file asli
        } else {
            $pathParts = explode('/', $relativePath);
            $fileName = array_pop($pathParts);
            $folderPath = implode('/', $pathParts);

            // Tambahkan logging untuk memastikan folderPath tidak kosong
            log_message('info', 'Folder Path to create: ' . $folderPath);

            $targetFolderId = $this->folderModel->findOrCreateByPath($folderPath, $rootParentId, $userId);

            if ($targetFolderId === null) {
                log_message('error', 'Gagal membuat struktur folder untuk path: ' . $folderPath);
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membuat struktur folder di server.'], 500);
            }
        }

        // Pastikan folder untuk upload ada
        $uploadPath = WRITEPATH . 'uploads/';
        if (!is_dir($uploadPath)) {
            // Ini adalah penyebab error mkdir jika folder uploads tidak ada
            mkdir($uploadPath, 0775, true);
        }

        $fileMimeType = $file->getMimeType();
        $fileSize = $file->getSize();
        $newName = $file->getRandomName();

        if ($file->move($uploadPath, $newName)) {
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

    /**
     * Metode untuk mengunggah file.
     * Menerima file dan folder_id (opsional) melalui POST.
     */
    public function uploadFile()
    {
        $userId = $this->session->get('user_id');

        if (!$userId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized. User not logged in.'
            ]);
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

        // Cek validitas file
        if (!$uploadedFile->isValid()) {
            $errorString = $uploadedFile->getErrorString() . '(' . $uploadedFile->getError() . ')';
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'File tidak valid.',
                'errors' => $errorString
            ]);
        }

        // **PERBAIKAN: Ambil informasi file SEBELUM dipindahkan**
        $originalName = $uploadedFile->getName();
        $fileSize = $uploadedFile->getSize();
        $mimeType = $uploadedFile->getMimeType(); // Ambil sebelum move()
        $newName = $uploadedFile->getRandomName();

        // Tentukan direktori tujuan
        $targetDirectory = WRITEPATH . 'uploads';

        // Gunakan try-catch untuk penanganan error yang lebih baik
        try {
            if ($uploadedFile->move($targetDirectory, $newName)) {
                $data = [
                    'folder_id' => empty($folderId) ? null : $folderId,
                    'uploader_id' => $userId,
                    'file_name' => $originalName, // Gunakan variabel yang sudah disimpan
                    'file_path' => $newName,
                    'file_size' => $fileSize, // Gunakan variabel yang sudah disimpan
                    'file_type' => $mimeType, // Gunakan variabel yang sudah disimpan
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                if ($this->fileModel->insert($data)) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'File berhasil diunggah.'
                    ]);
                } else {
                    // Hapus file yang sudah diunggah jika gagal menyimpan ke DB
                    $filePath = $targetDirectory . '/' . $newName;
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }

                    log_message('error', 'Gagal insert ke DB. File: ' . $newName . ', Errors: ' . json_encode($this->fileModel->errors()));

                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Gagal menyimpan data file ke database.',
                        'errors' => $this->fileModel->errors()
                    ]);
                }
            } else {
                log_message('error', 'Gagal memindahkan file. Error: ' . $uploadedFile->getErrorString());
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal memindahkan file yang diunggah. Mohon periksa izin direktori.'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Exception saat mengunggah file: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem saat mengunggah file.',
                'errors' => $e->getMessage()
            ]);
        }
    }

    public function uploadDokumenUmum()
    {
        // 1. Ambil session user
        $session = session();
        $currentUserId = $session->get('user_id');

        if (!$currentUserId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User not logged in.'])->setStatusCode(401);
        }

        // 2. Validasi file dan input POST
        $validationRule = [
            'file' => [
                'label' => 'Dokumen',
                'rules' => 'uploaded[file]|max_size[file,5120]|ext_in[file,pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif]',
                'errors' => [
                    'uploaded' => 'Anda harus memilih file untuk diunggah.',
                    'max_size' => 'Ukuran file terlalu besar (maks 5MB).',
                    'ext_in' => 'Format file tidak diizinkan. Hanya PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG, GIF.',
                ],
            ],
            'category' => 'required',
            'description' => 'required',
        ];

        if (!$this->validate($validationRule)) {
            $errors = $this->validator->getErrors();
            // Redirect kembali dengan pesan error
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $file = $this->request->getFile('file');

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return redirect()->back()->with('error', 'File tidak valid atau gagal diunggah.');
        }

        // 3. Pindahkan file dan simpan ke database
        $newName = $file->getRandomName();
        $uploadPath = WRITEPATH . 'uploads/dokumen-umum/';

        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0777, true)) {
                return redirect()->back()->with('error', 'Gagal membuat direktori upload. Periksa izin folder.');
            }
        }

        if (!$file->move($uploadPath, $newName)) {
            return redirect()->back()->with('error', 'Gagal memindahkan file yang diunggah.');
        }

        $hrdDocumentModel = new HrdDocumentModel();
        $dataDokumen = [
            'file_id' => $newName,
            'category' => $this->request->getPost('category'),
            'description' => $this->request->getPost('description'),
            'file_name' => $file->getName(),
        ];
        $hrdDocumentModel->save($dataDokumen);
        $documentId = $hrdDocumentModel->insertID();

        // 4. Kirim permintaan ke server Node.js untuk broadcast notifikasi
        $dataNotif = [
            'title' => 'Dokumen Umum Baru',
            'message' => 'Dokumen umum baru telah diunggah: ' . $this->request->getPost('description'),
            'url' => base_url('dokumen/view/' . $documentId) // Sesuaikan URL
        ];

        $ch = curl_init('http://localhost:3000/api/broadcast-notification');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dataNotif));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            log_message('error', 'Failed to send notification to Node.js server. HTTP Code: ' . $httpCode . ', Response: ' . $response);
        }

        return redirect()->to(base_url('hrd/dokumen-umum'))->with('success', 'Dokumen berhasil diunggah.');
    }

    public function listDocuments($parentId = null)
    {
        $documents = $this->hrdDocumentModel->getByParent($parentId);
        return view('hrd/documents_list', [
            'documents' => $documents,
            'parent_id' => $parentId
        ]);
    }



    public function renameFolder()
    {
        if ($this->request->isAJAX()) {
            $rules = [
                'id' => 'required|integer',
                'newName' => 'required|min_length[3]|max_length[255]',
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON(['status' => 'error', 'message' => $this->validator->getErrors()]);
            }

            $folderId = $this->request->getPost('id');
            $newName = $this->request->getPost('newName');

            $folder = $this->folderModel->find($folderId);
            if (!$folder) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Folder tidak ditemukan.']);
            }

            if (!isset($folder['full_path_physical'])) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Jalur fisik folder tidak ditemukan.']);
            }

            $oldPhysicalPath = $folder['full_path_physical'];
            $newPhysicalPath = dirname($oldPhysicalPath) . DIRECTORY_SEPARATOR . $newName;

            if (rename($oldPhysicalPath, $newPhysicalPath)) {
                $data = [
                    'name' => $newName,
                    'full_path_physical' => $newPhysicalPath,
                    'path' => str_replace(WRITABLE_PATH . 'uploads' . DIRECTORY_SEPARATOR, '', $newPhysicalPath), // Perbarui jalur relatif
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if ($this->folderModel->update($folderId, $data)) {
                    return $this->response->setJSON(['status' => 'success', 'message' => 'Folder berhasil diganti nama.']);
                } else {
                    rename($newPhysicalPath, $oldPhysicalPath); // Rollback
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui nama folder di database.']);
                }
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal mengganti nama folder fisik. Pastikan folder tidak sedang digunakan.']);
            }
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request.']);
    }

    public function deleteFolder()
    {
        if ($this->request->isAJAX()) {
            $rules = [
                'id' => 'required|integer',
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON(['status' => 'error', 'message' => $this->validator->getErrors()]);
            }

            $folderId = $this->request->getPost('id');
            $folder = $this->folderModel->find($folderId);

            if (!$folder) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Folder tidak ditemukan.']);
            }

            if (!isset($folder['full_path_physical'])) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Jalur fisik folder tidak ditemukan.']);
            }

            $physicalPath = $folder['full_path_physical'];

            if (is_dir($physicalPath)) {
                // Gunakan helper CodeIgniter delete_files untuk menghapus isi folder
                // Pastikan helper 'filesystem' sudah dimuat (sudah ada di __construct)
                if (!delete_files($physicalPath, TRUE) || !rmdir($physicalPath)) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus folder fisik atau isinya. Pastikan folder tidak terkunci.']);
                }
            }

            // Hapus file-file yang terkait dengan folder ini dari database
            $this->fileModel->where('folder_id', $folderId)->delete();
            // Hapus sub-folder yang terkait dengan folder ini dari database (rekursif jika perlu, tapi ini hanya menghapus langsung)
            $this->folderModel->where('parent_id', $folderId)->delete();

            if ($this->folderModel->delete($folderId)) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Folder berhasil dihapus.']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus folder dari database.']);
            }
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request.']);
    }

    public function renameFile()
    {
        if ($this->request->isAJAX()) {
            $rules = [
                'id' => 'required|integer',
                'newName' => 'required|min_length[1]|max_length[255]',
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON(['status' => 'error', 'message' => $this->validator->getErrors()]);
            }

            $fileId = $this->request->getPost('id');
            $newName = $this->request->getPost('newName');

            $file = $this->fileModel->find($fileId);
            if (!$file) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'File tidak ditemukan.']);
            }

            $oldFileName = $file['file_name'];
            $fileExtension = pathinfo($oldFileName, PATHINFO_EXTENSION);
            // Pastikan nama file baru memiliki ekstensi yang sama
            $newFullFileName = $newName . (empty($fileExtension) ? '' : '.' . $fileExtension);


            $folder = null;
            // Jalur dasar untuk file yang tidak di folder adalah WRITABLE_PATH . 'uploads/'
            $physicalFilePathBase = WRITABLE_PATH . 'uploads/';

            if (isset($file['folder_id']) && $file['folder_id']) {
                $folder = $this->folderModel->find($file['folder_id']);
                if ($folder && isset($folder['full_path_physical'])) {
                    $physicalFilePathBase = $folder['full_path_physical'] . DIRECTORY_SEPARATOR;
                } else {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Folder terkait file tidak ditemukan atau jalur fisiknya tidak valid.']);
                }
            }
            $oldPhysicalFilePath = $physicalFilePathBase . $file['server_file_name'];
            $newPhysicalFilePath = $physicalFilePathBase . $newFullFileName;


            if (rename($oldPhysicalFilePath, $newPhysicalFilePath)) {
                $data = [
                    'file_name' => $newFullFileName,
                    'server_file_name' => str_replace(WRITABLE_PATH . 'uploads' . DIRECTORY_SEPARATOR, '', $newPhysicalFilePath), // Perbarui jalur relatif
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if ($this->fileModel->update($fileId, $data)) {
                    return $this->response->setJSON(['status' => 'success', 'message' => 'File berhasil diganti nama.']);
                } else {
                    // Rollback rename if DB update fails
                    rename($newPhysicalFilePath, $oldPhysicalFilePath);
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui nama file di database.']);
                }
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal mengganti nama file fisik.']);
            }
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request.']);
    }

    public function deleteFile($fileId)
    {
        if ($this->request->isAJAX()) {
            $file = $this->fileModel->find($fileId);

            if (!$file) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'File tidak ditemukan.']);
            }

            $folder = null;
            // Jalur dasar untuk file yang tidak di folder adalah WRITABLE_PATH . 'uploads/'
            $physicalFilePathBase = WRITABLE_PATH . 'uploads/';

            if (isset($file['folder_id']) && $file['folder_id']) {
                $folder = $this->folderModel->find($file['folder_id']);
                if ($folder && isset($folder['full_path_physical'])) {
                    $physicalFilePathBase = $folder['full_path_physical'] . DIRECTORY_SEPARATOR;
                } else {
                    log_message('warning', 'File ' . $fileId . ' has invalid folder_id or missing full_path_physical for folder ' . $file['folder_id']);
                }
            }
            $physicalFilePath = $physicalFilePathBase . $file['server_file_name'];

            if (file_exists($physicalFilePath)) {
                if (!unlink($physicalFilePath)) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus file fisik.']);
                }
            } else {
                log_message('warning', 'Fisik file tidak ditemukan: ' . $physicalFilePath . ' (ID: ' . $fileId . ')');
            }

            if ($this->fileModel->delete($fileId)) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'File berhasil dihapus.']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus file dari database.']);
            }
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request.']);
    }

    public function downloadFile($fileId)
    {
        $file = $this->fileModel->find($fileId);

        if (!$file) {
            throw PageNotFoundException::forPageNotFound('File tidak ditemukan.');
        }

        if (!isset($file['server_file_name'])) {
            log_message('error', 'File ID ' . $fileId . ' ditemukan, tetapi kunci "server_file_name" tidak ada.');
            throw PageNotFoundException::forPageNotFound('Informasi file tidak lengkap.');
        }

        $folder = null;
        // Jalur dasar untuk mencari file adalah WRITABLE_PATH . 'uploads/'
        $filePathBase = WRITABLE_PATH . 'uploads/';

        if (isset($file['folder_id']) && $file['folder_id']) {
            $folder = $this->folderModel->find($file['folder_id']);
            if ($folder && isset($folder['full_path_physical'])) {
                $filePathBase = $folder['full_path_physical'] . DIRECTORY_SEPARATOR;
            } else {
                log_message('warning', 'File ' . $fileId . ' memiliki folder_id tidak valid atau full_path_physical hilang untuk folder ' . $file['folder_id']);
            }
        }
        $filePath = $filePathBase . $file['server_file_name'];

        if (!file_exists($filePath)) {
            log_message('error', 'File fisik tidak ditemukan di: ' . $filePath . ' untuk File ID: ' . $fileId);
            throw PageNotFoundException::forPageNotFound('File fisik tidak ditemukan.');
        }

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
            $data['previewUrl'] = site_url('staff/serve-file/' . $fileId); // Pastikan serve-file punya otorisasi
            return view('Umum/view_file_wrapper', $data);
        } else {
            // Untuk DOCX, PPTX, XLSX, dll.: tampilkan halaman info dan tombol unduh
            return view('Umum/view_file_khusus', $data);
        }
    }

    private function getBreadcrumbs($folderId)
    {
        $breadcrumbs = [];
        $currentFolderId = $folderId;

        while ($currentFolderId) {
            $folder = $this->folderModel->find($currentFolderId);
            if ($folder) {
                array_unshift($breadcrumbs, ['id' => $folder['id'], 'name' => $folder['name']]);
                $currentFolderId = $folder['parent_id'];
            } else {
                break;
            }
        }
        return $breadcrumbs;
    }

    public function dokumenSPV()
    {
        $session = session();
        $hrdUserId = $session->get('user_id');
        $hrdRoleId = $session->get('role_id');
        $staffRoleId = 5;
        $folders = $this->folderModel->getHRDViewForRole($hrdUserId, $hrdRoleId, $staffRoleId);
        $data['personalFolders'] = $folders;

        return view('HRD/dokumenSPV', $data);
    }
    public function dokumenManager()
    {
        $session = session();
        $hrdUserId = $session->get('user_id');
        $hrdRoleId = $session->get('role_id');
        $managerRoleId = 4;
        $folders = $this->folderModel->getHRDViewForRole($hrdUserId, $hrdRoleId, $managerRoleId);
        $data['personalFolders'] = $folders;

        return view('HRD/dokumenManager', $data);
    }
    public function dokumenDireksi()
    {
        $session = session();
        $hrdUserId = $session->get('user_id');
        $hrdRoleId = $session->get('role_id');
        $staffRoleId = 3;
        $folders = $this->folderModel->getHRDViewForRole($hrdUserId, $hrdRoleId, $staffRoleId);
        $data['personalFolders'] = $folders;

        return view('HRD/dokumenDireksi', $data);
    }

    public function dokumenBersama()
    {

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

    // app/Controllers/DokumenControllerHRD.php
    public function dokumenUmum()
    {
        $hrdDocumentModel = new \App\Models\HrdDocumentModel();
        $data['documents'] = $hrdDocumentModel->getByParent(null); // Mengambil dokumen root level
        $data['parent_id'] = null;
        return view('HRD/dokumenUmum', $data);
    }

    /**
     * Method khusus untuk membuat folder di halaman dokumen umum
     */
    public function createFolderUmum()
    {
        // Clear any previous output
        if (ob_get_level()) {
            ob_clean();
        }

        // Set proper headers
        $this->response->setHeader('Content-Type', 'application/json');
        $this->response->setHeader('Cache-Control', 'no-cache, must-revalidate');

        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
        }

        $input = $this->request->getJSON(true);

        $folderName = $input['name'] ?? null;
        $parentId = $input['parent_id'] ?? null;
        $folderType = $input['folder_type'] ?? 'personal';

        // Validasi input
        if (!$folderName || trim($folderName) === '') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Nama folder tidak boleh kosong.']);
        }

        // Inisialisasi HrdDocumentModel
        $hrdDocumentModel = new \App\Models\HrdDocumentModel();

        // Siapkan data untuk disimpan
        $data = [
            'parent_id' => $parentId ?: null,
            'name' => trim($folderName),
            'type' => 'folder',
            'mime_type' => null,
            'size' => null,
            'file_path' => null,
            'file_id' => null,
            'category' => ($folderType !== 'personal') ? $folderType : null,
            'description' => null
        ];

        try {
            // Simpan ke database
            if ($hrdDocumentModel->insert($data)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Folder berhasil dibuat!'
                ]);
            } else {
                $errors = $hrdDocumentModel->errors();
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal membuat folder.',
                    'errors' => $errors
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function ActivityLogs()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $searchQuery = $this->request->getGet('search');

        $query = $this->activityLogsModel
            ->select('activity_logs.*, users.name as user_name, roles.name as role_name');

        // Lakukan JOIN hanya untuk users dan roles
        $query->join('users', 'users.id = activity_logs.user_id', 'left')
            ->join('roles', 'roles.id = users.role_id', 'left');

        // 🔥 PENTING: HAPUS SEMUA JOIN KE TABEL 'files' dan 'folders' di sini.
        // Sekarang, nama file/folder diambil langsung dari 'activity_logs.target_name'.

        // Terapkan filter tanggal
        if (!empty($startDate) && !empty($endDate)) {
            try {
                $startDateTime = Time::parse($startDate . ' 00:00:00')->toDateTimeString();
                $endDateTime = Time::parse($endDate . ' 23:59:59')->toDateTimeString();

                $query->where('activity_logs.timestamp >=', $startDateTime)
                    ->where('activity_logs.timestamp <=', $endDateTime);
            } catch (\Exception $e) {
                log_message('error', 'Gagal parsing tanggal filter untuk log aktivitas: ' . $e->getMessage());
                session()->setFlashdata('error', 'Format tanggal tidak valid.');
                return redirect()->back(); // Gunakan redirect()->back() untuk lebih fleksibel
            }
        }

        // Terapkan filter pencarian
        if (!empty($searchQuery)) {
            $query->groupStart()
                ->orLike('users.name', $searchQuery)
                ->orLike('roles.name', $searchQuery)
                ->orLike('activity_logs.action', $searchQuery)
                ->orLike('activity_logs.target_name', $searchQuery) // 🔥 Gunakan kolom baru ini
                ->groupEnd();
        }

        $activityLogs = $query->orderBy('activity_logs.timestamp', 'DESC')->findAll();

        $data = [
            'title' => 'Log Aktivitas File HRD',
            'logs' => $activityLogs,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'searchQuery' => $searchQuery,
        ];

        return view('HRD/aktivitas', $data); // Sesuaikan nama view jika perlu
    }

    /**
     * Method untuk navigasi ke dalam folder dokumen umum
     */
    public function dokumenUmumFolder($folderId)
    {
        $hrdDocumentModel = new \App\Models\HrdDocumentModel();

        // Validasi folder exists
        $folder = $hrdDocumentModel->find($folderId);
        if (!$folder || $folder['type'] !== 'folder') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Folder tidak ditemukan.');
        }

        // Ambil semua dokumen/folder di dalam folder ini
        $documents = $hrdDocumentModel->getByParent($folderId);

        $data = [
            'documents' => $documents,
            'current_folder' => $folder,
            'parent_id' => $folderId,
            'breadcrumb' => $this->getBreadcrumb($folderId, $hrdDocumentModel)
        ];

        return view('HRD/dokumenUmumFolder', $data);
    }

    /**
     * Method untuk upload file ke dalam folder dokumen umum
     */
    public function uploadFileUmum()
    {
        // Clear any previous output
        if (ob_get_level()) {
            ob_clean();
        }

        // Set proper headers
        $this->response->setHeader('Content-Type', 'application/json');
        $this->response->setHeader('Cache-Control', 'no-cache, must-revalidate');

        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
        }

        $file = $this->request->getFile('file');
        $parentId = $this->request->getPost('parent_id');
        $description = $this->request->getPost('description');

        // Debug: Log received data
        log_message('info', 'Upload file request - Parent ID: ' . ($parentId ?? 'null'));
        log_message('info', 'Upload file request - File: ' . ($file ? $file->getClientName() : 'no file'));

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'File tidak valid.']);
        }

        // Validasi parent folder
        $hrdDocumentModel = new \App\Models\HrdDocumentModel();
        if ($parentId) {
            $parentFolder = $hrdDocumentModel->find($parentId);
            if (!$parentFolder || $parentFolder['type'] !== 'folder') {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Folder tujuan tidak valid.']);
            }
        }

        try {
            // Generate unique filename
            $fileName = $file->getRandomName();
            $uploadPath = WRITEPATH . 'uploads/dokumen-umum/';

            // Create directory if not exists
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            // Move file
            if ($file->move($uploadPath, $fileName)) {
                // Simpan data ke database dengan nama file asli
                $data = [
                    'parent_id' => $parentId,
                    'name' => $file->getClientName(), // Gunakan nama file asli
                    'type' => 'file',
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'file_path' => 'uploads/dokumen-umum/' . $fileName, // Gunakan nama random untuk file fisik
                    'category' => null,
                    'description' => $description,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $documentId = $hrdDocumentModel->insert($data);

                if ($documentId) {
                    // Trigger notifikasi realtime dan email
                    $this->triggerDocumentNotification($documentId, $fileName, null);

                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'File berhasil diupload!',
                        'document_id' => $documentId
                    ]);
                } else {
                    // Hapus file jika gagal insert ke database
                    if (file_exists($uploadPath . $fileName)) {
                        unlink($uploadPath . $fileName);
                    }
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Gagal menyimpan data file ke database.'
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal mengupload file.'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Helper method untuk membuat breadcrumb navigasi
     */
    private function getBreadcrumb($folderId, $model)
    {
        $breadcrumb = [];
        $currentId = $folderId;

        while ($currentId) {
            $folder = $model->find($currentId);
            if ($folder) {
                array_unshift($breadcrumb, $folder);
                $currentId = $folder['parent_id'];
            } else {
                break;
            }
        }

        return $breadcrumb;
    }

    /**
     * Helper method untuk generate breadcrumb
     */
    private function generateBreadcrumb($parentId)
    {
        $breadcrumb = [];
        $hrdDocumentModel = new \App\Models\HrdDocumentModel();

        while ($parentId) {
            $parent = $hrdDocumentModel->find($parentId);
            if ($parent) {
                array_unshift($breadcrumb, [
                    'id' => $parent['id'],
                    'name' => $parent['name']
                ]);
                $parentId = $parent['parent_id'];
            } else {
                break;
            }
        }

        return $breadcrumb;
    }

    /**
     * Trigger notifikasi realtime dan email saat upload dokumen
     */
    private function triggerDocumentNotification($documentId, $documentName, $category = null)
    {
        try {
            // Get current user info
            $session = session();
            $uploaderName = $session->get('name') ?? 'System';

            // Load notification service
            $notificationService = new \App\Services\NotificationService();

            // Process notification (database insert, WebSocket push, email send)
            $result = $notificationService->processDocumentUploadNotification(
                $documentId,
                $documentName,
                $uploaderName,
                $category
            );

            if ($result) {
                log_message('info', "Document notification triggered successfully for: {$documentName}");
            } else {
                log_message('warning', "Failed to trigger document notification for: {$documentName}");
            }

        } catch (\Exception $e) {
            log_message('error', 'Error triggering document notification: ' . $e->getMessage());
        }
    }
}