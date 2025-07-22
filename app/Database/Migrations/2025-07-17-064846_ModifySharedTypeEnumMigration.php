<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifySharedTypeEnum extends Migration
{
    public function up()
    {
        // Modifikasi kolom shared_type
        $this->forge->modifyColumn('folders', [
            'shared_type' => [
                'type' => 'ENUM',
                'constraint' => ['full', 'read'], // Ubah ke nilai yang digunakan di frontend/controller
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        // Jika di-rollback, kembalikan ke enum sebelumnya
        $this->forge->modifyColumn('folders', [
            'shared_type' => [
                'type' => 'ENUM',
                'constraint' => ['read-only', 'read-write'], // Nilai asli
                'null' => true,
            ],
        ]);
    }
}