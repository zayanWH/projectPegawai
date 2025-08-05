<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogsModel extends Model
{
    protected $table        = 'activity_logs';
    protected $primaryKey   = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    // 🔥 PENTING: Tambahkan 'target_name' ke dalam allowedFields
    protected $allowedFields = [
        'user_id',
        'action',
        'target_type',
        'target_id',
        'target_name',
        'details',
    ];

    protected $useTimestamps = true;
    protected $dateFormat     = 'datetime';
    protected $createdField   = 'timestamp';
    protected $updatedField   = '';
    protected $deletedField   = '';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Log activity - method untuk mencatat aktivitas
     * * @param int $userId
     * @param string $action
     * @param string $targetType
     * @param int $targetId
     * @param string|null $targetName
     * @param array|null $details
     * @return bool
     */
    public function logActivity($userId, $action, $targetType, $targetId, $targetName = null, $details = null)
    {
        $data = [
            'user_id'     => $userId,
            'action'      => $action,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'target_name' => $targetName, // Simpan nama di sini
            'details'     => json_encode($details),
            'timestamp'   => date('Y-m-d H:i:s')
        ];

        return $this->insert($data);
    }
}