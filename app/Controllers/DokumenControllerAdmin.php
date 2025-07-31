<?php

namespace App\Controllers;
use App\Models\UserModel;
use App\Models\RoleModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\FolderModel; 
use App\Models\FileModel;   
use App\Models\LogAksesModel;   

class DokumenControllerAdmin extends BaseController
{
    protected $userModel;
    protected $roleModel;
    protected $folderModel;
    protected $fileModel;
    protected $logAksesModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->folderModel = new FolderModel();
        $this->fileModel = new FileModel();
        $this->logAksesModel = new LogAksesModel();
    }

    public function index()
    {
        // Mendapatkan total jumlah folder
        $totalFolders = $this->folderModel->countAllResults();

        // Mendapatkan total jumlah file
        $totalFiles = $this->fileModel->countAllResults();

        // Mendapatkan total jumlah user
        $totalUsers = $this->userModel->countAllResults();

        // --- Perhitungan Total Penyimpanan Terpakai ---
        $totalUsedStorageKB = 0;
        
        // Cek apakah method getTotalFileSize ada di FileModel
        if (method_exists($this->fileModel, 'getTotalFileSize')) {
            // Asumsi method ini mengembalikan total dalam bytes
            $totalUsedStorageBytes = $this->fileModel->getTotalFileSize();
            // Perbaiki konversi dari bytes ke KB
            $totalUsedStorageKB = $totalUsedStorageBytes / 1024;
        } else {
            // Jika method belum ada, gunakan perhitungan manual atau dummy data
            $db = \Config\Database::connect();
            if ($db->tableExists('files')) {
                $result = $db->query("SELECT COALESCE(SUM(file_size), 0) as total FROM files")->getRow();
                // Nilai 'total' dari query adalah dalam bytes, jadi konversi ke KB
                $totalUsedStorageBytes = $result->total ?? 0;
                $totalUsedStorageKB = $totalUsedStorageBytes / 1024;
            } else {
                // Data dummy jika tabel files belum ada
                $totalUsedStorageKB = $totalUsers * 75 * 1024; // 75MB per user (ini sudah dalam KB)
            }
        }

        // Sekarang, $totalUsedStorageKB sudah benar dalam satuan Kilobyte.
        // Anda bisa langsung menggunakannya atau mengonversinya ke GB jika perlu.
        $totalUsedStorageGB = round($totalUsedStorageKB / 1024 / 1024, 2);
        $totalStorageLimitGB = 5;

        // Gunakan helper function untuk format tampilan yang benar
        $formattedUsedStorage = $this->formatStorageSize($totalUsedStorageKB);
        $formattedTotalLimit = $totalStorageLimitGB . ' GB';

        $latestLogs = $this->logAksesModel
            ->select('log_akses_file.*, users.name as user_name, files.file_name, folders.name as folder_name, roles.name as role_name')
            ->join('users', 'users.id = log_akses_file.user_id', 'left')
            ->join('files', 'files.id = log_akses_file.file_id', 'left')
            ->join('folders', 'folders.id = files.folder_id', 'left')
            ->join('roles', 'roles.id = users.role_id', 'left') // Tambahkan join ini
            ->orderBy('log_akses_file.timestamp', 'DESC')
            ->limit(10)
            ->findAll();

        $data = [
            'title' => 'Dashboard Admin',
            'totalFolders'         => $totalFolders,
            'totalFiles'           => $totalFiles,
            'totalUser'            => $totalUsers,
            'totalUsedStorageGB'   => $totalUsedStorageGB,
            'totalStorageLimitGB'  => $totalStorageLimitGB,
            'formattedUsedStorage' => $formattedUsedStorage,
            'formattedTotalLimit'  => $formattedTotalLimit,
            'latestLogs'           => $latestLogs // Tambahkan data ini ke view
        ];

        return view('Admin/dashboard', $data);
    }

    /**
     * Mengambil data storage usage berdasarkan jabatan untuk chart
     * @return ResponseInterface JSON response
     */
    public function getStorageByRole()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON([
                'status'  => 'error',
                'message' => 'Metode request tidak diizinkan.'
            ]);
        }

        try {
            // Cek apakah tabel files ada
            $db = \Config\Database::connect();
            $tableExists = $db->tableExists('files');

            if (!$tableExists) {
                // ... (kode dummy) ...
            } else {
                // Query untuk mendapatkan storage usage per role.
                // Kolom 'file_size' berisi bytes, jadi totalnya juga dalam bytes.
                $storageData = $this->userModel
                    ->select('
                        roles.name as role_name,
                        roles.max_upload_size_mb,
                        COUNT(DISTINCT users.id) as user_count,
                        COALESCE(SUM(files.file_size), 0) as total_storage_bytes
                    ')
                    ->join('roles', 'roles.id = users.role_id')
                    ->join('files', 'files.uploader_id = users.id', 'left')
                    ->where('users.is_active', 1)
                    ->groupBy('roles.id, roles.name, roles.max_upload_size_mb')
                    ->orderBy('roles.level', 'ASC')
                    ->findAll();
            }

            // Hitung total storage yang digunakan (dalam bytes)
            $totalUsedBytes = array_sum(array_column($storageData, 'total_storage_bytes'));
            $totalStorageLimitGB = 5;
            
            // Format data untuk chart
            $chartData = [];
            $statisticsData = [];
            $colors = ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444', '#06B6D4'];
            $borderColors = ['#1E40AF', '#059669', '#D97706', '#7C3AED', '#DC2626', '#0891B2'];
            
            foreach ($storageData as $index => $data) {
                // Konversi total_storage_bytes ke MB
                $storageMB = round($data['total_storage_bytes'] / (1024 * 1024), 1);
                $percentage = $totalUsedBytes > 0 ? round(($data['total_storage_bytes'] / $totalUsedBytes) * 100, 1) : 0;
                
                $chartData[] = [
                    'label' => $data['role_name'],
                    'data' => $storageMB,
                    'backgroundColor' => $colors[$index % count($colors)],
                    'borderColor' => $borderColors[$index % count($borderColors)]
                ];
                
                $statisticsData[] = [
                    'role_name' => $data['role_name'],
                    'user_count' => $data['user_count'],
                    'storage_mb' => $storageMB,
                    'percentage' => $percentage,
                    'max_storage_mb' => $data['max_upload_size_mb'],
                    'color' => $colors[$index % count($colors)]
                ];
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data' => [
                    'chart_data' => $chartData,
                    'statistics' => $statisticsData,
                    'total_used_mb' => round($totalUsedBytes / (1024 * 1024), 1),
                    'total_used_kb' => round($totalUsedBytes / 1024, 1),
                    // Tambahkan baris ini untuk mengirim batas penyimpanan
                    'total_storage_limit_gb' => $totalStorageLimitGB 
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error getting storage by role: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Helper function untuk format storage size
     */
    private function formatStorageSize(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    if ($bytes == 0) {
        return '0 B';
    }
    $i = floor(log($bytes, 1024));
    $value = $bytes / (1024 ** $i);
    return round($value, 2) . ' ' . $units[$i];
}

    // [Rest of your existing methods remain unchanged]
    public function manajemenUser()
    {
        $data['users'] = $this->userModel->getAllUsersWithRoleNames();
        return view('Admin/manajemenUser', $data);
    }

    // Metode untuk manajemen jabatan
    public function manajemenJabatan()
    {
        $data['roles'] = $this->roleModel->findAll(); // Mengambil semua data jabatan
        return view('Admin/manajemenJabatan', $data); // Pastikan path view sesuai
    }

    /**
     * Mengambil data jabatan untuk diisi ke form edit modal.
     * Dipanggil via AJAX GET request.
     * @param int $id ID jabatan
     * @return ResponseInterface JSON response
     */
    public function getRoleForEdit($id)
    {
        $role = $this->roleModel->find($id);

        if ($role) {
            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $role
            ]);
        } else {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Jabatan tidak ditemukan.'
            ]);
        }
    }

    /**
     * Memperbarui data jabatan.
     * Dipanggil via AJAX POST request.
     * @return ResponseInterface JSON response
     */
    public function updateJabatan()
    {
        if (!$this->request->isAJAX() || !$this->request->is('post')) {
            return $this->response->setStatusCode(405)->setJSON([
                'status'  => 'error',
                'message' => 'Metode request tidak diizinkan.'
            ]);
        }

        $id = $this->request->getPost('id');
        $name = $this->request->getPost('name');
        $level = (int)$this->request->getPost('level');
        $maxStorage = (int)$this->request->getPost('max_upload_size_mb');

        // Validasi basic
        if (empty($id) || empty($name) || $level <= 0 || $maxStorage < 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Data yang dikirim tidak lengkap atau tidak valid.'
            ]);
        }

        // Cek apakah jabatan dengan ID tersebut ada
        $existingRole = $this->roleModel->find($id);
        if (!$existingRole) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Jabatan tidak ditemukan.'
            ]);
        }

        // Cek apakah nama sudah digunakan oleh jabatan lain
        if ($this->roleModel->isNameExists($name, $id)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Nama jabatan ini sudah terdaftar.',
                'errors'  => ['name' => 'Nama jabatan ini sudah terdaftar.']
            ]);
        }

        $data = [
            'name'               => $name,
            'level'              => $level,
            'max_upload_size_mb' => $maxStorage
        ];

        try {
            // Gunakan method updateRole yang sudah dibuat di model
            if ($this->roleModel->updateRole($id, $data)) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => 'Data jabatan berhasil diperbarui.'
                ]);
            } else {
                $modelErrors = $this->roleModel->errors();
                if (!empty($modelErrors)) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'status'  => 'error',
                        'message' => 'Gagal memperbarui data jabatan. Validasi gagal.',
                        'errors'  => $modelErrors
                    ]);
                } else {
                    return $this->response->setStatusCode(500)->setJSON([
                        'status'  => 'error',
                        'message' => 'Gagal memperbarui data jabatan. Mungkin ada masalah dengan data yang diberikan atau database.'
                    ]);
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Error updating role: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ]);
        }
    }

    public function deleteJabatan()
    {
        // Cek apakah request adalah AJAX dan metode POST
        if (!$this->request->isAJAX() || !$this->request->is('post')) {
            return $this->response->setStatusCode(405)->setJSON([
                'status'  => 'error',
                'message' => 'Metode request tidak diizinkan.'
            ]);
        }

        $id = $this->request->getPost('id');

        // Validasi ID
        if (empty($id) || !is_numeric($id) || (int)$id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'ID jabatan tidak valid.'
            ]);
        }

        $id = (int)$id;

        try {
            // Cek apakah jabatan ada
            $role = $this->roleModel->find($id);
            if (!$role) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status'  => 'error',
                    'message' => 'Jabatan tidak ditemukan.'
                ]);
            }

            // *PENTING:* Cek apakah ada user yang masih menggunakan role ini
            // Ini penting untuk menjaga integritas data. Jika ada user, Anda tidak boleh menghapus rolenya.
            // Asumsi: Di UserModel Anda, ada foreign key ke roles.id
            $usersWithRole = $this->userModel->where('role_id', $id)->countAllResults();
            if ($usersWithRole > 0) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'Tidak dapat menghapus jabatan ini karena masih ada ' . $usersWithRole . ' user yang terhubung dengan jabatan ini. Harap ubah jabatan user tersebut terlebih dahulu.'
                ]);
            }

            // Lakukan penghapusan
            if ($this->roleModel->delete($id)) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => 'Jabatan berhasil dihapus.'
                ]);
            } else {
                // Ini bisa terjadi jika ada masalah DB lain yang tidak ditangkap oleh find() atau $usersWithRole
                return $this->response->setStatusCode(500)->setJSON([
                    'status'  => 'error',
                    'message' => 'Gagal menghapus jabatan dari database.'
                ]);
            }
        } catch (\Exception $e) {
            // Tangani error umum seperti masalah koneksi DB
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ]);
        }
    }

    public function addJabatan()
    {
        // Hanya pastikan metode request adalah POST.
        // Hapus !$this->request->isAJAX() karena fetch dengan FormData tidak selalu mengirim X-Requested-With header.
        if (!$this->request->is('post')) {
            return $this->response->setStatusCode(405)->setJSON([
                'status'  => 'error',
                'message' => 'Metode request tidak diizinkan.'
            ]);
        }

        // Ambil data dari request POST
        $data = [
            'name'               => $this->request->getPost('name'),
            'level'              => (int)$this->request->getPost('level'),
            'max_upload_size_mb' => (int)$this->request->getPost('max_upload_size_mb')
        ];

        try {
            // Lakukan validasi menggunakan rules yang sudah didefinisikan di RoleModel
            if (!$this->roleModel->validate($data)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'Validasi gagal',
                    'errors'  => $this->roleModel->errors() // Ambil error dari model
                ]);
            }

            // Simpan data baru ke database
            if ($this->roleModel->insert($data)) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => 'Jabatan berhasil ditambahkan.'
                ]);
            } else {
                // Jika insert gagal tanpa error validasi (misalnya masalah DB)
                return $this->response->setStatusCode(500)->setJSON([
                    'status'  => 'error',
                    'message' => 'Gagal menambahkan jabatan ke database. Coba lagi.'
                ]);
            }
        } catch (\Exception $e) {
            // Tangani error umum
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Mengambil data user untuk diisi ke form edit modal.
     * Dipanggil via AJAX GET request.
     * @param int $id ID user
     * @return ResponseInterface JSON response
     */
    public function getUserForEdit($id)
    {
        $user = $this->userModel->getUserWithRoleNameById($id);
        $roles = $this->roleModel->findAll();

        if ($user) {
            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $user,
                'roles'  => $roles
            ]);
        } else {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'User tidak ditemukan.'
            ]);
        }
    }

    /**
     * Memperbarui data user.
     * Dipanggil via AJAX POST request.
     * @return ResponseInterface JSON response
     */
    public function updateUser()
    {
        if (!$this->request->isAJAX() || !$this->request->is('post')) {
            return $this->response->setStatusCode(405)->setJSON([
                'status'  => 'error',
                'message' => 'Metode request tidak diizinkan.'
            ]);
        }

        $id = $this->request->getPost('id');

        // Ambil data user saat ini dari database untuk membandingkan email
        $currentUser = $this->userModel->find($id);
        $originalEmail = $currentUser['email'] ?? null;
        $submittedEmail = $this->request->getPost('email');

        // Definisikan aturan validasi dasar
        $rules = [
            'id'        => 'required|integer',
            'name'      => 'required|min_length[3]|max_length[255]',
            'role_id'   => 'required|integer',
            'is_active' => 'required|in_list[0,1]',
        ];

        // Definisikan pesan validasi dasar
        $messages = [
            'name' => [
                'required'   => 'Nama lengkap harus diisi.',
                'min_length' => 'Nama lengkap minimal 3 karakter.',
                'max_length' => 'Nama lengkap maksimal 255 karakter.'
            ],
            'email' => [
                'required'    => 'Email harus diisi.',
                'valid_email' => 'Format email tidak valid.',
                'max_length'  => 'Email maksimal 255 karakter.',
                'is_unique'   => 'Email ini sudah terdaftar.'
            ],
            'password' => [
                'min_length' => 'Password minimal 8 karakter.'
            ],
            'role_id' => [
                'required' => 'Jabatan harus dipilih.',
                'integer'  => 'Jabatan tidak valid.'
            ],
            'is_active' => [
                'required' => 'Status harus dipilih.',
                'in_list'  => 'Status tidak valid.'
            ]
        ];

        // Logika kondisional untuk validasi email
        if ($submittedEmail !== $originalEmail) {
            // Jika email diubah, terapkan aturan is_unique dengan mengecualikan ID saat ini
            $rules['email'] = "required|valid_email|max_length[255]|is_unique[users.email,id,{$id}]";
        } else {
            // Jika email tidak diubah, cukup validasi format dan keberadaannya
            $rules['email'] = 'required|valid_email|max_length[255]';
        }

        // Jika password diisi, tambahkan aturan validasi password
        if ($this->request->getPost('password')) {
            $rules['password'] = 'permit_empty|min_length[8]';
        }

        // Jalankan validasi dengan aturan dan pesan yang telah disesuaikan
        if (!$this->validate($rules, $messages)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Validasi gagal',
                'errors'  => $this->validator->getErrors()
            ]);
        }

        $data = [
            'name'      => $this->request->getPost('name'),
            'email'     => $this->request->getPost('email'),
            'role_id'   => $this->request->getPost('role_id'),
            'is_active' => (int)$this->request->getPost('is_active')
        ];

        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        try {
            // Skip model validation karena kita sudah validasi di controller
            if ($this->userModel->skipValidation(true)->update($id, $data)) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => 'Data user berhasil diperbarui.'
                ]);
            } else {
                return $this->response->setStatusCode(500)->setJSON([
                    'status'  => 'error',
                    'message' => 'Gagal memperbarui data user. Mungkin ada masalah dengan data yang diberikan atau database.'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ]);
        }
    }

    public function addUser()
    {
        if (!$this->request->isAJAX() || !$this->request->is('post')) {
            return $this->response->setStatusCode(405)->setJSON([
                'status'  => 'error',
                'message' => 'Metode request tidak diizinkan.'
            ]);
        }

        // Definisikan aturan validasi
        $rules = [
            'name'      => 'required|min_length[3]|max_length[255]',
            'email'     => 'required|valid_email|max_length[255]|is_unique[users.email]',
            'password'  => 'required|min_length[8]',
            'role_id'   => 'required|integer',
            'is_active' => 'required|in_list[0,1]',
        ];

        // Definisikan pesan validasi
        $messages = [
            'name' => [
                'required'   => 'Nama lengkap harus diisi.',
                'min_length' => 'Nama lengkap minimal 3 karakter.',
                'max_length' => 'Nama lengkap maksimal 255 karakter.'
            ],
            'email' => [
                'required'    => 'Email harus diisi.',
                'valid_email' => 'Format email tidak valid.',
                'max_length'  => 'Email maksimal 255 karakter.',
                'is_unique'   => 'Email ini sudah terdaftar.'
            ],
            'password' => [
                'required'   => 'Password harus diisi.',
                'min_length' => 'Password minimal 8 karakter.'
            ],
            'role_id' => [
                'required' => 'Jabatan harus dipilih.',
                'integer'  => 'Jabatan tidak valid.'
            ],
            'is_active' => [
                'required' => 'Status harus dipilih.',
                'in_list'  => 'Status tidak valid.'
            ]
        ];

        // Jalankan validasi
        if (!$this->validate($rules, $messages)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Validasi gagal',
                'errors'  => $this->validator->getErrors()
            ]);
        }

        // Siapkan data untuk disimpan
        $data = [
            'name'          => $this->request->getPost('name'),
            'email'         => $this->request->getPost('email'),
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role_id'       => (int)$this->request->getPost('role_id'),
            'is_active'     => (int)$this->request->getPost('is_active')
        ];

        try {
            // Simpan data user baru
            if ($this->userModel->insert($data)) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => 'User berhasil ditambahkan.'
                ]);
            } else {
                return $this->response->setStatusCode(500)->setJSON([
                    'status'  => 'error',
                    'message' => 'Gagal menambahkan user.',
                    'errors'  => $this->userModel->errors()
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Mengambil semua data roles untuk dropdown
     * Dipanggil via AJAX GET request.
     * @return ResponseInterface JSON response
     */
    public function getRoles()
    {
        try {
            $roles = $this->roleModel->findAll();
            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $roles
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Gagal mengambil data roles: ' . $e->getMessage()
            ]);
        }
    }

    public function deleteUser()
    {
        // Cek apakah request adalah AJAX dan metode POST
        if (!$this->request->isAJAX() || !$this->request->is('post')) {
            return $this->response->setStatusCode(405)->setJSON([
                'status'  => 'error',
                'message' => 'Metode request tidak diizinkan.'
            ]);
        }

        $id = $this->request->getPost('id');

        // Validasi ID
        if (empty($id) || !is_numeric($id) || (int)$id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'ID user tidak valid.'
            ]);
        }

        $id = (int)$id;

        try {
            // Cek apakah user ada
            $user = $this->userModel->find($id);
            if (!$user) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status'  => 'error',
                    'message' => 'User tidak ditemukan.'
                ]);
            }

            log_message('info', "Attempting to delete user ID: {$id}");

            // Cek apakah ini adalah user yang sedang login
            $session = session();
            $currentUserId = $session->get('user_id');
            if ($id == $currentUserId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'Anda tidak dapat menghapus akun Anda sendiri.'
                ]);
            }

            // Cek apakah user yang akan dihapus adalah admin terakhir yang aktif
            // Asumsi role_id 1 adalah admin
            $adminCount = $this->userModel->where('role_id', 1)->where('is_active', 1)->countAllResults();
            if ($user['role_id'] == 1 && $user['is_active'] == 1 && $adminCount <= 1) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'Tidak dapat menghapus admin terakhir yang aktif.'
                ]);
            }

            // --- BAGIAN INI MENJADI SANGAT SEDERHANA SETELAH FOREIGN KEY DIATUR ---
            // Gunakan model untuk menghapus user.
            // Database akan mengurus cascade/set null secara otomatis.
            $deleteResult = $this->userModel->delete($id);

            if ($deleteResult) {
                log_message('info', "User deleted successfully. ID: {$id}");

                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => 'User berhasil dihapus.'
                ]);
            } else {
                log_message('error', "Failed to delete user ID: {$id}. DB operation failed or ID not found by model.");
                
                return $this->response->setStatusCode(500)->setJSON([
                    'status'  => 'error',
                    'message' => 'Gagal menghapus user dari database. Mungkin ada masalah dengan integritas data atau konfigurasi FK.'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', "Exception during user deletion. ID: {$id}, Error: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());

            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ]);
        }
    }

    public function searchUsers()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON([
                'status'  => 'error',
                'message' => 'Metode request tidak diizinkan.'
            ]);
        }

        $searchTerm = $this->request->getGet('search');
        
        try {
            if (empty($searchTerm)) {
                // Jika search kosong, tampilkan semua user
                $users = $this->userModel->getAllUsersWithRoleNames();
            } else {
                // Lakukan pencarian berdasarkan nama atau email
                $users = $this->userModel->searchUsersByName($searchTerm);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $users
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ]);
        }
    }

    

    public function monitoringStorage()
{
    // Data untuk statistik umum
    $data = [
        'totalUsers' => $this->userModel->countAllResults(),
        'totalFiles' => $this->fileModel->countAllResults(),
        'totalFolders' => $this->folderModel->countAllResults(),
    ];
    
    return view('Admin/monitoringStorage', $data);
}

