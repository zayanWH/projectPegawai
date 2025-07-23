<?php

namespace App\Models;

use CodeIgniter\Model;

class HrdDocumentModel extends Model
{
    protected $table = 'hrd_documents'; // Nama tabel di database
    protected $primaryKey = 'id'; // Nama kolom primary key
    protected $useAutoIncrement = true;
    protected $returnType     = 'array'; // Atau 'object'
    protected $useSoftDeletes = false; // Sesuaikan jika Anda menggunakan soft delete
    
    // Daftar kolom yang diizinkan untuk diisi (fillable fields)
    protected $allowedFields = ['file_id', 'category', 'description', 'created_at', 'updated_at']; 

    // Pengaturan timestamps, jika tabel Anda memiliki kolom created_at dan updated_at
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at'; // Hanya jika useSoftDeletes diatur ke true

    // Anda bisa menambahkan aturan validasi di sini jika diperlukan
    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;
}