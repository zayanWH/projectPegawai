<?php

namespace App\Models;

use CodeIgniter\Model;

class LogAksesModel extends Model
{
    protected $table          = 'log_akses_file';
    protected $primaryKey     = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields    = true;
    
    // Kolom-kolom yang boleh diisi, sesuaikan dengan database 'appcloud'
    protected $allowedFields = [
        'user_id',
        'role',          // Ada di database 'appcloud'
        'file_id',
        'file_name',     // Ada di database 'appcloud'
        'aksi',          // Ada di database 'appcloud'
        // 'timestamp' tidak perlu di allowedFields karena diisi otomatis oleh createdField
    ];

    // Timestamp
    protected $useTimestamps = true;
    protected $createdField  = 'timestamp'; // <--- PASTIKAN INI 'timestamp'
    protected $updatedField  = ''; // Tidak digunakan
    protected $deletedField  = ''; // Tidak digunakan
}