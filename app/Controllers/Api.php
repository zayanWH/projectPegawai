<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait; // Ini untuk mempermudah respon JSON
use App\Models\FolderModel; // Import FolderModel Anda

class Api extends BaseController
{
    use ResponseTrait; // Gunakan trait untuk helper respon API

    public function createFolder()
    {
        // Pastikan hanya menerima permintaan AJAX POST
        if (!$this->request->isAJAX() || $this->request->getMethod() !== 'post') {
            return $this->failUnauthorized('Akses tidak diizinkan.');
        }

        // Dapatkan data JSON dari body permintaan
        $input = $this->request->getJSON(true);

        $folderName = $input['folder_name'] ?? null;
        $parentId = $input['parent_id'] ?? null; // Bisa null jika ini folder root
        $folderType = $input['folder_type'] ?? 'personal'; // Default 'personal'
        $ownerId = session()->get('user_id'); // Dapatkan ID pengguna dari sesi

        // Validasi input
        if (empty($folderName)) {
            return $this->fail('Nama folder tidak boleh kosong.', 400);
        }

        if (empty($ownerId)) {
            return $this->failUnauthorized('Pengguna tidak terautentikasi.');
        }

        $folderModel = new FolderModel();

        try {
            $data = [
                'name'        => $folderName,
                // Pastikan 'null' string dari JS diubah menjadi null PHP
                'parent_id'   => ($parentId === 'null' || $parentId === null) ? null : $parentId,
                'folder_type' => $folderType,
                'owner_id'    => $ownerId,
                'is_shared'   => 0, // Default tidak dibagikan
                'shared_type' => null, // Default null
            ];

            // Cek apakah ada folder dengan nama yang sama di parent_id yang sama untuk user ini
            $existingFolder = $folderModel->where('name', $folderName)
                                          ->where('parent_id', $data['parent_id'])
                                          ->where('owner_id', $ownerId)
                                          ->first();
            if ($existingFolder) {
                return $this->fail('Folder dengan nama tersebut sudah ada di lokasi ini.', 409); // Konflik
            }


            if ($folderModel->insert($data)) {
                // Mendapatkan ID folder yang baru dibuat
                $newFolderId = $folderModel->insertID();
                return $this->respondCreated([
                    'status' => 'success',
                    'message' => 'Sub-folder berhasil dibuat.',
                    'folder_id' => $newFolderId,
                    'folder_name' => $folderName,
                    'parent_id' => $data['parent_id']
                ]);
            } else {
                // Jika ada error validasi dari model atau gagal disimpan
                return $this->fail('Gagal membuat sub-folder: ' . json_encode($folderModel->errors()), 500);
            }
        } catch (\Exception $e) {
            // Tangani error lain yang mungkin terjadi
            return $this->failServerError('Terjadi kesalahan server: ' . $e->getMessage());
        }
    }
}