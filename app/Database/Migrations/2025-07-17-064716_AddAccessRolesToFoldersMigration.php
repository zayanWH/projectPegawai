<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAccessRolesToFolders extends Migration
{
    public function up()
    {
        // Tambahkan kolom 'access_roles' ke tabel 'folders'
        $this->forge->addColumn('folders', [
            'access_roles' => [
                'type'       => 'TEXT', // Gunakan TEXT karena ini akan menyimpan JSON string yang bisa panjang
                'null'       => true,   // Biarkan null karena tidak semua folder adalah shared folder atau memiliki roles
                'after'      => 'owner_id', // Opsional: Untuk posisi kolom
            ],
        ]);
    }

    public function down()
    {
        // Hapus kolom 'access_roles' jika migrasi di-rollback
        $this->forge->dropColumn('folders', 'access_roles');
    }
}