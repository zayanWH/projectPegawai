<?php

namespace App\Controllers;

use App\Models\FolderModel;
use App\Models\FileModel;
use App\Models\HrdDocumentModel;
use App\Models\UserModel;
use CodeIgniter\Files\File;
use CodeIgniter\Exceptions\PageNotFoundException;

class DokumenControllerHRD extends BaseController
{
    protected $folderModel;
    protected $fileModel;
    protected $hrdDocumentModel;
    protected $userModel;
    protected $helpers = ['form', 'url', 'filesystem'];

    public function __construct()
    {
        $this->folderModel = new FolderModel();
        $this->fileModel = new FileModel();
        $this->hrdDocumentModel = new HrdDocumentModel();
        $this->userModel = new UserModel();
        helper('session');
    }

    public function serveFile($fileId)
    {
        $session = session();
        $userRole = $session->get('role');
        $userId = $session->get('user_id');

        $file = $this->fileModel->find($fileId);

        if (!$file) {
            throw PageNotFoundException::forPageNotFound('File tidak ditemukan.');
        }

        // Logika Otorisasi:
        if ($userRole === 'hrd') {
            // HRD memiliki akses penuh, lanjutkan
        } elseif ($file['uploader_id'] == $userId) {
            // Pengguna adalah pemilik file, lanjutkan
        } else {
            throw new \CodeIgniter\Exceptions\AccessDeniedException('Anda tidak memiliki izin untuk melihat file ini.');
        }

        if (!isset($file['server_file_name'])) {
            log_message('error', 'File ID ' . $fileId . ' ditemukan, tetapi kunci "server_file_name" tidak ada.');
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Informasi file tidak lengkap.');
        }

        $folder = null;
        // Perbaikan: Jalur dasar untuk mencari file adalah WRITEPATH . 'uploads/'
        $filePathBase = WRITEPATH . 'uploads/';

        if (isset($file['folder_id']) && $file['folder_id']) {
            $folder = $this->folderModel->find($file['folder_id']);
            if ($folder && isset($folder['full_path_physical'])) {
                $filePathBase = $folder['full_path_physical'] . DIRECTORY_SEPARATOR;
            } else {
                log_message('warning', 'File ' . $fileId . ' memiliki folder_id tidak valid atau full_path_physical hilang untuk folder ' . $file['folder_id']);
            }
        }
        $filePath = $filePathBase . $file['server_file_name'];

        if (!file_exists($filePath)) {
            log_message('error', 'File fisik tidak ditemukan di: ' . $filePath . ' untuk File ID: ' . $fileId);
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File fisik tidak ditemukan.');
        }

        $mimeType = $file['file_type'];

        if (strpos($mimeType, 'image/') === 0 || $mimeType === 'application/pdf') {
            header('Content-Type: ' . $mimeType);
            readfile($filePath);
            exit;
        } else {
            return $this->response->download($filePath, null)->setFileName($file['file_name']);
        }
    }

