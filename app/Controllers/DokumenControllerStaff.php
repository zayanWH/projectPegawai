<?php namespace App\Controllers;

use App\Models\FolderModel;
use App\Models\FileModel;
use CodeIgniter\Controller;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\API\ResponseTrait;

class DokumenControllerStaff extends Controller
{
    use ResponseTrait;
    protected $folderModel;
    protected $fileModel;
    protected $session;

    public function __construct()
    {
        $this->folderModel = new FolderModel();
        $this->fileModel = new FileModel();
        $this->session = \Config\Services::session();
        helper(['form', 'url']);
    }

    public function index()
    {
        return redirect()->to(base_url('staff/dokumen-staff'));
    }

   public function dashboard()
    {
        $folderModel = new FolderModel();
        $fileModel = new FileModel();

        // Hitung total folder dari tabel 'folders'
        $totalFolders = $folderModel->countAllResults();

        // Hitung total file dari tabel 'files'
        $totalFiles = $fileModel->countAllResults();

        // Ambil tanggal created_at terbaru dari tabel 'folders'
        $latestFolderUpload = $folderModel->selectMax('created_at')->first();
        $latestFolderDate = $latestFolderUpload['created_at'] ?? null;

        // Ambil tanggal created_at terbaru dari tabel 'files'
        $latestFileUpload = $fileModel->selectMax('created_at')->first();
        $latestFileDate = $latestFileUpload['created_at'] ?? null;

        // Tentukan tanggal upload paling terbaru
        $latestUploadDate = null;
        if ($latestFolderDate && $latestFileDate) {
            $latestUploadDate = (strtotime($latestFolderDate) > strtotime($latestFileDate)) ? $latestFolderDate : $latestFileDate;
        } elseif ($latestFolderDate) {
            $latestUploadDate = $latestFolderDate;
        } elseif ($latestFileDate) {
            $latestUploadDate = $latestFileDate;
        }

        // Format tanggal untuk tampilan (opsional, bisa juga diformat di view)
        $formattedLatestUpload = $latestUploadDate ? date('d M Y', strtotime($latestUploadDate)) : 'Belum ada upload';

        // Ambil 10 item terbaru (file dan folder)
        $folders = $folderModel->select("id, name, created_at, owner_id as uploader_id, 'folder' as type")
                               ->orderBy('created_at', 'DESC')
                               ->findAll();
        
        $files = $fileModel->select("id, file_name as name, created_at, uploader_id, 'file' as type")
                           ->orderBy('created_at', 'DESC')
                           ->findAll();
        
        $recentItems = array_merge($folders, $files);
        
        // Urutkan gabungan item berdasarkan tanggal pembuatan
        usort($recentItems, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        // Ambil hanya 10 item teratas
        $recentItems = array_slice($recentItems, 0, 10);

        // Ambil semua personal folders untuk user yang sedang login
        $personalFolders = $folderModel->where('owner_id', session()->get('user_id'))
                                       ->where('folder_type', 'personal')
                                       ->findAll();

        // Ambil file yang tidak terkait dengan folder (file tanpa folder_id)
        $orphanFiles = $fileModel->where('folder_id IS NULL')->findAll();

        $data = [
            'personalFolders' => $personalFolders,
            'folderId' => null,
            'folderType' => null,
            'isShared' => null,
            'sharedType' => null,
            'orphanFiles' => $orphanFiles,
            'totalFolders' => $totalFolders,
            'totalFiles' => $totalFiles,
            'latestUploadDate' => $formattedLatestUpload, // Meneruskan tanggal terakhir upload ke view
            'recentItems' => $recentItems // Menambahkan item terbaru ke data
        ];

        return view('Staff/dashboard', $data);
    }

    public function search()
    {
        $query = $this->request->getVar('q');
        if (!$query) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Query is missing.']);
        }

        $userId = $this->session->get('user_id');

        $folders = $this->folderModel
            ->where('owner_id', $userId)
            ->like('name', $query)
            ->select("id, name, 'folder' as type")
            ->findAll();

        $files = $this->fileModel
            ->where('uploader_id', $userId)
            ->like('file_name', $query)
            ->select("id, file_name as name, 'file' as type, folder_id")
            ->findAll();

        $results = array_merge($folders, $files);

        return $this->response->setJSON($results);
    }

