<?php namespace App\Models;

use CodeIgniter\Model;

class FolderModel extends Model
{
    protected $table        = 'folders'; // Nama tabel Anda
    protected $primaryKey   = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false; // Sesuaikan jika Anda menggunakan soft delete, pastikan ada kolom `deleted_at` di DB jika true

    // Kolom-kolom yang diizinkan untuk diisi saat insert/update
    // PASTIKAN 'access_roles' ADA DI SINI
    protected $allowedFields = ['name', 'parent_id', 'folder_type', 'is_shared', 'shared_type', 'owner_id', 'access_roles'];

    protected bool $allowEmptyInserts = false;

    // Pengaturan Timestamps
    protected $useTimestamps = true; // Aktifkan jika tabel Anda memiliki kolom created_at dan updated_at
    protected $dateFormat    = 'datetime'; // Format tanggal (sesuaikan jika di DB berbeda, misal 'date' atau 'int')
    protected $createdField  = 'created_at'; // Nama kolom untuk timestamp pembuatan
    protected $updatedField  = 'updated_at'; // Nama kolom untuk timestamp pembaruan
    protected $deletedField  = 'deleted_at'; // Nama kolom untuk timestamp soft delete (hanya jika useSoftDeletes true)

    // Pengaturan Validasi (dapat ditambahkan sesuai kebutuhan)
    protected $validationRules = [
        'name'        => 'required|min_length[1]|max_length[255]',
        'folder_type' => 'required|in_list[personal,shared]',
        'is_shared'   => 'required|in_list[0,1]',
        'owner_id'    => 'required|integer',
        'parent_id'   => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Nama folder wajib diisi.',
            'min_length' => 'Nama folder minimal 1 karakter.',
            'max_length' => 'Nama folder maksimal 255 karakter.',
        ],
        'folder_type' => [
            'required' => 'Jenis folder wajib diisi.',
            'in_list'  => 'Jenis folder tidak valid.',
        ],
        'is_shared' => [
            'required' => 'Status share folder wajib diisi.',
            'in_list'  => 'Status share folder tidak valid.',
        ],
        'owner_id' => [
            'required' => 'Owner ID wajib diisi.',
            'integer'  => 'Owner ID harus berupa angka.',
        ],
        'parent_id' => [
            'integer' => 'Parent ID harus berupa angka.',
        ],
    ];
    protected $skipValidation     = false;
    protected $cleanValidationRules = true;

    // Callbacks (dapat ditambahkan sesuai kebutuhan, misal untuk hash password sebelum insert)
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function canDeleteFolder($folderId)
{
    $errors = [];
    
    // Cek apakah folder memiliki subfolder
    $subfolderCount = $this->where('parent_id', $folderId)->countAllResults();
    if ($subfolderCount > 0) {
        $errors[] = 'Folder memiliki ' . $subfolderCount . ' subfolder.';
    }
    
    // Cek apakah folder memiliki file (jika ada tabel files)
    /*
    $fileModel = model('FileModel');
    $fileCount = $fileModel->where('folder_id', $folderId)->countAllResults();
    if ($fileCount > 0) {
        $errors[] = 'Folder memiliki ' . $fileCount . ' file.';
    }
    */
    
    return [
        'can_delete' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Hapus folder beserta semua isinya (use with caution)
 * @param int $folderId
 * @return bool
 */
public function deleteFolder($folderId)
{
    $db = \Config\Database::connect();
    $db->transStart();
    
    try {
        // Hapus semua subfolder secara rekursif
        $subfolders = $this->where('parent_id', $folderId)->findAll();
        foreach ($subfolders as $subfolder) {
            $this->deleteFolder($subfolder['id']);
        }
        
        // Hapus semua file dalam folder (jika ada tabel files)
        /*
        $fileModel = model('FileModel');
        $fileModel->where('folder_id', $folderId)->delete();
        */
        
        // Hapus folder itu sendiri
        $this->delete($folderId);
        
        $db->transCommit();
        return true;
        
    } catch (\Exception $e) {
        $db->transRollback();
        throw $e;
    }
}

/**
 * Soft delete folder (jika menggunakan soft delete)
 * @param int $folderId
 * @return bool
 */
public function softDeleteFolder($folderId)
{
    $data = [
        'deleted_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    return $this->update($folderId, $data);
}

public function validateRename($data)
    {
        $rules = [
            'name' => 'required|min_length[1]|max_length[255]|regex_match[/^[a-zA-Z0-9\s\-_\.]+$/]'
        ];

        $messages = [
            'name' => [
                'required' => 'Nama folder harus diisi.',
                'min_length' => 'Nama folder minimal 1 karakter.',
                'max_length' => 'Nama folder maksimal 255 karakter.',
                'regex_match' => 'Nama folder hanya boleh mengandung huruf, angka, spasi, tanda hubung, underscore, dan titik.'
            ]
        ];

        $validation = \Config\Services::validation();
        $validation->setRules($rules, $messages);

        return $validation->run($data);
    }

    /**
     * Cek apakah nama folder sudah digunakan dalam scope yang sama
     * @param string $name
     * @param int|null $parentId
     * @param int $ownerId
     * @param int|null $excludeId
     * @return bool
     */
    public function isNameExists($name, $parentId, $ownerId, $excludeId = null)
    {
        $builder = $this->builder();
        $builder->where('name', $name);
        $builder->where('owner_id', $ownerId);
        
        if ($parentId === null) {
            $builder->where('parent_id IS NULL');
        } else {
            $builder->where('parent_id', $parentId);
        }
        
        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->countAllResults() > 0;
    }

    /**
     * Mendapatkan folder berdasarkan owner dan parent
     * @param int $ownerId
     * @param int|null $parentId
     * @return array
     */
    public function getFoldersByOwnerAndParent($ownerId, $parentId = null)
    {
        $builder = $this->builder();
        $builder->where('owner_id', $ownerId);
        
        if ($parentId === null) {
            $builder->where('parent_id IS NULL');
        } else {
            $builder->where('parent_id', $parentId);
        }
        
        return $builder->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Mendapatkan folder dengan informasi owner
     * @param int|null $folderId
     * @return array|null
     */
    public function getFolderWithOwner($folderId = null)
    {
        $builder = $this->db->table($this->table . ' f');
        $builder->join('users u', 'f.owner_id = u.id', 'left');
        $builder->select('f.*, u.name as owner_name, u.username as owner_username, u.email as owner_email');
        
        if ($folderId !== null) {
            $builder->where('f.id', $folderId);
            return $builder->get()->getRowArray();
        }
        
        return $builder->get()->getResultArray();
    }

    /**
     * Update folder dengan validasi
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateFolder($id, $data)
    {
        // Validasi data sebelum update
        if (isset($data['name'])) {
            $folder = $this->find($id);
            if (!$folder) {
                return false;
            }

            // Cek duplikasi nama
            if ($this->isNameExists($data['name'], $folder['parent_id'], $folder['owner_id'], $id)) {
                return false;
            }
        }

        // Update timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->update($id, $data);
    }

    /**
     * Mendapatkan path lengkap folder
     * @param int $folderId
     * @return string
     */
    public function getFolderPath($folderId)
    {
        $path = [];
        $currentId = $folderId;
        
        while ($currentId !== null) {
            $folder = $this->find($currentId);
            if (!$folder) {
                break;
            }
            
            array_unshift($path, $folder['name']);
            $currentId = $folder['parent_id'];
        }
        
        return implode('/', $path);
    }

    /**
     * Mendapatkan semua subfolder dari sebuah folder
     * @param int $parentId
     * @return array
     */
    public function getSubfolders($parentId)
    {
        return $this->where('parent_id', $parentId)
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }

    /**
     * Mendapatkan breadcrumbs (array folder dari root ke folder saat ini)
     * @param int $folderId
     * @return array
     */
    public function getBreadcrumbs($folderId)
    {
        $breadcrumbs = [];
        $currentId = $folderId;
        while ($currentId !== null) {
            $folder = $this->find($currentId);
            if (!$folder) {
                break;
            }
            array_unshift($breadcrumbs, $folder);
            $currentId = $folder['parent_id'];
        }
        return $breadcrumbs;
    }

    /**
     * Callback sebelum insert
     * @param array $data
     * @return array
     */
    protected function beforeInsert(array $data)
    {
        // Set default values jika tidak ada
        if (!isset($data['data']['is_shared'])) {
            $data['data']['is_shared'] = 0;
        }
        
        if (!isset($data['data']['folder_type'])) {
            $data['data']['folder_type'] = 'personal';
        }
        
        return $data;
    }

    /**
     * Callback sebelum update
     * @param array $data
     * @return array
     */
    protected function beforeUpdate(array $data)
    {
        // Validasi tambahan saat update
        if (isset($data['data']['name']) && isset($data['id'])) {
            $folder = $this->find($data['id'][0]);
            if ($folder && $this->isNameExists($data['data']['name'], $folder['parent_id'], $folder['owner_id'], $data['id'][0])) {
                throw new \Exception('Nama folder sudah digunakan di lokasi yang sama.');
            }
        }
        
        return $data;
    }
}