    public function index()
    {
        // 1. Mendapatkan total jumlah folder
        $totalFolders = $this->folderModel->countAllResults();

        // 2. Mendapatkan total jumlah file HRD
        $totalHrdFiles = $this->hrdDocumentModel->countAllResults();

        // 3. Mendapatkan total jumlah user
        $totalUsers = $this->userModel->countAllResults();

        // 4. Mendapatkan total jumlah file dari tabel files
        $totalFiles = $this->fileModel->countAllResults();

        // --- Bagian baru untuk Dokumen Terbaru ---

        // Ambil 10 folder terbaru
        $recentFolders = $this->folderModel
                               ->select('id, name, parent_id, owner_id, created_at, "folder" as type')
                               ->orderBy('created_at', 'DESC')
                               ->limit(10)
                               ->findAll();

        // Ambil 10 file terbaru
        $recentFiles = $this->fileModel
                             ->select('id, file_name as name, folder_id as parent_id, uploader_id as owner_id, created_at, "file" as type')
                             ->orderBy('created_at', 'DESC')
                             ->limit(10)
                             ->findAll();

        // Ambil 10 dokumen HRD terbaru
        $recentHrdDocuments = $this->hrdDocumentModel
                                   ->select('id, category as name, created_at, "hrd_doc" as type, file_id')
                                   ->orderBy('created_at', 'DESC')
                               ->limit(10)
                               ->findAll();

        // Gabungkan semua hasil
        $allRecentDocuments = array_merge($recentFolders, $recentFiles, $recentHrdDocuments);

        // Urutkan ulang berdasarkan created_at (terbaru pertama)
        usort($allRecentDocuments, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        // Ambil hanya 10 data teratas setelah diurutkan
        $latestDocuments = array_slice($allRecentDocuments, 0, 10);

        // Ambil informasi tambahan seperti nama folder induk dan nama pengunggah
        foreach ($latestDocuments as &$doc) {
            // Tentukan ikon berdasarkan tipe
            if ($doc['type'] === 'folder') {
                $doc['icon_class'] = 'w-5 h-5 text-gray-500 mr-2';
                $doc['icon_path'] = '<path d="M2 6a2 2 0 012-2h5l2 2h7a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />';
                $doc['display_name'] = $doc['name'];
            } elseif ($doc['type'] === 'file') {
                $doc['icon_class'] = 'w-5 h-5 text-red-500 mr-2';
                $doc['icon_path'] = '<path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>';
                $doc['display_name'] = $doc['name'];
            } elseif ($doc['type'] === 'hrd_doc') {
                $doc['icon_class'] = 'w-5 h-5 text-blue-500 mr-2';
                $doc['icon_path'] = '<path d="M10 2a2 2 0 00-2 2v2H6a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2V8a2 2 0 00-2-2h-2V4a2 2 0 00-2-2zM9 4a1 1 0 011-1h2a1 1 0 011 1v2H9V4z" />';
                $doc['display_name'] = $doc['name'];
            }

            // Dapatkan nama folder induk
            $doc['parent_folder_name'] = 'N/A';
            if (isset($doc['parent_id']) && $doc['parent_id']) {
                $parentFolder = $this->folderModel->find($doc['parent_id']);
                if ($parentFolder) {
                    $doc['parent_folder_name'] = $parentFolder['name'];
                }
            } elseif ($doc['type'] === 'hrd_doc') {
                $doc['parent_folder_name'] = 'HRD Documents';
            }

            // Dapatkan nama pengunggah/pemilik
            $doc['uploader_name'] = 'System';
            $ownerId = null;
            // Cek owner_id di folder dan file
            if (isset($doc['owner_id']) && $doc['owner_id']) {
                $ownerId = $doc['owner_id'];
            }
            // Jika dokumen adalah dokumen HRD dan memiliki file_id, coba dapatkan pengunggah dari file yang terkait
            // Bagian ini mengasumsikan bahwa hrd_documents.file_id terhubung ke files.id dan files.uploader_id adalah pengunggah
            if ($doc['type'] === 'hrd_doc' && isset($doc['file_id'])) {
                $linkedFile = $this->fileModel->find($doc['file_id']);
                if ($linkedFile && isset($linkedFile['uploader_id'])) {
                    $ownerId = $linkedFile['uploader_id'];
                }
            }

            if ($ownerId) {
                $user = $this->userModel->find($ownerId);
                if ($user) {
                    $doc['uploader_name'] = $user['name'];
                }
            } else if ($doc['type'] === 'hrd_doc') {
                // Jika tidak ditemukan owner_id untuk dokumen HRD, default ke HRD Admin atau pengguna sesi saat ini
                $doc['uploader_name'] = session()->get('name') ?? 'HRD Admin';
            }
        }

        // Mengirim semua data yang diperlukan ke view
        $data['totalFolders'] = $totalFolders;
        $data['totalHrdFiles'] = $totalHrdFiles;
        $data['totalUser'] = $totalUsers;
        $data['totalFiles'] = $totalFiles;
        $data['latestDocuments'] = $latestDocuments;

        return view('HRD/dashboard', $data);
    }

    public function dokumenStaff()
    {
        $session = session();
        $userRoleId = $session->get('role_id');

        $data['personalFolders'] = $this->folderModel->where('owner_role', 'staff')->findAll();

        return view('HRD/dokumenStaff', $data);
    }

    public function viewStaffFolder($folderId)
    {
        $folder = $this->folderModel->find($folderId);

        if (!$folder) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Folder tidak ditemukan.');
        }

        $subFolders = $this->folderModel->where('parent_id', $folderId)->findAll();
        $filesInFolder = $this->fileModel->where('folder_id', $folderId)->findAll();

        $breadcrumbs = $this->getBreadcrumbs($folderId);

        $data = [
            'folder' => $folder,
            'subFolders' => $subFolders,
            'filesInFolder' => $filesInFolder,
            'breadcrumbs' => $breadcrumbs,
            'folderId' => $folderId,
            'folderType' => $folder['folder_type'] ?? 'personal',
            'isShared' => $folder['is_shared'] ?? 0,
        ];

        return view('HRD/viewFolderContent', $data);
    }