/**
 * API untuk mendapatkan data storage berdasarkan jabatan
 */
public function getStorageByPosition()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(405)->setJSON([
            'status' => 'error',
            'message' => 'Method not allowed'
        ]);
    }

    try {
        $db = \Config\Database::connect();
        
        // Query untuk mendapatkan storage usage per jabatan
        $query = "
            SELECT 
                r.name as jabatan,
                COUNT(DISTINCT u.id) as jumlah_user,
                COUNT(f.id) as total_file,
                COALESCE(SUM(f.file_size), 0) as total_size_bytes
            FROM roles r
            LEFT JOIN users u ON u.role_id = r.id AND u.is_active = 1
            LEFT JOIN files f ON f.uploader_id = u.id
            GROUP BY r.id, r.name, r.level
            ORDER BY r.level ASC
        ";
        
        $result = $db->query($query)->getResultArray();
        
        $storageData = [];
        foreach ($result as $row) {
            $storageData[] = [
                'jabatan' => $row['jabatan'],
                'jumlah_user' => (int)$row['jumlah_user'],
                'total_file' => (int)$row['total_file'],
                'total_size' => $this->formatStorageSize($row['total_size_bytes']),
                'total_size_bytes' => (int)$row['total_size_bytes']
            ];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $storageData
        ]);

    } catch (\Exception $e) {
        log_message('error', 'Error getting storage by position: ' . $e->getMessage());
        return $this->response->setStatusCode(500)->setJSON([
            'status' => 'error',
            'message' => 'Server error: ' . $e->getMessage()
        ]);
    }
}

