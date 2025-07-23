<?php

// APPPATH\Models\UserModel.php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users'; // Nama tabel di database
    protected $primaryKey = 'id'; // Nama kolom primary key
    protected $useAutoIncrement = true;
    protected $returnType     = 'array'; // Atau 'object'
    protected $useSoftDeletes = false; // Sesuaikan jika Anda menggunakan soft delete
    
    // Daftar kolom yang diizinkan untuk diisi
    protected $allowedFields = ['name', 'email', 'password_hash', 'role_id', 'is_active', 'created_at']; 

    // Pengaturan timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at'; // Jika ada kolom updated_at di tabel users
    protected $deletedField  = 'deleted_at'; // Hanya jika useSoftDeletes diatur ke true

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;
}