    public function createFolder()
    {
        if ($this->request->isAJAX()) {
            $rules = [
                'name'        => 'required|min_length[3]|max_length[255]',
                'parent_id'   => 'permit_empty|integer',
                'folder_type' => 'required|in_list[personal,shared,public,staff,spv,manager,direksi]',
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON(['status' => 'error', 'message' => $this->validator->getErrors()]);
            }

            $folderName = $this->request->getVar('name');
            $parentId = $this->request->getVar('parent_id');
            $folderType = $this->request->getVar('folder_type');

            $userId = session()->get('user_id');
            $userRole = session()->get('role_id');

            $parentPathForPhysical = '';
            if ($parentId && $parentId != 0) {
                $parentFolder = $this->folderModel->find($parentId);
                if ($parentFolder && isset($parentFolder['path'])) {
                    $parentPathForPhysical = $parentFolder['path'] . DIRECTORY_SEPARATOR;
                }
            }

            // Perbaikan: Bangun storagePath agar langsung di bawah WRITEPATH . 'uploads/'
            // dan ikuti struktur path yang disimpan di database (yang seharusnya sudah relatif dari 'uploads/')
            $storagePath = WRITEPATH . 'uploads/' . $parentPathForPhysical;
            $fullPath = $storagePath . $folderName;

            if (!is_dir($fullPath)) {
                if (!mkdir($fullPath, 0777, true)) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membuat direktori fisik folder.']);
                }
            }

            // Jalur relatif yang disimpan di database (path)
            $relativePath = $parentPathForPhysical . $folderName;

            $data = [
                'name' => $folderName,
                'parent_id' => ($parentId && $parentId != 0) ? $parentId : null,
                'owner_id' => $userId,
                'owner_role' => $userRole,
                'path' => $relativePath,
                'full_path_physical' => $fullPath,
                'folder_type' => $folderType,
            ];

            if ($this->folderModel->insert($data)) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Folder berhasil dibuat.']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan data folder ke database.']);
            }
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request.']);
    }

    public function uploadFile()
    {
        if ($this->request->isAJAX()) {
            $validationRule = [
                'file_upload' => [
                    'label' => 'File',
                    'rules' => 'uploaded[file_upload]|max_size[file_upload,10240]|ext_in[file_upload,pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif]',
                    'errors' => [
                        'uploaded' => 'Anda harus memilih file untuk diunggah.',
                        'max_size' => 'Ukuran file terlalu besar (max 10MB).',
                        'ext_in'   => 'Jenis file tidak diizinkan.',
                    ],
                ],
            ];

            if (!$this->validate($validationRule)) {
                return $this->response->setJSON(['status' => 'error', 'message' => $this->validator->getErrors()]);
            }

            $uploadedFile = $this->request->getFile('file_upload');
            $folderId = $this->request->getPost('folder_id');

            $folder = null;
            $targetPath = WRITEPATH . 'uploads/'; // Jalur dasar untuk semua unggahan

            // Periksa apakah folderId diberikan dan valid
            if ($folderId) {
                $folder = $this->folderModel->find($folderId);
                if ($folder && isset($folder['full_path_physical'])) {
                    // Jika folder spesifik ada, gunakan jalur fisiknya
                    $targetPath = $folder['full_path_physical'] . DIRECTORY_SEPARATOR;
                } else {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Folder tidak ditemukan atau jalur fisik tidak valid.']);
                }
            }
            // Hapus bagian else { $targetPath .= 'hrd_root_files'; ... }

            $newName = $uploadedFile->getRandomName();

            if ($uploadedFile->isValid() && !$uploadedFile->hasMoved()) {
                $uploadedFile->move($targetPath, $newName);

                $userId = session()->get('user_id');
                $folderType = $folder ? $folder['folder_type'] : 'general'; // Default 'general' jika tidak ada folderId

                $data = [
                    'folder_id'        => $folderId,
                    'file_name'        => $uploadedFile->getName(),
                    'file_type'        => $uploadedFile->getMimeType(),
                    'file_size'        => $uploadedFile->getSize('kb'),
                    'uploader_id'      => $userId,
                    // server_file_name harus menyimpan jalur relatif dari WRITEPATH . 'uploads/'
                    'server_file_name' => str_replace(WRITEPATH . 'uploads/', '', $targetPath) . $newName,
                    'folder_type'      => $folderType,
                    'created_at'       => date('Y-m-d H:i:s'),
                ];

                if ($this->fileModel->insert($data)) {
                    return $this->response->setJSON(['status' => 'success', 'message' => 'File berhasil diunggah.']);
                } else {
                    unlink($targetPath . $newName);
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan data file ke database.']);
                }
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => $uploadedFile->getErrorString() . ' (' . $uploadedFile->getError() . ')']);
            }
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request.']);
    }

    public function renameFolder()
    {
        if ($this->request->isAJAX()) {
            $rules = [
                'id'        => 'required|integer',
                'newName'   => 'required|min_length[3]|max_length[255]',
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON(['status' => 'error', 'message' => $this->validator->getErrors()]);
            }

            $folderId = $this->request->getPost('id');
            $newName = $this->request->getPost('newName');

            $folder = $this->folderModel->find($folderId);
            if (!$folder) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Folder tidak ditemukan.']);
            }

            if (!isset($folder['full_path_physical'])) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Jalur fisik folder tidak ditemukan.']);
            }

            $oldPhysicalPath = $folder['full_path_physical'];
            $newPhysicalPath = dirname($oldPhysicalPath) . DIRECTORY_SEPARATOR . $newName;

            if (rename($oldPhysicalPath, $newPhysicalPath)) {
                $data = [
                    'name' => $newName,
                    'full_path_physical' => $newPhysicalPath,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if ($this->folderModel->update($folderId, $data)) {
                    return $this->response->setJSON(['status' => 'success', 'message' => 'Folder berhasil diganti nama.']);
                } else {
                    rename($newPhysicalPath, $oldPhysicalPath);
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui nama folder di database.']);
                }
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal mengganti nama folder fisik. Pastikan folder tidak sedang digunakan.']);
            }
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request.']);
    }

    public function deleteFolder()
    {
        if ($this->request->isAJAX()) {
            $rules = [
                'id' => 'required|integer',
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON(['status' => 'error', 'message' => $this->validator->getErrors()]);
            }

            $folderId = $this->request->getPost('id');
            $folder = $this->folderModel->find($folderId);

            if (!$folder) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Folder tidak ditemukan.']);
            }

            if (!isset($folder['full_path_physical'])) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Jalur fisik folder tidak ditemukan.']);
            }

            $physicalPath = $folder['full_path_physical'];

            if (is_dir($physicalPath)) {
                // Gunakan helper CodeIgniter `delete_files` untuk menghapus isi folder
                // Pastikan helper 'filesystem' sudah dimuat (sudah ada di __construct)
                if (!delete_files($physicalPath, TRUE) || !rmdir($physicalPath)) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus folder fisik atau isinya. Pastikan folder tidak terkunci.']);
                }
            }

            // Hapus file-file yang terkait dengan folder ini dari database
            $this->fileModel->where('folder_id', $folderId)->delete();
            // Hapus sub-folder yang terkait dengan folder ini dari database (rekursif jika perlu, tapi ini hanya menghapus langsung)
            $this->folderModel->where('parent_id', $folderId)->delete();

            if ($this->folderModel->delete($folderId)) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Folder berhasil dihapus.']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus folder dari database.']);
            }
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request.']);
    }

    public function renameFile()
    {
        if ($this->request->isAJAX()) {
            $rules = [
                'id'        => 'required|integer',
                'newName'   => 'required|min_length[1]|max_length[255]',
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON(['status' => 'error', 'message' => $this->validator->getErrors()]);
            }

            $fileId = $this->request->getPost('id');
            $newName = $this->request->getPost('newName');

            $file = $this->fileModel->find($fileId);
            if (!$file) {
                // Baris 475 yang diperbaiki
                return $this->response->setJSON(['status' => 'error', 'message' => 'File tidak ditemukan.']);
            }

            $oldFileName = $file['file_name'];
            $fileExtension = pathinfo($oldFileName, PATHINFO_EXTENSION);
            $newFullFileName = $newName . '.' . $fileExtension;

            $folder = null;
            // Perbaikan: Jalur dasar untuk file yang tidak di folder adalah WRITEPATH . 'uploads/'
            $physicalFilePathBase = WRITEPATH . 'uploads/';

            if (isset($file['folder_id']) && $file['folder_id']) {
                $folder = $this->folderModel->find($file['folder_id']);
                if ($folder && isset($folder['full_path_physical'])) {
                    $physicalFilePathBase = $folder['full_path_physical'];
                } else {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Folder terkait file tidak ditemukan atau jalur fisiknya tidak valid.']);
                }
            }
            $physicalFilePath = $physicalFilePathBase . DIRECTORY_SEPARATOR . $file['server_file_name'];
            $newPhysicalFilePath = dirname($physicalFilePath) . DIRECTORY_SEPARATOR . $newFullFileName;

            if (rename($physicalFilePath, $newPhysicalFilePath)) {
                $data = [
                    'file_name' => $newFullFileName,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if ($this->fileModel->update($fileId, $data)) {
                    return $this->response->setJSON(['status' => 'success', 'message' => 'File berhasil diganti nama.']);
                } else {
                    // Rollback rename if DB update fails
                    rename($newPhysicalFilePath, $physicalFilePath);
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui nama file di database.']);
                }
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal mengganti nama file fisik.']);
            }
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request.']);
    }

    public function deleteFile($fileId)
    {
        if ($this->request->isAJAX()) {
            $file = $this->fileModel->find($fileId);

            if (!$file) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'File tidak ditemukan.']);
            }

            $folder = null;
            // Perbaikan: Jalur dasar untuk file yang tidak di folder adalah WRITEPATH . 'uploads/'
            $physicalFilePathBase = WRITEPATH . 'uploads/';

            if (isset($file['folder_id']) && $file['folder_id']) {
                $folder = $this->folderModel->find($file['folder_id']);
                if ($folder && isset($folder['full_path_physical'])) {
                    $physicalFilePathBase = $folder['full_path_physical'];
                } else {
                    log_message('warning', 'File ' . $fileId . ' has invalid folder_id or missing full_path_physical for folder ' . $file['folder_id']);
                }
            }
            $physicalFilePath = $physicalFilePathBase . DIRECTORY_SEPARATOR . $file['server_file_name'];

            if (file_exists($physicalFilePath)) {
                if (!unlink($physicalFilePath)) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus file fisik.']);
                }
            } else {
                log_message('warning', 'Fisik file tidak ditemukan: ' . $physicalFilePath . ' (ID: ' . $fileId . ')');
            }

            if ($this->fileModel->delete($fileId)) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'File berhasil dihapus.']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus file dari database.']);
            }
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request.']);
    }

    public function downloadFile($fileId)
    {
        $file = $this->fileModel->find($fileId);

        if (!$file) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File tidak ditemukan.');
        }

        if (!isset($file['server_file_name'])) {
            log_message('error', 'File ID ' . $fileId . ' ditemukan, tetapi kunci "server_file_name" tidak ada.');
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Informasi file tidak lengkap.');
        }

        $folder = null;
        // Perbaikan: Jalur dasar untuk mencari file adalah WRITEPATH . 'uploads/'
        $filePathBase = WRITEPATH . 'uploads/';

        if (isset($file['folder_id']) && $file['folder_id']) {
            $folder = $this->folderModel->find($file['folder_id']);
            if ($folder && isset($folder['full_path_physical'])) {
                $filePathBase = $folder['full_path_physical'] . DIRECTORY_SEPARATOR;
            } else {
                log_message('warning', 'File ' . $fileId . ' memiliki folder_id tidak valid atau full_path_physical hilang untuk folder ' . $file['folder_id']);
            }
        }
        $filePath = $filePathBase . $file['server_file_name'];


        if (!file_exists($filePath)) {
            log_message('error', 'File fisik tidak ditemukan di: ' . $filePath . ' untuk File ID: ' . $fileId);
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File fisik tidak ditemukan.');
        }

        return $this->response->download($filePath, null)->setFileName($file['file_name']);
    }

    public function viewFile($fileId)
    {
        $file = $this->fileModel->find($fileId);

        if (!$file) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File tidak ditemukan.');
        }

        if (!isset($file['server_file_name'])) {
            log_message('error', 'File ID ' . $fileId . ' ditemukan, tetapi kunci "server_file_name" tidak ada.');
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Informasi file tidak lengkap.');
        }

        $folder = null;
        // Perbaikan: Jalur dasar untuk mencari file adalah WRITEPATH . 'uploads/'
        $filePathBase = WRITEPATH . 'uploads/';

        if (isset($file['folder_id']) && $file['folder_id']) {
            $folder = $this->folderModel->find($file['folder_id']);
            if ($folder && isset($folder['full_path_physical'])) {
                $filePathBase = $folder['full_path_physical'] . DIRECTORY_SEPARATOR;
            } else {
                log_message('warning', 'File ' . $fileId . ' memiliki folder_id tidak valid atau full_path_physical hilang untuk folder ' . $file['folder_id']);
            }
        }
        $filePath = $filePathBase . $file['server_file_name'];


        if (!file_exists($filePath)) {
            log_message('error', 'File fisik tidak ditemukan di: ' . $filePath . ' untuk File ID: ' . $fileId);
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File fisik tidak ditemukan.');
        }

        $mimeType = $file['file_type'];

        if (strpos($mimeType, 'image/') === 0 || $mimeType === 'application/pdf') {
            header('Content-Type: ' . $mimeType);
            readfile($filePath);
            exit;
        } else {
            return $this->response->download($filePath, null)->setFileName($file['file_name']);
        }
    }

    private function getBreadcrumbs($folderId)
    {
        $breadcrumbs = [];
        $currentFolderId = $folderId;

        while ($currentFolderId) {
            $folder = $this->folderModel->find($currentFolderId);
            if ($folder) {
                array_unshift($breadcrumbs, ['id' => $folder['id'], 'name' => $folder['name']]);
                $currentFolderId = $folder['parent_id'];
            } else {
                break;
            }
        }
        return $breadcrumbs;
    }

    public function dokumenSPV()
    {
        return view('HRD/dokumenSPV');
    }
    public function dokumenManager()
    {
        return view('HRD/dokumenManager');
    }
    public function dokumenDireksi()
    {
        return view('HRD/dokumenDireksi');
    }

    public function dokumenBersama()
    {
        return view('HRD/dokumenBersama');
    }

    public function dokumenUmum()
    {
        return view('HRD/dokumenUmum');
    }

    public function aktivitas()
    {
        return view('HRD/aktivitas');
    }
}