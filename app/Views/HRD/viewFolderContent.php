<?= $this->extend('layout/main') ?>

<?= $this->section('showBackButton') ?>

<?= $this->endSection() ?>

<?= $this->section('pageTitle') ?>
<?= esc($folderName ?? 'Isi Folder') ?>
<?= $this->endSection() ?>

<?= $this->section('pageLogo') ?>
<img src="<?= base_url('images/logo.jpg') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="hidden md:block">
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2 text-xl font-semibold text-gray-800">
                <nav class="text-sm font-medium text-gray-500">
                    <?php
                    // Tentukan root breadcrumb secara dinamis
                    $rootUrl = '';
                    $rootName = '';

                    // NEW: Logic untuk menentukan root breadcrumb
                    if (isset($isManagerFolder) && $isManagerFolder) {
                        $rootUrl = site_url('hrd/dokumen-manager');
                        $rootName = 'Dokumen Manager';
                    } elseif (isset($isSupervisorFolder) && $isSupervisorFolder) {
                        $rootUrl = site_url('hrd/dokumen-spv');
                        $rootName = 'Dokumen Supervisor';
                    } elseif (isset($isStaffFolder) && $isStaffFolder) {
                        $rootUrl = site_url('hrd/dokumen-staff');
                        $rootName = 'Dokumen Staff';
                    } elseif (isset($isDireksiFolder) && $isDireksiFolder) {
                        $rootUrl = site_url('hrd/dokumen-direksi');
                        $rootName = 'Dokumen Direksi';
                    } else {
                        $rootUrl = site_url('hrd/dashboard'); // URL default untuk dokumen HRD
                        $rootName = 'Dokumen Saya';
                    }
                    ?>
                    <a href="<?= $rootUrl ?>" class="text-blue-600 hover:text-blue-800"><?= esc($rootName) ?></a>

                    <?php if (!empty($breadcrumbs)): ?>
                        <?php
                        $startIndex = 0;
                        // ... logika untuk menyesuaikan startIndex ...
                        $totalBreadcrumbs = count($breadcrumbs);
                        ?>
                        <?php for ($i = $startIndex; $i < $totalBreadcrumbs; $i++): ?>
                            <?php $breadcrumb = $breadcrumbs[$i]; ?>
                            <span class="text-gray-500">/</span>
                            <?php if ($i < $totalBreadcrumbs - 1): ?>
                                <?php
                                $breadcrumbLink = '';
                                // Gunakan flag konteks dari controller
                                if (isset($isStaffFolder) && $isStaffFolder) {
                                    $breadcrumbLink = site_url('hrd/view-staff-folder/' . $breadcrumb['id']);
                                } elseif (isset($isSupervisorFolder) && $isSupervisorFolder) {
                                    $breadcrumbLink = site_url('hrd/view-supervisor-folder/' . $breadcrumb['id']);
                                } elseif (isset($isManagerFolder) && $isManagerFolder) {
                                    $breadcrumbLink = site_url('hrd/view-manager-folder/' . $breadcrumb['id']);
                                } elseif (isset($isDireksiFolder) && $isDireksiFolder) {
                                    $breadcrumbLink = site_url('hrd/view-direksi-folder/' . $breadcrumb['id']);
                                } else {
                                    // FALLBACK: URL default untuk folder HRD/lainnya
                                    $breadcrumbLink = site_url('hrd/view-folder/' . $breadcrumb['id']);
                                }
                                ?>
                                <a href="<?= $breadcrumbLink ?>"
                                    class="text-blue-600 hover:text-blue-800"><?= esc($breadcrumb['name']) ?></a>
                            <?php else: ?>
                                <span><?= esc($breadcrumb['name']) ?></span>
                            <?php endif; ?>
                        <?php endfor; ?>
                    <?php endif; ?>
                </nav>
            </div>
            <div class="flex items-center space-x-4">
                <img src="<?= base_url('images/logo.jpg') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
            </div>
        </div>
    </div>

    <div class="relative inline-block text-left mb-6">
        <button id="newButton"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                </path>
            </svg>
            <span>Baru</span>
        </button>

        <div id="newDropdown"
            class="absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg hidden">
            <a href="#" id="createFolderLink" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">📁 Buat
                Sub-Folder</a>
            <a href="#" id="uploadFileLink" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">⬆ Upload File</a>
        </div>
    </div>




    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                Isi Folder
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Jenis
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tanggal
                            Dibuat / Diunggah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($subFolders) && empty($filesInFolder)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                Folder ini kosong.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php if (!empty($subFolders)): ?>
                            <?php foreach ($subFolders as $subFolder): ?>
                                <tr class="hover:bg-gray-50 item-with-context-menu" data-item-type="folder"
                                    data-item-id="<?= esc($subFolder['id']) ?>" data-item-name="<?= esc($subFolder['name']) ?>"
                                    data-folder-id="<?= esc($subFolder['id']) ?>" data-folder-name="<?= esc($subFolder['name']) ?>"
                                    data-folder-type="<?= esc($subFolder['folder_type'] ?? 'personal') ?>"
                                    data-folder-is-shared="<?= esc($subFolder['is_shared'] ?? 0) ?>"
                                    data-folder-shared-type="<?= esc($subFolder['shared_type'] ?? '') ?>"
                                    data-folder-owner-id="<?= esc($subFolder['owner_id'] ?? '') ?>"
                                    data-folder-owner-name="<?= esc($subFolder['owner_display'] ?? $subFolder['owner_name'] ?? 'Unknown') ?>"
                                    data-folder-created-at="<?= esc($subFolder['created_at']) ?>"
                                    data-folder-updated-at="<?= esc($subFolder['updated_at']) ?>"
                                    data-folder-path="<?= esc($subFolder['path'] ?? $subFolder['name']) ?>">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $folderViewLink = '';
                                        if (isset($isStaffFolder) && $isStaffFolder) {
                                            $folderViewLink = base_url('hrd/view-staff-folder/' . $subFolder['id']);
                                        } elseif (isset($isSupervisorFolder) && $isSupervisorFolder) {
                                            // NEW: Link for Supervisor subfolders
                                            $folderViewLink = base_url('hrd/view-supervisor-folder/' . $subFolder['id']); // Needs to be defined in your routes
                                        } elseif (isset($isManagerFolder) && $isManagerFolder) {
                                            // NEW: Link for Manager subfolders
                                            $folderViewLink = base_url('hrd/view-manager-folder/' . $subFolder['id']); // Needs to be defined in your routes
                                        } elseif (isset($isDireksiFolder) && $isDireksiFolder) {
                                            // NEW: Link for Manager subfolders
                                            $folderViewLink = base_url('hrd/view-direksi-folder/' . $subFolder['id']); // Needs to be defined in your routes
                                        } else {
                                            $folderViewLink = base_url('hrd/view-folder/' . $subFolder['id']);
                                        }
                                        ?>
                                        <a href="<?= $folderViewLink ?>"
                                            class="block h-full w-full text-sm text-gray-900 hover:text-blue-700 hover:underline">
                                            <div class="flex items-center">
                                                <img src="<?= base_url('images/folder.png') ?>" alt="Folder Icon"
                                                    class="w-5 h-5 mr-2">
                                                <?= esc($subFolder['name']) ?>
                                            </div>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        Folder
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= date('d M Y', strtotime($subFolder['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <button
                                            onclick="showFloatingMenu(event, 'folder', '<?= esc($subFolder['id']) ?>', '<?= esc($subFolder['name']) ?>')"
                                            class="text-blue-600 hover:text-blue-900">⋮</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (!empty($filesInFolder)): ?>
                            <?php foreach ($filesInFolder as $file): ?>
                                <tr class="hover:bg-gray-50 item-with-context-menu" data-item-type="file"
                                    data-item-id="<?= esc($file['id']) ?>" data-item-name="<?= esc($file['file_name']) ?>"
                                    data-file-size="<?= esc($file['file_size']) ?>" data-file-type="<?= esc($file['file_type']) ?>"
                                    data-file-uploaded-by="<?= esc($file['uploaded_by'] ?? 'Unknown') ?>"
                                    data-file-created-at="<?= esc($file['created_at']) ?>"
                                    data-file-updated-at="<?= esc($file['updated_at'] ?? $file['created_at']) ?>"
                                    data-file-path="<?= esc($file['server_file_name']) ?>">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="<?= site_url('hrd/file/view/' . $file['id']) ?>" target="_blank"
                                            class="block h-full w-full text-sm text-gray-900 hover:text-blue-700 hover:underline">
                                            <div class="flex items-center">
                                                <?php
                                                $fileExtension = pathinfo($file['file_name'], PATHINFO_EXTENSION);
                                                $iconSrc = '';

                                                switch (strtolower($fileExtension)) {
                                                    case 'pdf':
                                                        $iconSrc = base_url('images/pdf.png');
                                                        break;
                                                    case 'doc':
                                                    case 'docx':
                                                        $iconSrc = base_url('images/word.png');
                                                        break;
                                                    case 'xls':
                                                    case 'xlsx':
                                                        $iconSrc = base_url('images/excel.png');
                                                        break;
                                                    case 'pptx':
                                                        $iconSrc = base_url('images/ppt.png');
                                                        break;
                                                    case 'png':
                                                    case 'jpg':
                                                    case 'jpeg':
                                                    case 'gif':
                                                        $iconSrc = base_url('images/image.png');
                                                        break;
                                                    default:
                                                        $iconSrc = base_url('images/file-default.png');
                                                        break;
                                                }
                                                ?>
                                                <img src="<?= $iconSrc ?>" alt="File Icon" class="w-5 h-5 mr-2">
                                                <span class="text-sm text-gray-900"><?= esc($file['file_name']) ?></span>
                                            </div>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= esc(strtoupper($fileExtension)) ?> File
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= date('d M Y', strtotime($file['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <button
                                            onclick="showFloatingMenu(event, 'file', '<?= esc($file['id']) ?>', '<?= esc($file['file_name']) ?>')"
                                            class="text-blue-600 hover:text-blue-900">⋮</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="block md:hidden">
    <div class="relative inline-block text-left mb-6">
        <button id="newButtonMobile"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                </path>
            </svg>
            <span>Baru</span>
        </button>

        <div id="newDropdownMobile"
            class="absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg hidden">
            <a href="#" id="createFolderLinkMobile" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">📁 Buat
                Sub-Folder</a>
            <a href="#" id="uploadFileLinkMobile" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">⬆ Upload
                File</a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Isi Folder</h2>
        </div>
        <div class="divide-y divide-gray-200">
            <?php if (empty($subFolders) && empty($filesInFolder)): ?>
                <div class="flex items-center justify-center px-6 py-4 text-gray-500">
                    Folder ini kosong.
                </div>
            <?php else: ?>
                <?php foreach ($subFolders as $subFolder): ?>
                    <div class="relative flex items-center justify-between px-4 py-3 hover:bg-gray-50"
                        data-folder-id="<?= esc($subFolder['id']) ?>" data-folder-name="<?= esc($subFolder['name']) ?>"
                        data-folder-type="<?= esc($subFolder['folder_type']) ?>"
                        data-folder-is-shared="<?= esc($subFolder['is_shared'] ?? 0) ?>"
                        data-folder-shared-type="<?= esc($subFolder['shared_type'] ?? '') ?>"
                        data-folder-owner-id="<?= esc($subFolder['owner_id']) ?>"
                        data-folder-owner-name="<?= esc($subFolder['owner_display'] ?? $subFolder['owner_name'] ?? 'Unknown') ?>"
                        data-folder-created-at="<?= esc($subFolder['created_at']) ?>"
                        data-folder-updated-at="<?= esc($subFolder['updated_at']) ?>"
                        data-folder-path="<?= esc($subFolder['path'] ?? $subFolder['name']) ?>">
                        <div class="flex items-center space-x-4">
                            <img src="<?= base_url('images/folder.png') ?>" alt="Folder Icon" class="w-6 h-6">
                            <div>
                                <?php
                                $folderViewLink = '';
                                if (isset($isStaffFolder) && $isStaffFolder) {
                                    $folderViewLink = base_url('hrd/view-staff-folder/' . $subFolder['id']);
                                } elseif (isset($isSupervisorFolder) && $isSupervisorFolder) {
                                    // NEW: Link for Supervisor subfolders
                                    $folderViewLink = base_url('hrd/view-supervisor-folder/' . $subFolder['id']); // Needs to be defined in your routes
                                } elseif (isset($isManagerFolder) && $isManagerFolder) {
                                    // NEW: Link for Manager subfolders
                                    $folderViewLink = base_url('hrd/view-manager-folder/' . $subFolder['id']); // Needs to be defined in your routes
                                } elseif (isset($isDireksiFolder) && $isDireksiFolder) {
                                    // NEW: Link for Manager subfolders
                                    $folderViewLink = base_url('hrd/view-direksi-folder/' . $subFolder['id']); // Needs to be defined in your routes
                                } else {
                                    $folderViewLink = base_url('hrd/view-folder/' . $subFolder['id']);
                                }
                                ?>
                                <a href="<?= $folderViewLink ?>"
                                    class="block font-medium text-gray-900 hover:text-blue-700 hover:underline">
                                    <?= esc($subFolder['name']) ?>
                                </a>
                                <div class="text-gray-500 text-xs">
                                    <?= date('d M Y', strtotime($subFolder['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                        <button onclick="toggleMenu(this)"
                            class="text-gray-500 hover:text-gray-900 text-xl font-bold">⋮</button>
                    </div>
                <?php endforeach; ?>

                <?php foreach ($filesInFolder as $file): ?>
                    <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50" data-item-type="file"
                        data-item-id="<?= esc($file['id']) ?>" data-item-name="<?= esc($file['file_name']) ?>"
                        data-file-id="<?= esc($file['id']) ?>" data-file-name="<?= esc($file['file_name']) ?>"
                        data-file-size="<?= esc($file['file_size'] ?? '0') ?>"
                        data-file-type="<?= esc($file['file_type'] ?? '') ?>"
                        data-file-path="<?= esc($file['file_path'] ?? '') ?>"
                        data-file-owner-id="<?= esc($file['uploader_id'] ?? '') ?>"
                        data-file-owner-name="<?= esc($file['uploader_name'] ?? '') ?>"
                        data-file-created-at="<?= esc($file['created_at']) ?>"
                        data-file-updated-at="<?= esc($file['updated_at'] ?? '') ?>">
                        <div class="flex items-center space-x-4">
                            <?php
                            $fileExtension = pathinfo($file['file_name'], PATHINFO_EXTENSION);
                            $iconSrc = '';
                            switch (strtolower($fileExtension)) {
                                case 'pdf':
                                    $iconSrc = base_url('images/pdf.png');
                                    break;
                                case 'doc':
                                case 'docx':
                                    $iconSrc = base_url('images/word.png');
                                    break;
                                case 'xls':
                                case 'xlsx':
                                    $iconSrc = base_url('images/excel.png');
                                    break;
                                case 'pptx':
                                    $iconSrc = base_url('images/ppt.png');
                                    break;
                                default:
                                    $iconSrc = base_url('images/file-default.png');
                                    break;
                            }
                            ?>
                            <img src="<?= $iconSrc ?>" alt="File Icon" class="w-6 h-6">
                            <div>
                                <a href="<?= site_url('hrd/file/view/' . $file['id']) ?>" target="_blank"
                                    class="block h-full w-full text-sm text-gray-900 hover:text-blue-700 hover:underline">
                                    <?= esc($file['file_name']) ?>
                                </a>
                                <div class="text-gray-500 text-xs">
                                    <?= esc(round($file['file_size'] / 1024, 2)) ?> KB |
                                    <?= date('d M Y', strtotime($file['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                        <button onclick="toggleMenu(this)"
                            class="text-gray-500 hover:text-gray-900 text-xl font-bold">⋮</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="modalUploadFile"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-sm hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
        <h2 class="text-xl font-semibold mb-4">Upload File Baru</h2>
        <form id="uploadFileForm" enctype="multipart/form-data">
            <label for="fileInput" class="block text-sm font-medium text-gray-700">Pilih File</label>
            <input type="file" id="fileInput" name="file_upload" class="mt-1 block w-full text-sm text-gray-500
                file:mr-4 file:py-2 file:px-4
                file:rounded-full file:border-0
                file:text-sm file:font-semibold
                file:bg-blue-50 file:text-blue-700
                hover:file:bg-blue-100" />

            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" id="csrf_token_field">

            <input type="hidden" name="folder_id" id="folder_id_input" value="<?= esc($folderId ?? '') ?>">

            <div class="flex justify-end space-x-4 mt-6">
                <button type="button" id="cancelUploadFileModal"
                    class="text-blue-500 hover:text-blue-700">Batal</button>
                <button type="submit" id="submitUploadFile"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md">Upload</button>
            </div>
        </form>
    </div>
</div>

<div id="modalCreateFolder"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-sm hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
        <h2 class="text-xl font-semibold mb-4">Buat Folder Baru</h2>
        <label class="block text-sm font-medium">Nama Folder</label>
        <input type="text" id="newFolderNameInput" placeholder="Masukan nama folder"
            class="w-full border rounded-lg px-3 py-2 mb-4">
        <div class="flex justify-end space-x-4">
            <button id="cancelCreateFolderModal" class="text-blue-500 hover:text-blue-700">Batal</button>
            <button id="submitCreateFolder"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md">Buat</button>
        </div>
    </div>
</div>

<script>
    // Pastikan variabel ini ada untuk JS
    window.currentFolderId = <?= esc($folderId ?? 'null') ?>;
    window.currentFolderType = '<?= esc($folderType ?? 'personal') ?>';
    window.isCurrentFolderShared = <?= (isset($isShared) && $isShared) ? 'true' : 'false' ?>;
    window.currentUserId = <?= session()->get('user_id') ?? 'null' ?>;
    window.baseUrl = "<?php echo base_url('index.php/hrd/view-staff-folder/' . $folderId . '/'); ?>";

    // --- INITIALIZATION FOR NEW BUTTON AND MODALS ---
    document.addEventListener('DOMContentLoaded', function () {
        const newButton = document.getElementById('newButton');
        const newDropdown = document.getElementById('newDropdown');
        const newButtonMobile = document.getElementById('newButtonMobile');
        const newDropdownMobile = document.getElementById('newDropdownMobile');

        // Create Folder elements
        const createFolderLink = document.getElementById('createFolderLink');
        const createFolderLinkMobile = document.getElementById('createFolderLinkMobile');
        const modalCreateFolder = document.getElementById('modalCreateFolder');
        const cancelCreateFolderModal = document.getElementById('cancelCreateFolderModal');
        const submitCreateFolder = document.getElementById('submitCreateFolder');
        const newFolderNameInput = document.getElementById('newFolderNameInput');

        // Upload File elements
        const uploadFileLink = document.getElementById('uploadFileLink');
        const uploadFileLinkMobile = document.getElementById('uploadFileLinkMobile');
        const modalUploadFile = document.getElementById('modalUploadFile');
        const cancelUploadFileModal = document.getElementById('cancelUploadFileModal');
        const uploadFileForm = document.getElementById('uploadFileForm');
        const uploadFileInput = document.getElementById('fileInput');
        const folderIdInput = document.getElementById('folder_id_input');

        // --- DROPDOWN "BARU" LOGIC ---
        if (newButton) {
            newButton.addEventListener('click', function (event) {
                event.stopPropagation();
                newDropdown.classList.toggle('hidden');
            });
        }

        // Click outside to close dropdown
        document.addEventListener('click', function (event) {
            if (newDropdown && !newDropdown.contains(event.target) && !newButton.contains(event.target)) {
                newDropdown.classList.add('hidden');
            }
        });

        if (newButtonMobile) { // Periksa keberadaan tombol
            newButtonMobile.addEventListener('click', function (event) {
                event.stopPropagation();
                newDropdownMobile.classList.toggle('hidden');
            });
        }

        document.addEventListener('click', function (event) {
            // Pastikan newButton ada sebelum mencoba contains
            if (newButtonMobile && !newButtonMobile.contains(event.target) && newDropdownMobile && !newDropdownMobile.contains(event.target)) {
                newDropdownMobile.classList.add('hidden');
            }
        });

        // --- MODAL "BUAT FOLDER BARU" LOGIC ---
        if (createFolderLink) {
            createFolderLink.addEventListener('click', function (event) {
                event.preventDefault();
                newDropdown.classList.add('hidden');
                modalCreateFolder.classList.remove('hidden');
            });
        }

        if (createFolderLinkMobile) {
            createFolderLinkMobile.addEventListener('click', function (event) {
                event.preventDefault();
                newDropdownMobile.classList.add('hidden');
                modalCreateFolder.classList.remove('hidden');
            });
        }

        if (cancelCreateFolderModal) {
            cancelCreateFolderModal.addEventListener('click', function () {
                modalCreateFolder.classList.add('hidden');
                newFolderNameInput.value = '';
            });
        }

        if (submitCreateFolder) {
            submitCreateFolder.addEventListener('click', function () {
                const folderName = newFolderNameInput.value.trim();
                if (folderName === '') {
                    alert('Nama folder tidak boleh kosong!');
                    return;
                }

                let folderTypeToSend = window.currentFolderType;
                let accessRolesToSend = [];

                if (window.currentFolderId === null || window.currentFolderId === 'null') {
                    if (folderTypeToSend === 'shared') {
                        accessRolesToSend = ["5", "2"];
                    }
                }

                // Ambil CSRF Token
                const csrfToken = document.querySelector('input[name="<?= csrf_token() ?>"]').value;

                fetch(`<?= site_url('hrd/create-folder') ?>`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        name: folderName,
                        parent_id: window.currentFolderId,
                        ...(window.currentFolderId === null || window.currentFolderId === 'null' ? { folder_type: folderTypeToSend } : {}),
                        ...(window.currentFolderId === null || window.currentFolderId === 'null' && folderTypeToSend === 'shared' ? { access_roles: accessRolesToSend } : {})
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert(data.message);
                            window.location.reload();
                        } else {
                            alert('Gagal membuat folder: ' + (data.message || 'Terjadi kesalahan.'));
                        }
                    })
                    .catch(error => console.error('Error creating folder:', error));
            });
        }

        // --- MODAL "UPLOAD FILE" LOGIC ---
        if (uploadFileLink) {
            uploadFileLink.addEventListener('click', function (event) {
                event.preventDefault();
                newDropdown.classList.add('hidden');
                modalUploadFile.classList.remove('hidden');

                // Atur folder_id pada input tersembunyi
                if (window.currentFolderId !== null && window.currentFolderId !== 'null') {
                    folderIdInput.value = window.currentFolderId;
                } else {
                    folderIdInput.value = '';
                }
            });
        }

        if (uploadFileLinkMobile) {
            uploadFileLinkMobile.addEventListener('click', function (event) {
                event.preventDefault();
                newDropdownMobile.classList.add('hidden');
                modalUploadFile.classList.remove('hidden');

                // Atur folder_id pada input tersembunyi
                if (window.currentFolderId !== null && window.currentFolderId !== 'null') {
                    folderIdInput.value = window.currentFolderId;
                } else {
                    folderIdInput.value = '';
                }
            });
        }

        if (cancelUploadFileModal) {
            cancelUploadFileModal.addEventListener('click', function () {
                modalUploadFile.classList.add('hidden');
                // Reset input file
                uploadFileInput.value = '';
            });
        }

        // Menggunakan event 'submit' pada form untuk upload file
        if (uploadFileForm) {
            uploadFileForm.addEventListener('submit', function (event) {
                event.preventDefault();

                if (uploadFileInput.files.length === 0) {
                    alert('Silakan pilih file untuk diunggah.');
                    return;
                }

                // Ambil data dari form secara otomatis
                const formData = new FormData(uploadFileForm);

                // Fetch request
                fetch(`<?= site_url('hrd/uploadFile') ?>`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert(data.message);
                            window.location.reload();
                        } else {
                            alert('Gagal mengunggah file: ' + (data.message || (data.errors ? Object.values(data.errors).join('\n') : 'Terjadi kesalahan.')));
                            console.error('Upload Error Details:', data.errors);
                        }
                    })
                    .catch(error => {
                        console.error('Error uploading file:', error);
                        alert('Terjadi kesalahan saat mengunggah file.');
                    });
            });
        }

        // --- CONTEXT MENU LOGIC ---
        const contextMenu = document.createElement('div');
        contextMenu.id = 'contextMenu';
        contextMenu.className = 'absolute z-50 bg-white border border-gray-200 rounded-lg shadow-lg hidden';
        document.body.appendChild(contextMenu);

        document.addEventListener('click', () => {
            contextMenu.classList.add('hidden');
        });

        document.addEventListener('contextmenu', function (event) {
            const row = event.target.closest('tr.item-with-context-menu');
            if (row) {
                event.preventDefault();

                const itemType = row.dataset.itemType;
                const itemId = row.dataset.itemId;
                const itemName = row.dataset.itemName;

                contextMenu.innerHTML = ''; // Clear previous menu

                const items = [];
                if (itemType === 'folder') {
                    // Items for folder
                    items.push({ text: '📁 Buka', action: `window.location.href='<?= site_url('hrd/view-staff-folder/') ?>${itemId}'` });
                    items.push({ text: '✏️ Ubah Nama', action: `renameItem('${itemType}', '${itemId}', '${itemName}')` });
                    items.push({ text: '🗑️ Hapus', action: `deleteItem('${itemType}', '${itemId}', '${itemName}')` });
                    items.push({ text: '🔗 Bagikan', action: `shareFolder('${itemId}', '${itemName}')` });
                } else if (itemType === 'file') {
                    // Items for file
                    items.push({ text: '👀 Lihat', action: `window.open('<?= site_url('hrd/file/view/') ?>${itemId}', '_blank')` });
                    items.push({ text: '⬇️ Unduh', action: `window.location.href='<?= site_url('hrd/file/download/') ?>${itemId}'` });
                    items.push({ text: '✏️ Ubah Nama', action: `renameItem('${itemType}', '${itemId}', '${itemName}')` });
                    items.push({ text: '🗑️ Hapus', action: `deleteItem('${itemType}', '${itemId}', '${itemName}')` });
                }

                items.forEach(item => {
                    const button = document.createElement('button');
                    button.className = 'block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100';
                    button.textContent = item.text;
                    button.setAttribute('onclick', item.action);
                    contextMenu.appendChild(button);
                });

                contextMenu.style.top = `${event.clientY}px`;
                contextMenu.style.left = `${event.clientX}px`;
                contextMenu.classList.remove('hidden');
            } else {
                contextMenu.classList.add('hidden');
            }
        });

        // --- CONTEXT MENU FUNCTIONS ---
        window.renameItem = function (type, id, oldName) {
            contextMenu.classList.add('hidden');
            const newName = prompt(`Ubah nama ${type} "${oldName}" menjadi:`, oldName);
            if (newName && newName.trim() !== '' && newName.trim() !== oldName) {
                const csrfToken = document.getElementById('csrf_token_field').value;
                const url = type === 'folder' ? `<?= site_url('hrd/rename-folder/') ?>${id}` : `<?= site_url('hrd/rename-file/') ?>${id}`;
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ new_name: newName.trim() })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert(data.message);
                            window.location.reload();
                        } else {
                            alert('Gagal mengubah nama: ' + (data.message || 'Terjadi kesalahan.'));
                        }
                    })
                    .catch(error => console.error('Error renaming item:', error));
            }
        };

        window.deleteItem = function (type, id, name) {
            contextMenu.classList.add('hidden');
            if (confirm(`Apakah Anda yakin ingin menghapus ${type} "${name}"?`)) {
                const csrfToken = document.getElementById('csrf_token_field').value;
                const url = type === 'folder' ? `<?= site_url('hrd/delete-folder/') ?>${id}` : `<?= site_url('hrd/delete-file/') ?>${id}`;
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert(data.message);
                            window.location.reload();
                        } else {
                            alert('Gagal menghapus: ' + (data.message || 'Terjadi kesalahan.'));
                        }
                    })
                    .catch(error => console.error('Error deleting item:', error));
            }
        };

        // Tambahkan fungsi shareFolder jika diperlukan
        window.shareFolder = function (id, name) {
            contextMenu.classList.add('hidden');
            alert(`Fungsi berbagi untuk folder "${name}" (ID: ${id}) akan datang.`);
        };
    });
</script>
<?= $this->endSection() ?>