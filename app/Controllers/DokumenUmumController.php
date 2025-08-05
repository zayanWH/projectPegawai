<?php

namespace App\Controllers;

use App\Models\HrdDocumentModel;

class DokumenUmumController extends BaseController
{
    protected $docModel;

    public function __construct()
    {
        $this->docModel = new HrdDocumentModel();
    }

    /**
     * Halaman utama Dokumen Umum
     */
    public function index($parentId = null)
    {
        $data = [
            'documents' => $this->docModel->getByParent($parentId),
            'parent_id' => $parentId
        ];

        return view('HRD/dokumenUmum', $data);
    }

    /**
     * Buat Folder Baru
     */
    public function createFolder()
    {
        $folderName = $this->request->getPost('folderName');
        $parentId = $this->request->getPost('parent_id');

        if (!$folderName) {
            return redirect()->back()->with('error', 'Nama folder tidak boleh kosong');
        }

        $this->docModel->insert([
            'parent_id' => $parentId ?: null,
            'name' => $folderName,
            'type' => 'folder'
        ]);

        return redirect()->back()->with('success', 'Folder berhasil dibuat');
    }

    /**
     * Upload File
     */
    public function uploadFile()
    {
        $parentId = $this->request->getPost('parent_id');
        $file = $this->request->getFile('file');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads', $newName);

            $this->docModel->insert([
                'parent_id' => $parentId ?: null,
                'name' => $file->getClientName(),
                'type' => 'file',
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'file_path' => 'uploads/' . $newName
            ]);

            return redirect()->back()->with('success', 'File berhasil diunggah');
        }

        return redirect()->back()->with('error', 'Gagal mengunggah file');
    }

    public function createFolderAjax()
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $folderName = $this->request->getPost('folderName');
        $parentId = $this->request->getPost('parent_id');
        $category = $this->request->getPost('category');

        if (!$folderName) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Nama folder tidak boleh kosong']);
        }

        $data = [
            'parent_id' => $parentId ?: null,
            'name' => $folderName,
            'type' => 'folder',
            'mime_type' => null,
            'size' => null,
            'file_path' => null,
            'file_id' => null,
            'category' => $category ?: null,
            'description' => null
        ];

        try {
            $this->docModel->insert($data);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Folder berhasil dibuat']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membuat folder: ' . $e->getMessage()]);
        }
    }

}