/**
 * API untuk mendapatkan top 5 users dengan storage terbesar
 */
public function getTopStorageUsers()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(405)->setJSON([
            'status' => 'error',
            'message' => 'Method not allowed'
        ]);
    }

    try {
        $db = \Config\Database::connect();
        
        // Query untuk mendapatkan top 5 users
        $query = "
            SELECT 
                u.name as nama_user,
                r.name as jabatan,
                COUNT(f.id) as total_file,
                COALESCE(SUM(f.file_size), 0) as total_size_bytes
            FROM users u
            LEFT JOIN roles r ON r.id = u.role_id
            LEFT JOIN files f ON f.uploader_id = u.id
            WHERE u.is_active = 1
            GROUP BY u.id, u.name, r.name
            HAVING total_size_bytes > 0
            ORDER BY total_size_bytes DESC
            LIMIT 5
        ";
        
        $result = $db->query($query)->getResultArray();
        
        $topUsers = [];
        foreach ($result as $row) {
            $topUsers[] = [
                'nama_user' => $row['nama_user'],
                'jabatan' => $row['jabatan'],
                'total_file' => (int)$row['total_file'],
                'total_size' => $this->formatStorageSize($row['total_size_bytes']),
                'total_size_bytes' => (int)$row['total_size_bytes']
            ];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $topUsers
        ]);

    } catch (\Exception $e) {
        log_message('error', 'Error getting top storage users: ' . $e->getMessage());
        return $this->response->setStatusCode(500)->setJSON([
            'status' => 'error',
            'message' => 'Server error: ' . $e->getMessage()
        ]);
    }
}

