<?php namespace App\Models;

use CodeIgniter\Model;

class FileModel extends Model
{
    protected $table      = 'files'; // Nama tabel Anda
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array'; // Atau 'object'
    protected $useSoftDeletes = false; // Jika Anda tidak menggunakan soft deletes

    // Kolom-kolom yang boleh diisi
    protected $allowedFields = [
        'folder_id',
        'uploader_id',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
        'download_count',
        // Tambahkan kolom lain jika ada, contoh: 'description', 'shared_with', dll.
    ];

    // Timestamp
    protected $useTimestamps = true; // Set ke true jika tabel memiliki created_at dan updated_at
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at'; // Hanya jika useSoftDeletes = true

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;
}