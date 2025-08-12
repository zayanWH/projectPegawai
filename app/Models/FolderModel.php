<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\RawSql; // Pastikan ini ada jika menggunakan JSON_CONTAINS

class FolderModel extends Model
{
    protected $table = 'folders';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['name', 'parent_id', 'folder_type', 'is_shared', 'shared_type', 'owner_id', 'access_roles', 'owner_role'];

    protected bool $allowEmptyInserts = false;

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'name' => 'required|min_length[1]|max_length[255]',
        'folder_type' => 'required|in_list[personal,shared]',
        'is_shared' => 'required|in_list[0,1]',
        'owner_id' => 'required|integer',
        'parent_id' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Nama folder wajib diisi.',
            'min_length' => 'Nama folder minimal 1 karakter.',
            'max_length' => 'Nama folder maksimal 255 karakter.',
        ],
        'folder_type' => [
            'required' => 'Jenis folder wajib diisi.',
            'in_list' => 'Jenis folder tidak valid.',
        ],
        'is_shared' => [
            'required' => 'Status share folder wajib diisi.',
            'in_list' => 'Status share folder tidak valid.',
        ],
        'owner_id' => [
            'required' => 'Owner ID wajib diisi.',
            'integer' => 'Owner ID harus berupa angka.',
        ],
        'parent_id' => [
            'integer' => 'Parent ID harus berupa angka.',
        ],
    ];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert = ['setDefaultsBeforeInsert'];
    protected $afterInsert = [];
    protected $beforeUpdate = ['checkNameExistsBeforeUpdate'];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    protected function setDefaultsBeforeInsert(array $data)
    {
        if (!isset($data['data']['is_shared'])) {
            $data['data']['is_shared'] = 0;
        }

        if (!isset($data['data']['folder_type'])) {
            $data['data']['folder_type'] = 'personal';
        }

        return $data;
    }

    protected function checkNameExistsBeforeUpdate(array $data)
    {
        // Pastikan ID dan nama baru ada di data
        if (isset($data['data']['name']) && isset($data['id'])) {
            $folderId = $data['id'][0];
            $newName = $data['data']['name'];

            // Ambil data folder yang lama untuk mendapatkan parent_id dan owner_id
            $folder = $this->find($folderId);

            // Jika folder ada, lakukan pengecekan nama
            if ($folder && $this->isNameExists($newName, $folder['parent_id'], $folder['owner_id'], $folderId)) {
                // Jika nama sudah ada, lempar exception
                throw new \Exception('Nama folder sudah digunakan di lokasi yang sama.');
            }
        }

        return $data;
    }

    public function canDeleteFolder($folderId)
    {
        $errors = [];

        $subfolderCount = $this->where('parent_id', $folderId)->countAllResults();
        if ($subfolderCount > 0) {
            $errors[] = 'Folder memiliki ' . $subfolderCount . ' subfolder.';
        }

        return [
            'can_delete' => empty($errors),
            'errors' => $errors
        ];
    }

    public function findOrCreateByPath(string $path, ?int $rootParentId, int $userId): ?int
    {
        if (empty($path)) {
            return $rootParentId;
        }

        $pathParts = explode('/', trim($path, '/'));
        $currentParentId = $rootParentId;

        foreach ($pathParts as $part) {
            $folder = $this->where('name', $part)
                ->where('parent_id', $currentParentId)
                ->where('owner_id', $userId)
                ->first();

            if ($folder) {
                $currentParentId = $folder['id'];
            } else {
                $data = [
                    'name' => $part,
                    'parent_id' => $currentParentId,
                    'folder_type' => 'personal',
                    'is_shared' => 0,
                    'owner_id' => $userId,
                ];

                $this->insert($data);
                $currentParentId = $this->insertID();
            }
        }

        return $currentParentId;
    }

    public function isNameExists(string $name, ?int $parentId, int $ownerId, ?int $excludeId = null): bool
    {
        $builder = $this->builder();
        $builder->where('name', $name);
        $builder->where('owner_id', $ownerId);

        if ($parentId === null || $parentId === 0) {
            $builder->where('parent_id IS NULL');
        } else {
            $builder->where('parent_id', $parentId);
        }

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    public function getBreadcrumbs(int $folderId): array
    {
        $path = [];
        $currentId = $folderId;

        while ($currentId !== null && $currentId !== 0) {
            $folder = $this->find($currentId);
            if (!$folder) {
                break;
            }
            array_unshift($path, ['id' => $folder['id'], 'name' => $folder['name']]);
            $currentId = $folder['parent_id'];
        }

        return $path;
    }

    public function getFolderWithOwner(int $folderId): ?array
    {
        // Pastikan Anda memilih owner_name dan owner_role di sini
        return $this->select('folders.*, users.name as owner_name, roles.name as owner_role')
            ->join('users', 'users.id = folders.owner_id')
            ->join('roles', 'roles.id = users.role_id', 'left') // Bergabung dengan tabel roles
            ->where('folders.id', $folderId)
            ->first();
    }

    public function getSubfolders(int $parentId, int $currentUserId, string $currentUserRole): array
    {
        $builder = $this->builder();

        $builder->where('parent_id', $parentId);

        // --- Perbaikan logika otorisasi ---
        $builder->groupStart(); // Mulai grup untuk semua kondisi OR

        // Kondisi 1: Folder adalah milik user yang login
        $builder->where('owner_id', $currentUserId);

        // Kondisi 2: Folder adalah 'public' (dibagikan ke semua orang)
        $builder->orWhere('shared_type', 'public');

        // Kondisi 3: Folder dibagikan ke peran tertentu (is_shared = 1 dan role ada di access_roles)
        $builder->orGroupStart(); // Mulai grup OR bersarang untuk kondisi shared + role
        $builder->where('is_shared', 1);
        $builder->where('access_roles IS NOT NULL');
        // Menggunakan JSON_CONTAINS untuk pencarian role yang lebih akurat
        $builder->where(new RawSql("JSON_CONTAINS(access_roles, '\"{$currentUserRole}\"')"));
        $builder->groupEnd(); // Akhiri grup OR bersarang

        $builder->groupEnd(); // Akhiri grup untuk semua kondisi OR

        $builder->orderBy('name', 'ASC');

        return $builder->get()->getResultArray();
    }

    public function getMyPersonalFolders(int $ownerId, ?int $parentId = null): array
    {
        $builder = $this->builder();
        $builder->where('owner_id', $ownerId)
            ->where('folder_type', 'personal')
            ->where('is_shared', 0);

        if ($parentId === null || $parentId === 0) {
            $builder->where('parent_id IS NULL');
        } else {
            $builder->where('parent_id', $parentId);
        }

        $builder->orderBy('name', 'ASC');

        return $builder->get()->getResultArray();
    }

    public function getSharedFoldersForUser(int $currentUserId, string $currentUserRole): array
    {
        $builder = $this->builder();

        $builder->select('folders.*, users.name as owner_name, roles.name as owner_role');
        $builder->join('users', 'users.id = folders.owner_id');
        $builder->join('roles', 'roles.id = users.role_id', 'left');

        // 🔥 FILTER UTAMA: Hanya ambil folder yang secara eksplisit bertipe 'shared'
        $builder->where('folders.folder_type', 'shared');

        // Opsional, tapi baik untuk validasi ganda
        $builder->where('folders.is_shared', 1);

        // Hanya folder top-level
        $builder->where('folders.parent_id IS NULL');

        // *KONDISI OTORISASI: Pastikan pengguna memiliki izin akses ke folder shared tersebut.*
        // Gunakan groupStart() untuk mengelompokkan kondisi-kondisi OR
        $builder->groupStart();

        // Kondisi 1: Pengguna adalah pemilik folder shared tersebut
        $builder->orWhere('folders.owner_id', $currentUserId);

        // Kondisi 2: Folder dibagikan sebagai 'public'
        $builder->orWhere('folders.shared_type', 'public');

        // Kondisi 3: Peran pengguna ada di dalam access_roles folder
        // Gunakan JSON_CONTAINS untuk mencari string peran di dalam kolom JSON
        $builder->orWhere(new RawSql("JSON_CONTAINS(folders.access_roles, '\"{$currentUserRole}\"')"));

        $builder->groupEnd();

        $builder->orderBy('folders.name', 'ASC');

        return $builder->get()->getResultArray();
    }


    /**
     * Mengambil jalur lengkap (path) dari sebuah folder sebagai string.
     * Contoh: "Root Folder / Subfolder 1 / Target Folder"
     *
     * @param int $folderId ID dari folder yang ingin dicari jalurnya.
     * @return string Jalur folder yang dipisahkan oleh ' / '.
     */
    public function getFolderPath(int $folderId): string
    {
        // Panggil method getBreadcrumbs yang sudah ada untuk mendapatkan array jalur
        $breadcrumbs = $this->getBreadcrumbs($folderId);

        // Ekstrak hanya nama-nama folder dari array breadcrumbs
        $pathNames = array_column($breadcrumbs, 'name');

        // Gabungkan nama-nama folder dengan ' / ' sebagai pemisah
        return implode(' / ', $pathNames);
    }

    public function getSubfoldersWithDetails(int $parentId, int $currentUserId = null, int $currentUserRoleId = null): array
    {
        $builder = $this->select('folders.*, users.name as owner_name, roles.name as owner_role_name')
            ->join('users', 'users.id = folders.owner_id', 'left')
            ->join('roles', 'roles.id = folders.owner_role', 'left'); // Bergabung dengan tabel roles menggunakan owner_role di tabel folders

        $builder->where('folders.parent_id', $parentId);
        $builder->groupStart();
        $builder->where('folders.owner_id', $currentUserId);
        $builder->orWhere('folders.owner_role', $currentUserRoleId); 
        if ($currentUserRoleId !== null) {
            $builder->orGroupStart()
                ->where('folders.is_shared', 1)
                ->where('folders.access_roles IS NOT NULL')
                ->where(new RawSql("JSON_CONTAINS(folders.access_roles, '\"{$currentUserRoleId}\"')")) // Menggunakan role ID
                ->groupEnd();
        }
        $builder->orWhere('folders.folder_type', 'public');

        $builder->groupEnd();

        $builder->orderBy('folders.name', 'ASC');

        return $builder->findAll();
    }

    public function getFoldersForRoleView(int $currentUserId, int $currentUserRoleId): array
    {
        $builder = $this->select('folders.*, users.name as owner_name, roles.name as owner_role_name')
            ->join('users', 'users.id = folders.owner_id', 'left')
            ->join('roles', 'roles.id = folders.owner_role', 'left');

        $builder->where('folders.parent_id', null); // Hanya folder di level root

        $builder->groupStart();

        // Kondisi 1: Folder yang dibuat langsung oleh user itu sendiri
        $builder->orWhere('folders.owner_id', $currentUserId);

        // Kondisi 2: Folder yang dibuat oleh pihak lain (seperti HRD) dan ditujukan untuk peran ini
        $builder->orWhere('folders.owner_role', $currentUserRoleId);

        $builder->orWhere('folders.folder_type', 'public');
        $builder->orGroupStart()
            ->where('folders.is_shared', 1)
            ->where(new RawSql("JSON_CONTAINS(folders.access_roles, '\"{$currentUserRoleId}\"')"))
            ->groupEnd();

        $builder->groupEnd();
        $builder->orderBy('folders.name', 'ASC');

        return $builder->findAll();
    }

    public function getPersonalRootFolders(int $ownerId): array
    {
        return $this->select('folders.*, users.name as owner_name, roles.name as owner_role')
            ->join('users', 'users.id = folders.owner_id', 'left')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->where('folders.owner_id', $ownerId)
            ->where('folders.parent_id', NULL)
            ->where('folders.folder_type', 'personal')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function getFoldersForRole(int $roleId): array
    {
        $builder = $this->select('folders.*, users.name as owner_name, roles.name as owner_role')
            ->join('users', 'users.id = folders.owner_id', 'left')
            ->join('roles', 'roles.id = users.role_id', 'left');

        $builder->where('folders.parent_id', NULL);
        $builder->groupStart();
        $builder->orWhere('users.role_id', $roleId);
        $builder->orGroupStart();
        $builder->where('folders.is_shared', 1);
        $builder->where('folders.access_roles IS NOT NULL');
        $builder->where(new RawSql("JSON_CONTAINS(folders.access_roles, '\"{$roleId}\"')"));
        $builder->groupEnd();

        $builder->groupEnd();

        $builder->orderBy('folders.name', 'ASC');

        return $builder->findAll();
    }

    public function getFoldersForHRDView(int $hrdUserId, int $hrdRoleId, int $targetRoleId): array
    {
        $builder = $this->select('folders.*, users.name as owner_name, roles.name as owner_role_name')
            ->join('users', 'users.id = folders.owner_id', 'left')
            ->join('roles', 'roles.id = folders.owner_role', 'left');

        $builder->where('folders.parent_id', null);

        // Ganti groupStart dengan where biasa
        $builder->where('folders.owner_role', $targetRoleId);

        $builder->orderBy('folders.name', 'ASC');

        return $builder->findAll();
    }

    // app/Models/FolderModel.php
    public function getFoldersForHRD()
    {
        $hrdRoleId = 2;
        $staffRoleId = 6;

        return $this->db->table('folders')
            ->groupStart()
            // Kondisi 1: Ambil folder shared yang dibuat oleh HRD itu sendiri
            ->where('owner_role', $hrdRoleId)
            ->where('is_shared', 1)
            ->groupEnd()
            ->orGroupStart()
            // Kondisi 2: Ambil folder personal Staff yang di-share ke HRD
            ->where('owner_role', $staffRoleId)
            ->where('is_shared', 1)
            ->like('access_roles', (string)$hrdRoleId)
            ->groupEnd()
            ->get()
            ->getResultArray();
    }
    // app/Models/FolderModel.php

    /**
     * Mengambil folder yang relevan berdasarkan peran (role).
     *
     * @param array $relevantRoleIds Array yang berisi role_id yang relevan (misal: [5, 2] untuk SPV dan HRD)
     * @return array
     */
    public function getFoldersForMultipleRoles(array $relevantRoleIds): array
    {
        $builder = $this->select('folders.*, users.name as owner_name, roles.name as owner_role')
            ->join('users', 'users.id = folders.owner_id', 'left')
            ->join('roles', 'roles.id = users.role_id', 'left');

        $builder->where('folders.parent_id', null);

        // Memulai group kondisi untuk OR
        $builder->groupStart();

        // Kondisi 1: Folder personal milik user yang sedang login
        $builder->where('folders.owner_id', session()->get('user_id'));

        // Kondisi 2: Folder shared yang dibagikan ke salah satu role di $relevantRoleIds
        $builder->orGroupStart();
        $builder->where('folders.is_shared', 1);

        // Tambahkan kondisi OR untuk setiap role ID dengan JSON_CONTAINS
        foreach ($relevantRoleIds as $roleId) {
            $builder->orWhere(new RawSql("JSON_CONTAINS(folders.access_roles, '\"{$roleId}\"')"));
        }
        $builder->groupEnd();

        // Kondisi 3: Folder yang dibagikan secara publik
        $builder->orWhere('folders.shared_type', 'public');

        $builder->groupEnd();

        $builder->orderBy('folders.name', 'ASC');

        return $builder->findAll();
    }

    public function getHRDViewForRole(int $hrdUserId, int $hrdRoleId, int $targetRoleId): array
    {
        $builder = $this->select('folders.*, users.name as owner_name, roles.name as owner_role_name')
            ->join('users', 'users.id = folders.owner_id', 'left')
            ->join('roles', 'roles.id = folders.owner_role', 'left');

        $builder->where('folders.parent_id', null);

        // 🔥 PERBAIKAN LOGIKA QUERY UNTUK MENAMPILKAN SEMUA FOLDER STAFF + FOLDER HRD YANG TERKAIT 🔥
        $builder->groupStart();

        // Kondisi 1: Folder yang dibuat oleh Staff (owner_role = 6)
        // dan dibagikan ke HRD (access_roles mengandung 2)
        $builder->where('folders.owner_role', $targetRoleId);
        $builder->where('folders.is_shared', 1);
        $builder->where(new RawSql("JSON_CONTAINS(folders.access_roles, '\"{$hrdRoleId}\"')"));

        // Kondisi 2: Folder yang dibuat oleh HRD (owner_role = 2)
        // dan dibagikan ke Staff (access_roles mengandung 6)
        $builder->orGroupStart()
            ->where('folders.owner_role', $hrdRoleId) // Owner role HRD
            ->where('folders.is_shared', 1)
            ->where(new RawSql("JSON_CONTAINS(folders.access_roles, '\"{$targetRoleId}\"')")) // Dibagikan ke Staff
            ->groupEnd();

        $builder->groupEnd();
        // 🔥 AKHIR PERBAIKAN 🔥

        $builder->orderBy('folders.name', 'ASC');

        return $builder->findAll();
    }

    public function getHRDViewFolders(int $hrdUserId, int $hrdRoleId, int $managerRoleId): array
    {
        $builder = $this->select('folders.*, users.name as owner_name, roles.name as owner_role_name')
            ->join('users', 'users.id = folders.owner_id', 'left')
            ->join('roles', 'roles.id = folders.owner_role', 'left')
            ->where('folders.parent_id', null);

        $builder->groupStart();

        // Kondisi 1: Ambil folder personal milik HRD yang sedang login
        $builder->where('folders.owner_id', $hrdUserId);

        // Kondisi 2: Ambil folder yang dibuat oleh user mana pun dan ditujukan untuk peran Manager
        $builder->orWhere('folders.owner_role', $managerRoleId);

        // Kondisi 3: Ambil folder public
        $builder->orWhere('folders.folder_type', 'public');

        // Kondisi 4: Ambil folder shared yang dibagikan ke peran HRD
        $builder->orGroupStart()
            ->where('folders.is_shared', 1)
            ->where(new RawSql("JSON_CONTAINS(folders.access_roles, '\"{$hrdRoleId}\"')"))
            ->groupEnd();

        $builder->groupEnd();
        $builder->orderBy('folders.name', 'ASC');

        return $builder->findAll();
    }

    public function getFoldersForUser(int $userId, int $userRoleId): array
    {
        $builder = $this->select('folders.*, users.name as owner_name, roles.name as owner_role_name')
            ->join('users', 'users.id = folders.owner_id', 'left')
            ->join('roles', 'roles.id = folders.owner_role', 'left');

        $builder->where('folders.parent_id', null);

        $builder->groupStart();
        $builder->where('folders.owner_id', $userId);
        $builder->orWhere('folders.owner_role', $userRoleId);
        $builder->orWhere('folders.folder_type', 'public');
        $builder->orGroupStart()
            ->where('folders.is_shared', 1)
            ->where(new RawSql("JSON_CONTAINS(folders.access_roles, '\"{$userRoleId}\"')"))
            ->groupEnd();
        $builder->groupEnd();

        $builder->orderBy('folders.name', 'ASC');

        return $builder->findAll();
    }

}