/**
 * API untuk mendapatkan file terbesar
 */
public function getLargestFiles()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(405)->setJSON([
            'status' => 'error',
            'message' => 'Method not allowed'
        ]);
    }

    try {
        $db = \Config\Database::connect();
        
        // Query untuk mendapatkan file terbesar
        $query = "
            SELECT 
                f.file_name as nama_file,
                f.file_size as ukuran_bytes,
                f.file_type,
                u.name as uploader,
                r.name as jabatan,
                DATE_FORMAT(f.created_at, '%d %M %Y') as tanggal_upload
            FROM files f
            LEFT JOIN users u ON u.id = f.uploader_id
            LEFT JOIN roles r ON r.id = u.role_id
            ORDER BY f.file_size DESC
            LIMIT 10
        ";
        
        $result = $db->query($query)->getResultArray();
        
        $largestFiles = [];
        foreach ($result as $row) {
            // Tentukan icon berdasarkan file type
            $fileIcon = $this->getFileIcon($row['file_type']);
            
            $largestFiles[] = [
                'nama_file' => $row['nama_file'],
                'ukuran' => $this->formatStorageSize($row['ukuran_bytes']),
                'ukuran_bytes' => (int)$row['ukuran_bytes'],
                'uploader' => $row['uploader'] ?? 'Unknown',
                'jabatan' => $row['jabatan'] ?? 'Unknown',
                'tanggal_upload' => $row['tanggal_upload'],
                'file_icon' => $fileIcon
            ];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $largestFiles
        ]);

    } catch (\Exception $e) {
        log_message('error', 'Error getting largest files: ' . $e->getMessage());
        return $this->response->setStatusCode(500)->setJSON([
            'status' => 'error',
            'message' => 'Server error: ' . $e->getMessage()
        ]);
    }
}

