<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['name', 'email', 'password_hash', 'role_id', 'is_active']; // Pastikan 'password_hash' sesuai dengan nama kolom di database Anda

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation - PERBAIKAN DI SINI
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[255]',
        'email' => 'required|valid_email|max_length[255]|is_unique[users.email,id,{id}]', // Menggunakan {id} placeholder
        'password' => 'permit_empty|min_length[8]', // Validasi ini untuk input 'password' dari form, yang nantinya perlu di-hash sebelum disimpan ke 'password_hash'
        'role_id' => 'required|integer',
        'is_active' => 'required|in_list[0,1]',
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Nama lengkap harus diisi.',
            'min_length' => 'Nama lengkap minimal 3 karakter.',
            'max_length' => 'Nama lengkap maksimal 255 karakter.'
        ],
        'email' => [
            'required' => 'Email harus diisi.',
            'valid_email' => 'Format email tidak valid.',
            'max_length' => 'Email maksimal 255 karakter.',
            'is_unique' => 'Email ini sudah terdaftar.'
        ],
        'password' => [
            'min_length' => 'Password minimal 8 karakter.'
        ],
        'role_id' => [
            'required' => 'Jabatan harus dipilih.',
            'integer' => 'Jabatan tidak valid.'
        ],
        'is_active' => [
            'required' => 'Status harus dipilih.',
            'in_list' => 'Status tidak valid.'
        ]
    ];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    public function getAllUsersWithRoleNames()
    {
        return $this->select('users.*, roles.name as role_name')
            ->join('roles', 'roles.id = users.role_id')
            ->findAll();
    }

    /**
     * Mengambil data user berdasarkan ID dengan nama role.
     * @param int $id ID user
     * @return array|null Data user atau null jika tidak ditemukan
     */
    public function getUserWithRoleNameById(int $id)
    {
        return $this->select('users.*, roles.name as role_name')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.id', $id)
            ->first();
    }

    /**
     * Custom method untuk update dengan validasi email yang dinamis
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateUserWithEmailCheck($id, $data)
    {
        // Ambil data user saat ini
        $currentUser = $this->find($id);

        if (!$currentUser) {
            return false;
        }

        // Jika email tidak berubah, skip validasi is_unique untuk email
        if (isset($data['email']) && $data['email'] === $currentUser['email']) {
            // Temporarily modify validation rules untuk skip email uniqueness
            $originalRules = $this->validationRules;
            $this->validationRules['email'] = 'required|valid_email|max_length[255]';

            $result = $this->update($id, $data);

            // Restore original rules
            $this->validationRules = $originalRules;

            return $result;
        }

        // Jika email berubah, gunakan validasi normal
        return $this->update($id, $data);
    }

    public function searchUsersByName($searchTerm)
    {
        return $this->select('users.*, roles.name as role_name')
            ->join('roles', 'roles.id = users.role_id')
            ->like('users.name', $searchTerm)
            ->orLike('users.email', $searchTerm)
            ->findAll();
    }

    public function getUserCountByRole()
    {
        return $this->select('roles.name as role_name, COUNT(users.id) as user_count')
            ->join('roles', 'roles.id = users.role_id')
            ->groupBy('roles.name')
            ->orderBy('roles.level', 'ASC')
            ->findAll();
    }

    /**
     * Mengambil daftar semua pengguna kecuali mereka yang memiliki role 'Admin'.
     *
     * @return array Daftar pengguna.
     */
    public function getUsersExcludingAdmin()
    {
        return $this->select('users.id, users.name, users.email, roles.name as role_name') // 'users.username' telah dihapus
            ->join('roles', 'roles.id = users.role_id')
            ->where('roles.name !=', 'Admin') // Pastikan 'Admin' adalah nama role yang benar untuk administrator Anda
            ->findAll();
    }

    /**
     * Mendapatkan daftar ID user berdasarkan nama peran (role).
     *
     * @param string $roleName Nama peran yang dicari (misal: 'Supervisor').
     * @return array Daftar ID pengguna.
     */
    // File: app/Models/UserModel.php

    // ... (kode model lainnya) ...

    public function getUsersByRole(string $roleName): array
    {
        $roleModel = new RoleModel();
        // PERBAIKAN: Mengganti 'role_name' dengan 'name' dan 'Manager' dengan 'Manajer'
        $role = $roleModel->where('name', 'Manajer')->first();

        if ($role === null) {
            return [];
        }

        $userIds = $this->select('id')->where('role_id', $role['id'])->findAll();

        $result = array_column($userIds, 'id');

        return $result;
    }
}