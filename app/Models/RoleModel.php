<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'level', 'max_upload_size_mb'];

    protected $useTimestamps = false;
    protected $createdField  = null;
    protected $updatedField  = null;
    protected $deletedField  = null;

    // Validasi untuk INSERT
    protected $validationRules = [
        'name'               => 'required|min_length[3]|max_length[255]|is_unique[roles.name]',
        'level'              => 'required|integer|greater_than_equal_to[1]',
        'max_upload_size_mb' => 'required|integer|greater_than_equal_to[0]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Nama jabatan harus diisi.',
            'min_length' => 'Nama jabatan minimal 3 karakter.',
            'max_length' => 'Nama jabatan maksimal 255 karakter.',
            'is_unique'  => 'Nama jabatan ini sudah terdaftar.'
        ],
        'level' => [
            'required'              => 'Level harus diisi.',
            'integer'               => 'Level harus berupa angka bulat.',
            'greater_than_equal_to' => 'Level minimal 1.'
        ],
        'max_upload_size_mb' => [
            'required'              => 'Max Storage harus diisi.',
            'integer'               => 'Max Storage harus berupa angka bulat.',
            'greater_than_equal_to' => 'Max Storage minimal 0 MB.'
        ]
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Custom method untuk update dengan validasi khusus
     */
    public function updateRole($id, $data)
    {
        // Validasi rules khusus untuk update
        $updateRules = [
            'name'               => "required|min_length[3]|max_length[255]|is_unique[roles.name,id,{$id}]",
            'level'              => 'required|integer|greater_than_equal_to[1]',
            'max_upload_size_mb' => 'required|integer|greater_than_equal_to[0]',
        ];

        $updateMessages = [
            'name' => [
                'required'   => 'Nama jabatan harus diisi.',
                'min_length' => 'Nama jabatan minimal 3 karakter.',
                'max_length' => 'Nama jabatan maksimal 255 karakter.',
                'is_unique'  => 'Nama jabatan ini sudah terdaftar.'
            ],
            'level' => [
                'required'              => 'Level harus diisi.',
                'integer'               => 'Level harus berupa angka bulat.',
                'greater_than_equal_to' => 'Level minimal 1.'
            ],
            'max_upload_size_mb' => [
                'required'              => 'Max Storage harus diisi.',
                'integer'               => 'Max Storage harus berupa angka bulat.',
                'greater_than_equal_to' => 'Max Storage minimal 0 MB.'
            ]
        ];

        // Set validasi rules dan messages untuk update
        $this->setValidationRules($updateRules);
        $this->setValidationMessages($updateMessages);

        // Lakukan update
        return $this->update($id, $data);
    }

    /**
     * Method untuk check apakah nama jabatan sudah ada (kecuali ID tertentu)
     */
    public function isNameExists($name, $excludeId = null)
    {
        $builder = $this->where('name', $name);
        
        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->countAllResults() > 0;
    }

    /**
     * Mengambil nama role berdasarkan ID.
     *
     * @param int $roleId ID role.
     * @return string|null Nama role atau null jika tidak ditemukan.
     */
    public function getRoleNameById(int $roleId): ?string
    {
        $role = $this->find($roleId);
        return $role['name'] ?? null;
    }
}