/**
 * Helper function untuk menentukan icon file
 */
private function getFileIcon($fileType)
{
    $iconMap = [
        'video' => '<svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-4 4 4 4-4v4z" clip-rule="evenodd"></path></svg>',
        'document' => '<svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zM11 6V3.586L14.414 7H12a2 2 0 01-2-2z" clip-rule="evenodd"></path></svg>',
        'image' => '<svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-4 4 4 4-4v4z" clip-rule="evenodd"></path></svg>',
        'archive' => '<svg class="w-5 h-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path></svg>'
    ];

    // Deteksi berdasarkan MIME type atau extension
    if (strpos($fileType, 'video') !== false || strpos($fileType, '.mp4') !== false) {
        return $iconMap['video'];
    } elseif (strpos($fileType, 'image') !== false) {
        return $iconMap['image'];
    } elseif (strpos($fileType, 'application/zip') !== false || strpos($fileType, 'application/x-rar') !== false) {
        return $iconMap['archive'];
    } else {
        return $iconMap['document'];
    }
}


    public function logAksesFile()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $query = $this->logAksesModel
            ->select('log_akses_file.*, users.name as user_name, roles.name as role_name')
            ->join('users', 'users.id = log_akses_file.user_id', 'left')
            ->join('roles', 'roles.id = users.role_id', 'left');

        // Terapkan filter tanggal jika ada input
        if (!empty($startDate) && !empty($endDate)) {
            // Flatpickr mengirimkan format "d F Y" (misal: "01 July 2025")
            // Kita perlu mengubahnya ke format Y-m-d H:i:s untuk database
            $startDateTime = date('Y-m-d H:i:s', strtotime($startDate . ' 00:00:00'));
            $endDateTime = date('Y-m-d H:i:s', strtotime($endDate . ' 23:59:59'));
            
            // PASTIKAN INI 'log_akses_file.timestamp'
            $query->where('log_akses_file.timestamp >=', $startDateTime)
                  ->where('log_akses_file.timestamp <=', $endDateTime);
        }

        // PASTIKAN INI 'log_akses_file.timestamp'
        $logAkses = $query->orderBy('log_akses_file.timestamp', 'DESC')->findAll();

        $data = [
            'title'     => 'Log Akses File',
            'logs'      => $logAkses,
            'startDate' => $startDate,
            'endDate'   => $endDate,
        ];

        return view('Admin/logAksesFile', $data);
    }
}