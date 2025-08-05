<?php

namespace App\Models;

use CodeIgniter\Model;

class HrdDocumentModel extends Model
{
    protected $table = 'hrd_documents';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    // Field baru + field lama
    protected $allowedFields = [
        'parent_id',    // Folder induk
        'name',         // Nama file/folder
        'type',         // Jenis: folder/file
        'mime_type',    // Tipe file
        'size',         // Ukuran file
        'file_path',    // Path penyimpanan
        'file_id',      // Field lama
        'category',
        'description',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    /**
     * Ambil dokumen/folder berdasarkan parent_id
     */
    public function getByParent($parentId = null)
    {
        return $this->where('parent_id', $parentId)
                    ->orderBy('type', 'ASC') // Folder dulu
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }
}
