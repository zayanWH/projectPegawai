<?php namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\RawSql; // Pastikan ini ada jika menggunakan JSON_CONTAINS

class FolderModel extends Model
{
    protected $table = 'folders';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false; 

    protected $allowedFields = ['name', 'parent_id', 'folder_type', 'is_shared', 'shared_type', 'owner_id', 'access_roles'];

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
        if (isset($data['data']['name']) && isset($data['id'])) {
            $folder = $this->find($data['id'][0]);
            if ($folder && $this->isNameExists($data['data']['name'], $folder['parent_id'], $folder['owner_id'], $data['id'][0])) {
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

        // **PENTING: Pastikan Anda memilih owner_name dan owner_role di sini**
        $builder->select('folders.*, users.name as owner_name, roles.name as owner_role');
        $builder->join('users', 'users.id = folders.owner_id');
        $builder->join('roles', 'roles.id = users.role_id', 'left'); // Bergabung dengan tabel roles

        $builder->groupStart(); // Mulai grup untuk semua kondisi OR

            // Kondisi 1: Folder adalah milik user yang login
            $builder->orWhere('owner_id', $currentUserId);

            // Kondisi 2: Folder adalah 'public' (dibagikan ke semua orang)
            $builder->orWhere('shared_type', 'public');

            // Kondisi 3: Folder dibagikan ke peran tertentu (is_shared = 1 dan role ada di access_roles)
            $builder->orGroupStart();
                $builder->where('is_shared', 1);
                $builder->where('access_roles IS NOT NULL');
                $builder->where(new RawSql("JSON_CONTAINS(access_roles, '\"{$currentUserRole}\"')"));
            $builder->groupEnd();

        $builder->groupEnd();

        // Untuk view dokumenBersama, Anda mungkin ingin melihat folder root yang dibagikan.
        // Jika Anda ingin semua folder yang dibagikan (termasuk subfolder), hapus baris ini.
        $builder->where('parent_id IS NULL');

        $builder->orderBy('name', 'ASC');

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

     /**
     * Mengambil folder yang diunggah oleh pengguna dengan peran 'Manajer'.
     *
     * @param int|null $parentId ID dari folder induk. Null untuk folder root.
     * @return array
     */
    public function getManagerFolders(?int $parentId): array
    {
        // Mengambil ID dari semua pengguna dengan peran 'Manajer'
        $userModel = new UserModel();
        // PERBAIKAN: Mengganti 'Manager' menjadi 'Manajer'
        $managerUserIds = $userModel->getUsersByRole('Manajer');

        $builder = $this->builder();
        
        // Memfilter folder berdasarkan parent_id
        if ($parentId === null) {
            $builder->where('parent_id IS NULL');
        } else {
            $builder->where('parent_id', $parentId);
        }

        // PERBAIKAN: Pengecekan apakah ada user ID yang ditemukan
        if (!empty($managerUserIds)) {
            $builder->whereIn('owner_id', $managerUserIds);
        } else {
            // Jika tidak ada user manager, kembalikan array kosong agar tidak terjadi error SQL
            return [];
        }
        
        // PERBAIKAN: Mengganti 'folder_name' dengan 'name'
        $builder->orderBy('name', 'ASC');

        return $builder->get()->getResultArray();
    }
}

