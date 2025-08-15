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
                    // Tentukan root breadcrumb
                    $rootUrl = '';
                    $rootName = '';

                    if (isset($isStaffFolder) && $isStaffFolder) {
                        $rootUrl = site_url('supervisor/dokumen-staff');
                        $rootName = 'Dokumen Staff'; // Selalu tampilkan 'Dokumen Staff' sebagai root
                    } else {
                        $rootUrl = site_url('supervisor/dokumen-supervisor');
                        $rootName = 'Dokumen Saya';
                    }
                    ?>
                    <a href="<?= $rootUrl ?>" class="text-blue-600 hover:text-blue-800"><?= esc($rootName) ?></a>

                    <?php if (!empty($breadcrumbs)): ?>
                        <?php
                        // Jika ini adalah folder staff, kita ingin melewati breadcrumb pertama
                        // jika breadcrumb pertama adalah "Dokumen Staff" itu sendiri,
                        // karena sudah ditangani sebagai root di atas.
                        $startIndex = 0;
                        if (isset($isStaffFolder) && $isStaffFolder && !empty($breadcrumbs) && $breadcrumbs[0]['name'] === 'Dokumen Staff') {
                            $startIndex = 1;
                        }
                        ?>
                        <?php for ($i = $startIndex; $i < count($breadcrumbs); $i++): ?>
                            <?php $breadcrumb = $breadcrumbs[$i]; ?>
                            <span class="text-gray-500">/</span>
                            <?php if ($breadcrumb['id'] !== null && isset($breadcrumb['url'])): ?>
                                <a href="<?= $breadcrumb['url'] ?>"
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

    <?php if (isset($canManageFolder) && $canManageFolder): ?>
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
                <a href="#" id="uploadFileLink" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">⬆️ Upload File</a>
            </div>
        </div>
    <?php endif; ?>

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
                                        <a href="<?= (isset($isStaffFolder) && $isStaffFolder) ? base_url('supervisor/view-staff-folder/' . $subFolder['id']) : base_url('supervisor/folder/' . $subFolder['id']) ?>"
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
                                        <?php if (isset($canManageFolder) && $canManageFolder): ?>
                                            <button onclick="toggleMenu(this);" class="text-blue-600 hover:text-blue-900">⋮</button>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (!empty($filesInFolder)): ?>
                            <?php foreach ($filesInFolder as $file): ?>
                                <tr class="hover:bg-gray-50 item-with-context-menu" data-item-type="file"
                                    data-item-id="<?= esc($file['id']) ?>" data-item-name="<?= esc($file['file_name']) ?>"
                                    data-file-id="<?= esc($file['id']) ?>" data-file-name="<?= esc($file['file_name']) ?>"
                                    data-file-size="<?= esc($file['file_size'] ?? '0') ?>"
                                    data-file-type="<?= esc($file['file_type'] ?? '') ?>"
                                    data-file-path="<?= esc($file['file_path'] ?? '') ?>"
                                    data-file-owner-id="<?= esc($file['uploader_id'] ?? '') ?>"
                                    data-file-owner-name="<?= esc($file['uploader_name'] ?? '') ?>"
                                    data-file-created-at="<?= esc($file['created_at']) ?>"
                                    data-file-updated-at="<?= esc($file['updated_at'] ?? '') ?>">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="<?= base_url('supervisor/view-file/' . $file['id']) ?>" target="_blank"
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
                                        <?php if (isset($canManageFolder) && $canManageFolder): ?>
                                            <button onclick="toggleMenu(this, event);"
                                                class="text-blue-600 hover:text-blue-900">⋮</button>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
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
    <?php if (isset($canManageFolder) && $canManageFolder): ?>
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
                <a href="#" id="uploadFileLinkMobile" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">⬆️ Upload
                    File</a>
            </div>
        </div>
    <?php endif; ?>

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
                                <a href="<?= (isset($isStaffFolder) && $isStaffFolder) ? base_url('supervisor/view-staff-folder/' . $subFolder['id']) : base_url('supervisor/folder/' . $subFolder['id']) ?>"
                                    class="block font-medium text-gray-900 hover:text-blue-700 hover:underline">
                                    <?= esc($subFolder['name']) ?>
                                </a>
                                <div class="text-gray-500 text-xs">
                                    <?= date('d M Y', strtotime($subFolder['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                        <?php if (isset($canManageFolder) && $canManageFolder): ?>
                            <button onclick="toggleMenu(this, event);" class="text-blue-600 hover:text-blue-900">⋮</button>
                        <?php else: ?>
                            <span class="text-gray-400">-</span>
                        <?php endif; ?>
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
                            <img src="<?= $iconSrc ?>" alt="File Icon" class="w-6 h-6">
                            <div>
                                <a href="<?= base_url('supervisor/view-file/' . $file['id']) ?>"
                                    class="block font-medium text-gray-900 hover:text-blue-700 hover:underline">
                                    <?= esc($file['file_name']) ?>
                                </a>
                                <div class="text-gray-500 text-xs">
                                    <?= esc(round($file['file_size'] / 1024, 2)) ?> KB
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
        <input type="file" id="uploadFileInput" class="w-full border rounded-lg px-3 py-2 mb-4">
        <div class="flex justify-end space-x-4">
            <button id="cancelUploadFileModal" class="text-blue-500 hover:text-blue-700">Batal</button>
            <button id="submitUploadFile"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md">Upload</button>
        </div>
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
    // Pastikan variabel ini didefinisikan
    window.currentFolderId = <?= esc($folderId ?? 'null') ?>;
    window.currentFolderType = '<?= esc($folderType ?? 'personal') ?>';
    window.isCurrentFolderShared = <?= (isset($isShared) && $isShared) ? 'true' : 'false' ?>;
    window.currentUserId = <?= session()->get('user_id') ?? 'null' ?>;
    // Tambahkan variabel baru untuk JS
    window.canManageFolder = <?= (isset($canManageFolder) && $canManageFolder) ? 'true' : 'false' ?>;


    function setSelectedFolderId(id) {
        window.selectedFolderId = id;
    }

    document.addEventListener('DOMContentLoaded', function () {
        // --- ALL ELEMENTS ---
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
        const submitUploadFile = document.getElementById('submitUploadFile');
        const uploadFileInput = document.getElementById('uploadFileInput');

        // --- DROPDOWN "BARU" LOGIC (hanya inisialisasi jika tombol ada) ---
        if (newButton) { // Periksa keberadaan tombol
            newButton.addEventListener('click', function (event) {
                event.stopPropagation();
                newDropdown.classList.toggle('hidden');
            });
        }

        document.addEventListener('click', function (event) {
            // Pastikan newButton ada sebelum mencoba contains
            if (newButton && !newButton.contains(event.target) && newDropdown && !newDropdown.contains(event.target)) {
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

        // --- MODAL "BUAT FOLDER BARU" LOGIC (hanya inisialisasi jika link ada) ---
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
            });
        }

        if (submitCreateFolder) {
            submitCreateFolder.addEventListener('click', function () {
                const folderName = newFolderNameInput.value.trim();
                if (folderName === '') {
                    alert('Nama folder tidak boleh kosong!');
                    return;
                }

                fetch('<?= esc(site_url('supervisor/create-folder')) ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        name: folderName,
                        parent_id: window.currentFolderId,
                        folder_type: window.currentFolderType // Kirim juga folder_type
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


        // --- MODAL "UPLOAD FILE" LOGIC (hanya inisialisasi jika link ada) ---
        if (uploadFileLink) {
            uploadFileLink.addEventListener('click', function (event) {
                event.preventDefault();
                newDropdown.classList.add('hidden');
                modalUploadFile.classList.remove('hidden');
            });
        }

        if (uploadFileLinkMobile) {
            uploadFileLinkMobile.addEventListener('click', function (event) {
                event.preventDefault();
                newDropdownMobile.classList.add('hidden');
                modalUploadFile.classList.remove('hidden');
            });
        }

        if (cancelUploadFileModal) {
            cancelUploadFileModal.addEventListener('click', function () {
                modalUploadFile.classList.add('hidden');
            });
        }

        if (submitUploadFile) {
            submitUploadFile.addEventListener('click', function () {
                if (uploadFileInput.files.length === 0) {
                    alert('Silakan pilih file untuk diunggah.');
                    return;
                }
                const file = uploadFileInput.files[0];
                const formData = new FormData();
                formData.append('file_upload', file);
                formData.append('folder_id', window.currentFolderId);

                // UBAH URL INI KE ROUTE SUPERVISOR UNTUK UPLOAD
                fetch('<?= esc(site_url('supervisor/upload-file')) ?>', {
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
                            alert('Gagal mengunggah file: ' + (data.message || 'Terjadi kesalahan.'));
                        }
                    })
                    .catch(error => console.error('Error uploading file:', error));
            });
        }
    });
</script>

<?= $this->endsection() ?>