<?php namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController; // Gunakan ini jika Anda ingin API response
use App\Models\FileModel; // Pastikan Anda telah mendefinisikan FileModel
use App\Models\FolderModel; // Jika Anda perlu memvalidasi folder_id

class Upload extends ResourceController
{
    // Menggunakan format JSON response untuk AJAX
    protected $format    = 'json';

    public function doUpload()
    {
        // Pastikan ini adalah POST request
        if ($this->request->getMethod() !== 'post') {
            return $this->failUnauthorized('Metode request tidak diizinkan.', 405);
        }

        // Ambil file yang diunggah. 'uploadedFile' harus sesuai dengan nama 'formData.append' di JS
        $file = $this->request->getFile('uploadedFile');

        // Ambil data lain dari FormData yang dikirim oleh JavaScript
        $folderId = $this->request->getPost('folder_id');
        $uploaderId = $this->request->getPost('uploader_id');

        // --- Validasi Input ---
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            // Tangani jika tidak ada file, file tidak valid, atau sudah dipindahkan
            return $this->failValidationErrors('Tidak ada berkas yang diunggah atau berkas tidak valid.');
        }

        // Lakukan validasi tambahan untuk file (ukuran, tipe, dll.)
        $validationRule = [
            'uploadedFile' => [
                'rules' => 'uploaded[uploadedFile]|max_size[uploadedFile,2048]|ext_in[uploadedFile,pdf,doc,docx,xls,xlsx,jpg,jpeg,png]',
                'errors' => [
                    'uploaded'  => 'Anda harus memilih berkas untuk diunggah.',
                    'max_size'  => 'Ukuran berkas terlalu besar (maks 2MB).',
                    'ext_in'    => 'Tipe berkas tidak diizinkan. Hanya PDF, Word, Excel, Gambar yang diizinkan.'
                ],
            ],
        ];

        if (! $this->validate($validationRule)) {
            // Jika validasi gagal, kirim pesan kesalahan validasi
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Validasi folder_id dan uploader_id (Opsional tapi sangat disarankan)
        $folderModel = new FolderModel();
        if (!$folderModel->find($folderId)) {
            return $this->failNotFound('ID Folder tidak ditemukan.');
        }
        // Anda juga bisa memvalidasi uploaderId dengan model User/Staff

        // --- Proses Unggah Berkas ---
        $originalFileName = $file->getName();
        $fileExtension    = $file->getExtension(); // Mengambil ekstensi asli
        $fileSize         = $file->getSize(); // Ukuran dalam bytes
        $fileType         = $file->getMimeType();

        // Buat nama unik untuk berkas agar tidak terjadi duplikasi saat disimpan di server
        $newFileName = $file->getRandomName();

        // Tentukan folder tujuan penyimpanan di server.
        // Pastikan folder 'writable/uploads' ini ada dan memiliki izin tulis (chmod 755 atau 777)
        $uploadPath = WRITEPATH . 'uploads/'; // Direktori 'writable' CodeIgniter

        // Pindahkan berkas dari direktori temporer ke direktori tujuan
        if (! $file->move($uploadPath, $newFileName)) {
            // Jika gagal memindahkan berkas
            return $this->failServerError('Gagal memindahkan berkas ke server.');
        }

        // --- Simpan Metadata ke Database ---
        $fileModel = new FileModel();

        $dataToSave = [
            'folder_id'    => $folderId,
            'uploader_id'  => $uploaderId,
            'file_name'    => $originalFileName, // Nama asli berkas
            'file_path'    => 'uploads/' . $newFileName, // Path relatif dari direktori writable
            'file_size'    => $fileSize,
            'file_type'    => $fileType,
            'download_count' => 0, // Inisialisasi
            'created_at'   => date('Y-m-d H:i:s'), // Atur created_at secara manual jika tidak ada di model
            'updated_at'   => date('Y-m-d H:i:s'), // Atur updated_at secara manual jika tidak ada di model
        ];

        try {
            $fileModel->insert($dataToSave);
            $newFileId = $fileModel->insertID(); // Ambil ID berkas yang baru disimpan

            // Berhasil diunggah dan disimpan
            return $this->respondCreated([
                'success'  => true,
                'message'  => 'Berkas berhasil diunggah dan disimpan!',
                'fileData' => array_merge($dataToSave, ['id' => $newFileId]) // Sertakan ID baru
            ]);

        } catch (\Exception $e) {
            // Jika gagal menyimpan ke database, hapus file yang sudah diunggah di server
            if (file_exists($uploadPath . $newFileName)) {
                unlink($uploadPath . $newFileName);
            }
            return $this->failServerError('Gagal menyimpan data berkas ke database: ' . $e->getMessage());
        }
    }
}