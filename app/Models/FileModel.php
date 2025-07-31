<?php

namespace App\Models;

use CodeIgniter\Model;

class FileModel extends Model
{
    protected $table        = 'files';
    protected $primaryKey   = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    // Kolom-kolom yang boleh diisi
    protected $allowedFields = [
        'folder_id',
        'uploader_id',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
        'download_count',
        'server_file_name',
        'folder_type'
    ];

    // Timestamp
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Mendapatkan daftar file berdasarkan ID folder.
     * Digunakan untuk menampilkan isi folder.
     * @param int $folderId ID folder
     * @return array Daftar file dalam folder tersebut
     */
    public function getFilesByFolder(int $folderId): array
    {
        return $this->where('folder_id', $folderId)
                    ->orderBy('file_name', 'ASC') // Mengurutkan berdasarkan nama file
                    ->findAll();
    }

    /**
     * Mendapatkan total ukuran semua file dalam bytes
     * @return int Total ukuran file dalam bytes
     */
    public function getTotalFileSize()
    {
        $result = $this->selectSum('file_size')->get()->getRow();
        return $result->file_size ?? 0;
    }

    /**
     * Mendapatkan storage usage berdasarkan uploader dengan informasi role
     * @return array Data storage usage per user dengan role
     */
    public function getStorageUsageByUser()
    {
        return $this->select('
                users.id as user_id,
                users.name as user_name,
                roles.name as role_name,
                COUNT(files.id) as total_files,
                COALESCE(SUM(files.file_size), 0) as total_size_bytes
            ')
            ->join('users', 'users.id = files.uploader_id', 'left')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->where('users.is_active', 1)
            ->groupBy('users.id, users.name, roles.name')
            ->orderBy('total_size_bytes', 'DESC')
            ->findAll();
    }

    /**
     * Mendapatkan top N users dengan storage terbesar
     * @param int $limit Jumlah user yang akan diambil
     * @return array Top users dengan storage terbesar
     */
    public function getTopStorageUsers($limit = 5)
    {
        return $this->select('
                users.id as user_id,
                users.name as user_name,
                roles.name as role_name,
                COUNT(files.id) as total_files,
                COALESCE(SUM(files.file_size), 0) as total_size_bytes
            ')
            ->join('users', 'users.id = files.uploader_id', 'left')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->where('users.is_active', 1)
            ->groupBy('users.id, users.name, roles.name')
            ->having('total_size_bytes >', 0)
            ->orderBy('total_size_bytes', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Mendapatkan file-file terbesar
     * @param int $limit Jumlah file yang akan diambil
     * @return array File-file dengan ukuran terbesar
     */
    public function getLargestFiles($limit = 10)
    {
        return $this->select('
                files.*,
                users.name as uploader_name,
                roles.name as role_name
            ')
            ->join('users', 'users.id = files.uploader_id', 'left')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->orderBy('files.file_size', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Mendapatkan storage usage berdasarkan role/jabatan
     * @return array Data storage usage per role
     */
    public function getStorageUsageByRole()
    {
        return $this->select('
                roles.id as role_id,
                roles.name as role_name,
                roles.level as role_level,
                COUNT(DISTINCT users.id) as user_count,
                COUNT(files.id) as total_files,
                COALESCE(SUM(files.file_size), 0) as total_size_bytes
            ')
            ->join('users', 'users.id = files.uploader_id', 'left')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->where('users.is_active', 1)
            ->groupBy('roles.id, roles.name, roles.level')
            ->orderBy('roles.level', 'ASC')
            ->findAll();
    }

    /**
     * Mendapatkan total file count dan size untuk dashboard
     * @return array Summary data
     */
    public function getStorageSummary()
    {
        $result = $this->select('
                COUNT(id) as total_files,
                COALESCE(SUM(file_size), 0) as total_size_bytes
            ')
            ->get()
            ->getRowArray();

        return [
            'total_files' => (int)$result['total_files'],
            'total_size_bytes' => (int)$result['total_size_bytes'],
            'total_size_mb' => round($result['total_size_bytes'] / (1024 * 1024), 2),
            'total_size_gb' => round($result['total_size_bytes'] / (1024 * 1024 * 1024), 2)
        ];
    }

    /**
     * Mendapatkan file berdasarkan type untuk analisis
     * @return array Data file berdasarkan type
     */
    public function getFilesByType()
    {
        return $this->select('
                file_type,
                COUNT(id) as file_count,
                COALESCE(SUM(file_size), 0) as total_size_bytes
            ')
            ->groupBy('file_type')
            ->orderBy('total_size_bytes', 'DESC')
            ->findAll();
    }

    /**
     * Mendapatkan statistik upload per bulan
     * @param int $months Jumlah bulan terakhir
     * @return array Data upload per bulan
     */
    public function getUploadStatsByMonth($months = 12)
    {
        return $this->select("
                DATE_FORMAT(created_at, '%Y-%m') as month_year,
                COUNT(id) as file_count,
                COALESCE(SUM(file_size), 0) as total_size_bytes
            ")
            ->where('created_at >=', date('Y-m-d', strtotime("-{$months} months")))
            ->groupBy("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderBy('month_year', 'ASC')
            ->findAll();
    }

    // Di app/Models/FileModel.php
public function getSharedFiles(?int $folderId, int $currentUserId, string $currentUserRole): array
{
    $builder = $this->builder();

    if ($folderId === null) {
        $builder->where('folder_id IS NULL'); // File di root shared folder
    } else {
        $builder->where('folder_id', $folderId);
    }

    $builder->where('is_shared', 1); // Hanya file yang ditandai sebagai shared

    $builder->groupStart();
        $builder->orWhere('owner_id', $currentUserId); // File yang dimiliki user
        $builder->orWhere('shared_type', 'public'); // File yang dibagikan secara publik
        $builder->orGroupStart(); // Atau file yang dibagikan ke peran user
            $builder->where('access_roles IS NOT NULL');
            $builder->where(new \CodeIgniter\Database\RawSql("JSON_CONTAINS(access_roles, '\"{$currentUserRole}\"')"));
        $builder->groupEnd();
    $builder->groupEnd();

    $builder->orderBy('file_name', 'ASC');

    return $builder->get()->getResultArray();
}

    
}