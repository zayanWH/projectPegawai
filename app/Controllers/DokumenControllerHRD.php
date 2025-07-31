<?php

namespace App\Controllers;

use App\Models\FolderModel;
use App\Models\FileModel;
use App\Models\HrdDocumentModel;
use App\Models\UserModel;
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
    protected $roleModel; // Deklarasi properti
    protected $helpers = ['form', 'url', 'filesystem'];

    public function __construct()
    {
        $this->folderModel = new FolderModel();
        $this->fileModel = new FileModel();
        $this->hrdDocumentModel = new HrdDocumentModel();
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel(); // Inisialisasi properti
        helper('session');
    }



    public function serveFile($fileId)
    {
        $session = session();
        $userRole = $session->get('role'); // Contoh: 'hrd', 'staff', dll.
        $userId = $session->get('user_id');

        $file = $this->fileModel->find($fileId);

        if (!$file) {
            throw PageNotFoundException::forPageNotFound('File tidak ditemukan.');
        }

        // Logika Otorisasi:
        if ($userRole === 'hrd') {
            // HRD memiliki akses penuh, lanjutkan
        } elseif ($file['uploader_id'] == $userId) {
            // Pengguna adalah pemilik file, lanjutkan
        } else {
            throw new AccessDeniedException('Anda tidak memiliki izin untuk melihat file ini.');
        }

        // --- Pastikan konstanta WRITABLE_PATH didefinisikan di app/Config/Constants.php ---
        $filePath = WRITABLE_PATH . 'uploads' . DIRECTORY_SEPARATOR . $file['server_file_name'];
        // ---------------------------------------------------------------------------------

        // Verifikasi apakah file fisik benar-benar ada
        if (!file_exists($filePath)) {
            throw PageNotFoundException::forPageNotFound('File fisik tidak ditemukan di server.');
        }

        $mimeType = $file['file_type'];

        if (strpos($mimeType, 'image/') === 0 || $mimeType === 'application/pdf') {
            header('Content-Type: ' . $mimeType);
            readfile($filePath);
            exit;
        } else {
            return $this->response->download($filePath, null)->setFileName($file['file_name']);
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
        $currentUserId = $session->get('user_id'); // ID pengguna HRD yang sedang login
        $currentUserRole = $session->get('role_name'); // Nama peran HRD yang sedang login (misal: 'HRD')

        // Ambil semua folder dari database, beserta informasi pemilik (nama pengguna dan nama peran)
        $data['personalFolders'] = $this->folderModel
            ->select('folders.*, users.name as owner_display, roles.name as owner_role_name') // Menggunakan 'users.name' jika itu kolom nama pengguna
            ->join('users', 'users.id = folders.owner_id', 'left') // Join dengan tabel users
            ->join('roles', 'roles.id = users.role_id', 'left')   // Join dengan tabel roles melalui users
            ->where('folders.parent_id IS NULL')
            ->findAll(); // Mengambil semua folder

        // Untuk saat ini, kita asumsikan tidak ada file yang ditampilkan bersama folder di halaman ini.
        // Jika ada, Anda perlu query terpisah untuk file.
        $data['files'] = []; // Inisialisasi kosong jika tidak ada file yang ditampilkan

        // Data untuk JavaScript frontend (penting!)
        // currentFolderId di set null karena ini adalah halaman root dokumen staff
        $data['currentFolderId'] = null;
        $data['currentUserId'] = $currentUserId;
        $data['userRoleName'] = $currentUserRole;

        return view('HRD/dokumenStaff', $data); // Pastikan path view sudah benar (HRD/dokumenStaff)
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
        ];

        return view('HRD/viewFolderContent', $data);
    }

    public function createFolder()
    {
        // Memastikan request adalah AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
        }

        // Mengambil ID pengguna dari sesi
        $userId = session()->get('user_id');
        // MENGAMBIL ROLE ID PENGGUNA DARI SESI SAAT FOLDER DIBUAT
        $userRole = session()->get('user_role'); // Ini penting ditambahkan!

        // Memastikan pengguna sudah login
        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized. User not logged in.']);
        }

        // Mengambil input JSON dari request
        $input = $this->request->getJSON(true);

        $folderName = $input['name'] ?? null;
        $parentId = $input['parent_id'] ?? null;
        $folderType = $input['folder_type'] ?? 'personal';
        $isShared = $input['is_shared'] ?? 0;
        $sharedType = $input['shared_type'] ?? null;
        $accessRoles = $input['access_roles'] ?? []; // Pastikan ini array kosong sebagai default

        // Aturan validasi untuk nama folder
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
        ];
        if (!$this->validate($rules, ['name' => ['required' => 'Nama folder tidak boleh kosong.']])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Validasi gagal.', 'errors' => $this->validator->getErrors()]);
        }

        // Jika ada parent_id, ambil informasi folder induk
        if ($parentId) {
            $parentFolder = $this->folderModel->find($parentId);
            if ($parentFolder) {
                // Warisi properti dari folder induk
                $folderType = $parentFolder['folder_type'];
                $isShared = $parentFolder['is_shared'];
                $sharedType = $parentFolder['shared_type'];
                // Decode access_roles dari induk jika ada
                $accessRoles = json_decode($parentFolder['access_roles'] ?? '[]', true);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Parent folder tidak ditemukan.']);
            }
        }

        // --- LOGIKA PENTING BARU UNTUK owner_role dan access_roles ---

        // Set owner_role ke role dari pengguna yang membuat folder
        $ownerRoleToSave = $userRole;

        // Jika folder bertipe 'personal' dan dibuat oleh Admin (role 1) atau Staff (role 6)
        if ($folderType === 'personal') {
            if ($userRole == 1) { // Jika Admin (role ID 1) yang membuat folder personal
                $accessRoles = [6]; // Otomatis berikan akses ke Staff (role ID 6)
                $isShared = 1; // Jadikan folder ini sebagai shared (meskipun personal, untuk menerapkan access_roles)
                $sharedType = 'role_based'; // Tipe shared berdasarkan peran
            } else if ($userRole == 6) { // Jika Staff (role ID 6) yang membuat folder personal
                $accessRoles = [1]; // Otomatis berikan akses ke Admin (role ID 1)
                $isShared = 1; // Jadikan shared
                $sharedType = 'role_based'; // Tipe shared berdasarkan peran
            } else {
                // Untuk peran (role) lain, folder personal tetap privat
                $accessRoles = [];
                $isShared = 0;
                $sharedType = null;
            }
        }
        // Jika folder bukan personal, ikuti input yang ada, tapi pastikan accessRoles adalah array
        else {
            // Jika folder diset sebagai shared dan accessRoles masih kosong,
            // secara default tambahkan role pembuat folder ke access_roles
            if ((int) $isShared === 1 && empty($accessRoles)) {
                $accessRoles = [$userRole];
            }
        }

        // --- AKHIR LOGIKA PENTING BARU ---

        // Data yang akan disimpan ke database
        $data = [
            'name' => $folderName,
            'parent_id' => $parentId,
            'owner_id' => $userId,
            'owner_role' => $ownerRoleToSave, // Ini akan mengisi kolom owner_role
            'folder_type' => $folderType,
            'is_shared' => (int) $isShared,
            'shared_type' => ((int) $isShared === 1) ? $sharedType : null,
            // Encode array accessRoles ke format JSON string, pastikan unik dan tidak kosong
            'access_roles' => ((int) $isShared === 1 && !empty($accessRoles)) ? json_encode(array_values(array_unique($accessRoles))) : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Menyimpan data folder ke database
        if ($this->folderModel->insert($data)) {
            $newFolderId = $this->folderModel->insertID();
            $relativePath = $this->folderModel->getFolderPath($newFolderId);
            $folderPath = WRITEPATH . 'uploads/' . $relativePath;
            // Membuat folder fisik di server
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

    public function uploadFile() // Tidak perlu lagi parameter $folderId di sini
    {
        $session = session();
        $currentUserId = $session->get('user_id');

        if (!$currentUserId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User not logged in.'])->setStatusCode(401);
        }

        // Ambil file yang diupload
        $file = $this->request->getFile('file'); // 'file' adalah nama input field di form/payload

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return $this->response->setJSON(['status' => 'error', 'message' => $file->getErrorString() ?? 'File upload failed.'])->setStatusCode(400);
        }

        // Ambil folder_id dari FormData (bukan dari URL)
        $folderId = $this->request->getPost('folder_id'); // Ambil dari POST data FormData

        $folder = null;
        if ($folderId) {
            $folder = $this->folderModel->find($folderId);
            if (!$folder) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Folder not found.'])->setStatusCode(404);
            }
            // Tambahkan logika otorisasi di sini jika diperlukan:
            // Apakah HRD yang login memiliki izin untuk upload ke folder ini?
            // (misalnya, hanya ke folder mereka sendiri, atau shared folder dengan izin edit)
        }

        // Tentukan direktori tujuan penyimpanan fisik
        $targetDirectory = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'files';
        if ($folder) {
            $targetDirectory = $folder['full_path_physical']; // Gunakan path fisik folder
        } else {
            // Jika tidak ada folderId (upload ke root/orphan), simpan di folder user
            $targetDirectory .= DIRECTORY_SEPARATOR . $currentUserId;
        }

        // Pastikan direktori tujuan ada
        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0777, true);
        }

        // Generate nama file unik untuk menghindari tabrakan
        $newName = $file->getRandomName();

        // Pindahkan file ke direktori tujuan
        if ($file->move($targetDirectory, $newName)) {
            // Simpan metadata file ke database (tabel 'files')
            $dataToSave = [
                'name' => $file->getName(), // Nama asli file
                'new_name' => $newName,         // Nama unik di server
                'type' => $file->getClientMimeType(),
                'size' => $file->getSizeByUnit('kb'),
                'path' => str_replace(WRITEPATH, '', $targetDirectory), // Path relatif dari WRITEPATH
                'full_path_physical' => $targetDirectory . DIRECTORY_SEPARATOR . $newName,
                'folder_id' => $folderId,      // ID folder tempat file diupload (bisa NULL)
                'uploader_id' => $currentUserId, // ID pengguna yang mengunggah
                'owner_role' => $session->get('role_name'), // Peran user yang mengunggah
            ];

            if ($this->fileModel->insert($dataToSave)) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'File uploaded successfully!', 'file' => $dataToSave]);
            } else {
                // Hapus file fisik jika penyimpanan DB gagal
                if (file_exists($targetDirectory . DIRECTORY_SEPARATOR . $newName)) {
                    unlink($targetDirectory . DIRECTORY_SEPARATOR . $newName);
                }
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to save file metadata to database.']);
            }
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to move uploaded file.']);
        }
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

        // Hapus baris dd($file); yang sebelumnya Anda tambahkan

        // Perbarui pemeriksaan ini untuk memeriksa 'file_path'
        if (!isset($file['file_path']) || empty($file['file_path'])) {
            log_message('error', 'File ID ' . $fileId . ' ditemukan, tetapi kunci "file_path" tidak ada atau kosong.');
            throw PageNotFoundException::forPageNotFound('Informasi file tidak lengkap atau nama file server kosong.');
        }

        $folder = null;
        $filePathBase = WRITABLE_PATH . 'uploads/';

        if (isset($file['folder_id']) && $file['folder_id']) {
            $folder = $this->folderModel->find($file['folder_id']);
            if ($folder && isset($folder['full_path_physical'])) {
                $filePathBase = $folder['full_path_physical'] . DIRECTORY_SEPARATOR;
            } else {
                log_message('warning', 'File ' . $fileId . ' memiliki folder_id tidak valid atau full_path_physical hilang untuk folder ' . $file['folder_id']);
            }
        }

        // --- GANTI BARIS INI ---
        // Dari: $filePath = $filePathBase . $file['server_file_name'];
        // Menjadi:
        $filePath = $filePathBase . $file['file_path'];

        // Hapus baris dd($filePath); yang sebelumnya Anda tambahkan

        if (!file_exists($filePath)) {
            log_message('error', 'File fisik tidak ditemukan di: ' . $filePath . ' untuk File ID: ' . $fileId);
            throw PageNotFoundException::forPageNotFound('File fisik tidak ditemukan.');
        }

        $mimeType = $file['file_type'];

        if (strpos($mimeType, 'image/') === 0 || $mimeType === 'application/pdf') {
            header('Content-Type: ' . $mimeType);
            readfile($filePath);
            exit;
        } else {
            return $this->response->download($filePath, null)->setFileName($file['file_name']);
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
        return view('HRD/dokumenSPV');
    }
    public function dokumenManager()
    {
        return view('HRD/dokumenManager');
    }
    public function dokumenDireksi()
    {
        return view('HRD/dokumenDireksi');
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
        return view('HRD/dokumenUmum');
    }

    public function aktivitas()
    {
        return view('HRD/aktivitas');
    }
}