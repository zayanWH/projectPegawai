<?php

namespace App\Controllers;

use App\Models\FolderModel;
use App\Models\FileModel;
use App\Models\UserModel;
use CodeIgniter\Controller;
use CodeIgniter\Session\Session;
use CodeIgniter\Exceptions\PageNotFoundException;

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

    public function index()
    {
        return view('Manager/dashboard');
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
            'title'           => 'Dokumen Saya',
            'personalFolders' => $personalFolders,
            'orphanFiles'     => $orphanFiles,
            'isStaffFolder'   => false,      // Bukan folder staff
            'isSupervisorFolder' => false,   // Bukan folder supervisor
            'canManageFolder' => true,       // Manager bisa mengelola folder pribadinya
            'folderId'        => null,       // Root folder, jadi tidak ada folderId spesifik
        ];

        return view('Manager/dokumenManager', $data);
    }

    public function dokumenBersama()
    {
        return view('Umum/dokumenBersama');
    }

    public function dokumenUmum()
    {
        return view('Umum/dokumenUmum');
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

        $currentFolder = $this->folderModel->find($folderId);

        if (!$currentFolder) {
            throw PageNotFoundException::forPageNotFound('Folder tidak ditemukan.');
        }

        // Validasi akses untuk folder personal Manager
        if ($currentFolder['folder_type'] === 'personal' && $currentFolder['owner_id'] !== $userId) {
            // Jika mencoba akses folder personal orang lain, arahkan ke dashboard Manager
            return redirect()->to(base_url('manager/dokumen-manager'))->with('error', 'Anda tidak memiliki akses ke folder personal ini.');
        }

        // Validasi akses untuk folder shared
        if ($currentFolder['folder_type'] === 'shared') {
            $userRole = $this->session->get('role_id'); // Mengambil role_id dari sesi
            $accessRoles = json_decode($currentFolder['access_roles'] ?? '[]', true);

            if (empty($accessRoles) || !in_array($userRole, $accessRoles)) {
                return redirect()->to(base_url('manager/dokumen-manager'))->with('error', 'Anda tidak memiliki izin untuk folder shared ini.');
            }
        }

        $subFolders = $this->folderModel->where('parent_id', $folderId)->findAll();
        $filesInFolder = $this->fileModel->where('folder_id', $folderId)->findAll();
        $breadcrumbs = $this->folderModel->getBreadcrumbs($folderId);

        $data = [
            'title'           => 'Folder: ' . $currentFolder['name'],
            'folderName'      => $currentFolder['name'],
            'folderId'        => $currentFolder['id'],
            'isShared'        => (bool)$currentFolder['is_shared'],
            'sharedType'      => $currentFolder['shared_type'],
            'folderType'      => $currentFolder['folder_type'],
            'subFolders'      => $subFolders,
            'filesInFolder'   => $filesInFolder,
            'breadcrumbs'     => $breadcrumbs,
            'isStaffFolder'   => false,         // Bukan folder staff
            'isSupervisorFolder' => false,      // Bukan folder supervisor
            'canManageFolder' => true,          // Manager bisa mengelola folder pribadinya
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
            'title'             => 'Dokumen Seluruh Staff',
            'personalFolders'   => [],
            'orphanFiles'       => [],
            'currentUserId'     => $userId,
            'currentRole'       => $this->session->get('user_role'),
            'isStaffFolder'     => true, // Ini adalah halaman daftar dokumen staff
            'isSupervisorFolder' => false, // Bukan halaman daftar dokumen supervisor
            'canManageFolder'   => false, // Manager tidak mengelola di halaman daftar ini
            'folderId'          => null, // Tidak ada folder spesifik di halaman daftar
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
            'title'              => 'Dokumen Seluruh Supervisor',
            'personalFolders'    => [],
            'orphanFiles'        => [],
            'currentUserId'      => $userId,
            'currentRole'        => $this->session->get('user_role'),
            'isStaffFolder'      => false, // Bukan halaman daftar dokumen staff
            'isSupervisorFolder' => true,  // Ini adalah halaman daftar dokumen supervisor
            'canManageFolder'    => false, // Manager tidak mengelola di halaman daftar ini
            'folderId'           => null, // Tidak ada folder spesifik di halaman daftar
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
            'title'              => 'Folder Staff: ' . $currentFolder['name'],
            'folderName'         => $currentFolder['name'],
            'folderId'           => $currentFolder['id'],
            'isShared'           => (bool)$currentFolder['is_shared'],
            'sharedType'         => $currentFolder['shared_type'],
            'folderType'         => $currentFolder['folder_type'],
            'subFolders'         => $subFolders,
            'filesInFolder'      => $filesInFolder,
            'breadcrumbs'        => $breadcrumbs,
            'isStaffFolder'      => true,       // Flag ini menyatakan ini adalah folder Staff
            'isSupervisorFolder' => false,      // Bukan folder Supervisor
            'canManageFolder'    => false,      // Manager tidak bisa mengelola (hanya lihat) folder Personal Staff
            'targetUserId'       => $currentFolder['owner_id'], // ID Staff yang memiliki folder ini
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
            'title'              => 'Folder Supervisor: ' . $currentFolder['name'],
            'folderName'         => $currentFolder['name'],
            'folderId'           => $currentFolder['id'],
            'isShared'           => (bool)$currentFolder['is_shared'],
            'sharedType'         => $currentFolder['shared_type'],
            'folderType'         => $currentFolder['folder_type'],
            'subFolders'         => $subFolders,
            'filesInFolder'      => $filesInFolder,
            'breadcrumbs'        => $breadcrumbs,
            'isStaffFolder'      => false,      // Bukan folder Staff
            'isSupervisorFolder' => true,       // Flag ini menyatakan ini adalah folder Supervisor
            'canManageFolder'    => false,      // Manager tidak bisa mengelola (hanya lihat) folder Personal Supervisor
            'targetUserId'       => $currentFolder['owner_id'], // ID Supervisor yang memiliki folder ini
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
            'name'         => $folderName,
            'parent_id'    => $parentId,
            'owner_id'     => $targetUserId, // Gunakan targetUserId (bisa ID Manager, Staff, atau SPV)
            'folder_type'  => $folderType,
            'is_shared'    => (int)$isShared,
            'shared_type'  => ((int)$isShared === 1) ? $sharedType : null,
            'access_roles' => ((int)$isShared === 1 && !empty($accessRoles)) ? json_encode($accessRoles) : null,
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
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
}