    public function viewFile($fileId)
    {
        $file = $this->fileModel->find($fileId);

        if (!$file) {
            throw PageNotFoundException::forPageNotFound('File tidak ditemukan.');
        }

        $data = [
            'fileId'   => $fileId,
            'fileName' => $file['file_name'],
        ];

        return view('Staff/view_file_wrapper', $data);
    }

    public function serveFile($fileId)
    {
        $file = $this->fileModel->find($fileId);

        if (!$file) {
            throw PageNotFoundException::forPageNotFound('File tidak ditemukan.');
        }

        $filePath = WRITEPATH . 'uploads/' . $file['file_path'];

        if (!file_exists($filePath)) {
            throw PageNotFoundException::forPageNotFound('File tidak ada di server.');
        }

        // Tentukan Content-Type berdasarkan ekstensi file
        $mime = mime_content_type($filePath);
        
        // Atur header untuk menampilkan file di browser (inline)
        $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . basename($filePath) . '"')
            ->setBody(file_get_contents($filePath));

        return $this->response;
    }


    public function dokumenStaff()
    {
        $userId = $this->session->get('user_id');

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses dokumen.');
        }

        $personalFolders = $this->folderModel->where('owner_id', $userId)
                                             ->where('parent_id', NULL)
                                             ->where('folder_type', 'personal')
                                             ->findAll();

        $orphanFiles = $this->fileModel->where('uploader_id', $userId)
                                         ->where('folder_id', NULL)
                                         ->findAll();

        $data = [
            'title'           => 'Dokumen Saya',
            'personalFolders' => $personalFolders,
            'orphanFiles'     => $orphanFiles,
        ];

        return view('staff/dokumenStaff', $data);
    }

    public function viewFolder($folderId = null)
    {
        if ($folderId === null) {
            throw PageNotFoundException::forPageNotFound('Folder ID tidak ditentukan.');
        }

        $userId = $this->session->get('user_id');

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses folder.');
        }

        $currentFolder = $this->folderModel->find($folderId);

        if (!$currentFolder) {
            throw PageNotFoundException::forPageNotFound('Folder tidak ditemukan.');
        }

        if ($currentFolder['folder_type'] === 'personal' && $currentFolder['owner_id'] !== $userId) {
            return redirect()->to(base_url('staff/dokumen-staff'))->with('error', 'Anda tidak memiliki akses ke folder personal ini.');
        }

        if ($currentFolder['folder_type'] === 'shared' && $currentFolder['owner_id'] !== $userId) {
            $userRole = $this->session->get('user_role');
            $accessRoles = json_decode($currentFolder['access_roles'] ?? '[]', true);

            if (empty($accessRoles) || !in_array($userRole, $accessRoles)) {
                return redirect()->to(base_url('staff/dokumen-staff'))->with('error', 'Anda tidak memiliki izin untuk folder shared ini.');
            }
        }

        $subFolders = $this->folderModel->where('parent_id', $folderId)->findAll();
        $filesInFolder = $this->fileModel->where('folder_id', $folderId)->findAll();
        $breadcrumbs = $this->folderModel->getBreadcrumbs($folderId);

        $data = [
            'title'         => 'Folder: ' . $currentFolder['name'],
            'folderName'    => $currentFolder['name'],
            'folderId'      => $currentFolder['id'],
            'isShared'      => (bool)$currentFolder['is_shared'],
            'sharedType'    => $currentFolder['shared_type'],
            'folderType'    => $currentFolder['folder_type'],
            'subFolders'    => $subFolders,
            'filesInFolder' => $filesInFolder,
            'breadcrumbs'   => $breadcrumbs,
        ];

        return view('staff/viewFolder', $data);
    }

    public function createFolder()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
        }

        $userId = $this->session->get('user_id');
        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized. User not logged in.']);
        }

        $input = $this->request->getJSON(true);

        $folderName = $input['name'] ?? null;
        $parentId = $input['parent_id'] ?? null;
        $folderType = $input['folder_type'] ?? 'personal';
        $isShared = $input['is_shared'] ?? 0;
        $sharedType = $input['shared_type'] ?? null;
        $accessRoles = $input['access_roles'] ?? null;

        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
        ];
        if (!$this->validate($rules, ['name' => ['required' => 'Nama folder tidak boleh kosong.']])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Validasi gagal.', 'errors' => $this->validator->getErrors()]);
        }

        if ($parentId) {
            $parentFolder = $this->folderModel->find($parentId);
            if ($parentFolder) {
                $folderType = $parentFolder['folder_type'];
                $isShared = $parentFolder['is_shared'];
                $sharedType = $parentFolder['shared_type'];
                $accessRoles = json_decode($parentFolder['access_roles'] ?? '[]', true);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Parent folder tidak ditemukan.']);
            }
        }

        $data = [
            'name'        => $folderName,
            'parent_id'   => $parentId,
            'owner_id'    => $userId,
            'folder_type' => $folderType,
            'is_shared'   => (int)$isShared,
            'shared_type' => ((int)$isShared === 1) ? $sharedType : null,
            'access_roles' => ((int)$isShared === 1 && !empty($accessRoles)) ? json_encode($accessRoles) : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->folderModel->insert($data)) {
            // Setelah insert, buat folder fisik di server
            $newFolderId = $this->folderModel->insertID();
            $relativePath = $this->folderModel->getFolderPath($newFolderId);
            $folderPath = WRITEPATH . 'uploads/' . $relativePath;
            if (!is_dir($folderPath)) {
                if (!mkdir($folderPath, 0777, true)) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membuat folder fisik di server.']);
                }
            }
            return $this->response->setJSON(['status' => 'success', 'message' => 'Folder berhasil dibuat!', 'new_folder_id' => $newFolderId]);
        } else {
            $errors = $this->folderModel->errors();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membuat folder.', 'errors' => $errors]);
        }
    }

    public function uploadFromFolder()
    {
        $userId = $this->session->get('user_id');
        if (!$userId) { 
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $file = $this->request->getFile('file');
        $relativePath = $this->request->getPost('relativePath');
        $parentIdPost = $this->request->getPost('parent_id');
        $rootParentId = ($parentIdPost === 'null' || $parentIdPost === null || $parentIdPost === '') ? null : $parentIdPost;

        if (!$file || !$file->isValid() || empty($relativePath)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'File atau path tidak valid.'], 400);
        }

        $pathParts = explode('/', $relativePath);
        $fileName = array_pop($pathParts);
        $folderPath = implode('/', $pathParts);

        // Cari atau buat folder tujuan
        $targetFolderId = $this->folderModel->findOrCreateByPath($folderPath, $rootParentId, $userId);

        if ($targetFolderId === null) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membuat struktur folder di server.'], 500);
        }

        // Ambil info SEBELUM memindahkan file
        $fileMimeType = $file->getMimeType();
        $fileSize = $file->getSize();
        $newName = $file->getRandomName();

        // Simpan file
        if ($file->move(WRITEPATH . 'uploads', $newName)) {
            $data = [
                'folder_id'   => $targetFolderId,
                'uploader_id' => $userId,
                'file_name'   => $fileName,
                'file_path'   => $newName,
                'file_size'   => $fileSize,
                'file_type'   => $fileMimeType,
            ];
            $this->fileModel->insert($data);
            return $this->response->setJSON(['status' => 'success']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memindahkan file.'], 500);
    }

    public function uploadFile()
    {
        $userId = $this->session->get('user_id');

        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized. User not logged in.']);
        }

        $validationRule = [
            'file_upload' => [
                'label' => 'File',
                'rules' => 'uploaded[file_upload]|max_size[file_upload,10240]|ext_in[file_upload,pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif]',
                'errors' => [
                    'uploaded' => 'Harus ada file yang diupload.',
                    'max_size' => 'Ukuran file terlalu besar (maks 10MB).',
                    'ext_in' => 'Format file tidak didukung.',
                ],
            ],
            'folder_id' => [
                'label' => 'Folder ID',
                'rules' => 'permit_empty|is_natural_no_zero',
            ],
        ];

        if (!$this->validate($validationRule)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $uploadedFile = $this->request->getFile('file_upload');
        $folderId = $this->request->getPost('folder_id');

        if ($uploadedFile->isValid() && !$uploadedFile->hasMoved()) {
            // Ambil info SEBELUM memindahkan file
            $fileMimeType = $uploadedFile->getMimeType();
            $fileSize = $uploadedFile->getSize();
            $fileName = $uploadedFile->getName();
            $newName = $uploadedFile->getRandomName();

            $targetDirectory = WRITEPATH . 'uploads/';

            if (!is_dir($targetDirectory)) {
                mkdir($targetDirectory, 0777, true);
            }

            if ($uploadedFile->move($targetDirectory, $newName)) {
                $data = [
                    'folder_id'   => empty($folderId) ? null : $folderId,
                    'uploader_id' => $userId,
                    'file_name'   => $fileName,
                    'file_path'   => $newName,
                    'file_size'   => $fileSize,
                    'file_type'   => $fileMimeType,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ];

                if ($this->fileModel->insert($data)) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'File berhasil diunggah.'
                    ]); 
                } else {
                    // Hapus file jika insert DB gagal
                    unlink($targetDirectory . $newName);
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Gagal menyimpan data file ke database.',
                        'errors' => $this->fileModel->errors()
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal memindahkan file yang diunggah.',
                    'errors' => $uploadedFile->getErrorString() . '(' . $uploadedFile->getError() . ')'
                ]);
            }
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'File tidak valid atau sudah dipindahkan.',
            ]);
        }
    }

    public function downloadFile($fileId)
    {
        $userId = $this->session->get('user_id');

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengunduh file.');
        }

        $file = $this->fileModel->find($fileId);

        if (!$file) {
            throw PageNotFoundException::forPageNotFound('File tidak ditemukan.');
        }

        $allowedToDownload = false;
        if ($file['folder_id']) {
            $parentFolder = $this->folderModel->find($file['folder_id']);
            if ($parentFolder) {
                if ($parentFolder['folder_type'] === 'personal' && $parentFolder['owner_id'] === $userId) {
                    $allowedToDownload = true;
                } elseif ($parentFolder['folder_type'] === 'shared') {
                    $userRole = $this->session->get('user_role');
                    $accessRoles = json_decode($parentFolder['access_roles'] ?? '[]', true);

                    if (in_array($userRole, $accessRoles) || $parentFolder['owner_id'] === $userId) {
                        $allowedToDownload = true;
                    }
                }
            }
        } else {
            if ($file['uploader_id'] === $userId) {
                $allowedToDownload = true;
            }
        }

        if (!$allowedToDownload) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengunduh file ini.');
        }

        $this->fileModel->update($fileId, ['download_count' => ($file['download_count'] ?? 0) + 1]);

        $filePath = WRITEPATH . 'uploads/' . $file['file_path'];

        if (!file_exists($filePath)) {
            throw PageNotFoundException::forPageNotFound('File tidak ditemukan di server.');
        }

        return $this->response->download($filePath, null)->setFileName($file['file_name']);
    }

    public function dokumenBersama()
    {
        $userId = $this->session->get('user_id');

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses dokumen bersama.');
        }
        $sharedFolders = $this->folderModel
                              ->where('is_shared', 1)
                              ->groupStart() 
                                  ->where('owner_id', $userId) 
                                  ->orWhere('JSON_CONTAINS(access_roles, \'["' . $this->session->get('user_role') . '"]\')', null, false)
                              ->groupEnd()
                              ->findAll();

        $data = [
            'title'         => 'Dokumen Bersama',
            'sharedFolders' => $sharedFolders,
        ];

        return view('Umum/dokumenBersama', $data);
    }

    public function dokumenUmum()
    {
        $userId = $this->session->get('user_id');

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Anda harus login untuk mengakses dokumen umum.');
        }
        $publicFolders = $this->folderModel
                              ->where('folder_type', 'public')
                              ->findAll();

        $data = [
            'title'         => 'Dokumen Umum',
            'publicFolders' => $publicFolders,
        ];

        return view('Umum/dokumenUmum', $data);
    }
}