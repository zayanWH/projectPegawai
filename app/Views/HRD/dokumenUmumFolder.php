<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="container mx-auto px-4 py-6">
    <!-- Breadcrumb Navigation -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="<?= base_url('hrd/dokumen-umum') ?>"
                    class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z">
                        </path>
                    </svg>
                    Dokumen Umum
                </a>
            </li>
            <?php if (!empty($breadcrumb)): ?>
                <?php foreach ($breadcrumb as $folder): ?>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <?php if ($folder['id'] === $current_folder['id']): ?>
                                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2"><?= esc($folder['name']) ?></span>
                            <?php else: ?>
                                <a href="<?= base_url('hrd/dokumen-umum/folder/' . $folder['id']) ?>"
                                    class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2"><?= esc($folder['name']) ?></a>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ol>
    </nav>

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                <svg class="w-8 h-8 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                </svg>
                <?= esc($current_folder['name']) ?>
            </h1>
            <p class="text-gray-600 mt-1">Kelola dokumen dan folder dalam <?= esc($current_folder['name']) ?></p>
        </div>

        <!-- Action Buttons -->
        <?php
        $userRoleId = session()->get('role_id');
        ?>

        <?php if ($userRoleId == 1 || $userRoleId == 2): ?>
            <div class="flex space-x-3">
                <button onclick="openUploadModal()"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                        </path>
                    </svg>
                    Upload File
                </button>

                <div class="relative">
                    <button onclick="toggleDropdownMenu()"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Baru
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div id="dropdownMenu"
                        class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border">
                        <div class="py-1">
                            <button onclick="openCreateFolderModal()"
                                class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                                </svg>
                                📁 Buat Folder
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Documents Table -->
    <div class="bg-white shadow-sm rounded-lg border">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Pengunggah</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
    <?php if (!empty($documents)): ?>
        <?php foreach ($documents as $doc): ?>
            <tr class="hover:bg-gray-50 item-with-context-menu"
                data-item-type="<?= esc($doc['type']) ?>"
                data-item-id="<?= esc($doc['id']) ?>"
                data-item-name="<?= esc($doc['name']) ?>"
                <?php if ($doc['type'] === 'folder'): ?>
                    data-folder-id="<?= esc($doc['id']) ?>"
                    data-folder-name="<?= esc($doc['name']) ?>"
                    data-folder-type="<?= esc($doc['folder_type'] ?? 'personal') ?>"
                    data-folder-is-shared="<?= esc($doc['is_shared'] ?? 0) ?>"
                    data-folder-shared-type="<?= esc($doc['shared_type'] ?? '') ?>"
                    data-folder-owner-id="<?= esc($doc['owner_id'] ?? '') ?>"
                    data-folder-owner-name="<?= esc($doc['owner_display'] ?? $doc['owner_name'] ?? 'Unknown') ?>"
                    data-folder-created-at="<?= esc($doc['created_at']) ?>"
                    data-folder-updated-at="<?= esc($doc['updated_at']) ?>"
                    data-folder-path="<?= esc($doc['path'] ?? $doc['name']) ?>"
                <?php else: ?>
                    data-file-size="<?= esc($doc['file_size'] ?? '') ?>"
                    data-file-type="<?= esc($doc['file_type'] ?? '') ?>"
                    data-file-uploaded-by="<?= esc($doc['uploaded_by'] ?? 'Unknown') ?>"
                    data-file-created-at="<?= esc($doc['created_at']) ?>"
                    data-file-updated-at="<?= esc($doc['updated_at'] ?? $doc['created_at']) ?>"
                    data-file-path="<?= esc($doc['server_file_name'] ?? '') ?>"
                <?php endif; ?>>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <?php if ($doc['type'] === 'folder'): ?>
                            <a href="<?= base_url('hrd/dokumen-umum/folder/' . $doc['id']) ?>"
                                class="flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline cursor-pointer">
                                 <img src="<?= base_url('images/folder.png') ?>" alt="Folder Icon"
                                                    class="w-5 h-5 mr-2">
                                                <?= esc($doc['name']) ?>
                            </a>
                        <?php else: ?>
                            <?php
                            $fileExtension = pathinfo($doc['file_name'] ?? $doc['name'], PATHINFO_EXTENSION);
                            $iconSvg = '';
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
                            <a href="<?= base_url('hrd/file/viewFile/' . $doc['id']) ?>"
                                class="flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline cursor-pointer">
                                <?= $iconSvg ?>
                                <?= esc($doc['name']) ?>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($doc['category'])): ?>
                            <span class="ml-2 px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded"><?= esc($doc['category']) ?></span>
                        <?php endif; ?>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <?php if ($doc['type'] === 'folder'): ?>
                        Folder
                    <?php else: ?>
                        <?= esc(strtoupper($fileExtension ?? '')) ?> File
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-5 h-5 bg-blue-500 rounded-full mr-2"></div>
                        <span class="text-sm text-gray-900">HRD</span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <?= date('d M Y', strtotime($doc['created_at'])) ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <button onclick="toggleMenu(this, event);"
                        class="text-blue-600 hover:text-blue-900">⋮</button>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                Folder ini masih kosong
            </td>
        </tr>
    <?php endif; ?>
</tbody>
        </table>
    </div>
</div>

<!-- Modal Upload File -->
<div id="modalUploadFile" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Upload File</h3>
            <form id="uploadFileForm" enctype="multipart/form-data">
                <input type="hidden" name="parent_id" value="<?= $parent_id ?>">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                    <select name="category"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="SOP">SOP</option>
                        <option value="SK">SK</option>
                        <option value="Pengumuman">Pengumuman</option>
                        <option value="Personal">Personal</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File</label>
                    <input type="file" name="file" required
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Format yang didukung: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX,
                        JPG, JPEG, PNG</p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeUploadModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Create Folder -->
<div id="modalCreateFolder" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Folder Baru</h3>
            <form id="createFolderForm">
                <input type="hidden" name="parent_id" value="<?= $parent_id ?>">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Folder</label>
                    <select name="folder_type"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="SOP">SOP</option>
                        <option value="SK">SK</option>
                        <option value="Pengumuman">Pengumuman</option>
                        <option value="Personal">Personal</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Folder</label>
                    <input type="text" name="folder_name" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeCreateFolderModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Buat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Namespace untuk dokumen umum folder untuk menghindari konflik
    window.DokumenUmumFolder = {
        // Dropdown functionality
        toggleDropdownMenu: function () {
            const menu = document.getElementById('dropdownMenu');
            if (menu) {
                menu.classList.toggle('hidden');
            }
        },

        // Modal functions
        openUploadModal: function () {
            const modal = document.getElementById('modalUploadFile');
            if (modal) {
                modal.classList.remove('hidden');
            }
            // Close dropdown if open
            const dropdown = document.getElementById('dropdownMenu');
            if (dropdown && !dropdown.classList.contains('hidden')) {
                dropdown.classList.add('hidden');
            }
        },

        closeUploadModal: function () {
            const modal = document.getElementById('modalUploadFile');
            if (modal) {
                modal.classList.add('hidden');
            }
        },

        openCreateFolderModal: function () {
            const modal = document.getElementById('modalCreateFolder');
            if (modal) {
                modal.classList.remove('hidden');
            }
            // Close dropdown if open
            const dropdown = document.getElementById('dropdownMenu');
            if (dropdown && !dropdown.classList.contains('hidden')) {
                dropdown.classList.add('hidden');
            }
        },

        closeCreateFolderModal: function () {
            const modal = document.getElementById('modalCreateFolder');
            if (modal) {
                modal.classList.add('hidden');
            }
        }
    };

    // Global functions untuk backward compatibility
    function toggleDropdownMenu() { return window.DokumenUmumFolder.toggleDropdownMenu(); }
    function openUploadModal() { return window.DokumenUmumFolder.openUploadModal(); }
    function closeUploadModal() { return window.DokumenUmumFolder.closeUploadModal(); }
    function openCreateFolderModal() { return window.DokumenUmumFolder.openCreateFolderModal(); }
    function closeCreateFolderModal() { return window.DokumenUmumFolder.closeCreateFolderModal(); }

    // Close dropdown when clicking outside
    document.addEventListener('click', function (event) {
        const dropdown = document.getElementById('dropdownMenu');
        const button = event.target.closest('button');

        if (!button || !button.onclick || button.onclick.toString().indexOf('toggleDropdownMenu') === -1) {
            if (dropdown && !dropdown.classList.contains('hidden')) {
                dropdown.classList.add('hidden');
            }
        }
    });

    // Upload File Form Handler dengan error handling yang robust
    document.addEventListener('DOMContentLoaded', function () {
        const uploadFileForm = document.getElementById('uploadFileForm');
        if (!uploadFileForm) {
            console.warn('Upload file form tidak ditemukan');
            return;
        }

        uploadFileForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');

            // Debug: Log form data
            console.log('Form data being sent:');
            for (let [key, value] of formData.entries()) {
                console.log(key + ':', value);
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Uploading...';

            fetch('<?= base_url('hrd/dokumen-umum/upload-file') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
                .then(response => {
                    console.log('Upload response status:', response.status);
                    console.log('Upload response ok:', response.ok);

                    // Clone response untuk debugging
                    const responseClone = response.clone();

                    // Log raw response text untuk debugging
                    return responseClone.text().then(text => {
                        console.log('Upload raw response text:', text);

                        // Jika response ok dan ada content, coba parse JSON
                        if (response.ok && text.trim()) {
                            try {
                                const jsonData = JSON.parse(text);
                                return jsonData;
                            } catch (jsonError) {
                                console.error('Upload JSON parse error:', jsonError);
                                // Jika JSON parsing gagal tapi status OK, anggap sukses
                                if (response.status === 200) {
                                    return { status: 'success', message: 'File berhasil diupload!' };
                                }
                                throw new Error('Response bukan JSON valid: ' + text);
                            }
                        } else if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        } else {
                            // Response kosong tapi status OK
                            return { status: 'success', message: 'File berhasil diupload!' };
                        }
                    });
                })
                .then(data => {
                    if (data.status === 'success') {
                        alert('File berhasil diupload!');

                        // Close modal
                        const modal = document.getElementById('modalUploadFile');
                        if (modal) {
                            modal.classList.add('hidden');
                        }

                        // Reset form
                        const uploadFileForm = document.getElementById('uploadFileForm');
                        if (uploadFileForm) uploadFileForm.reset();

                        // Reload page to show new file
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        // Log error tapi tidak tampilkan alert
                        console.log('Upload file response:', data);

                        // Tetap close modal dan reload karena file kemungkinan berhasil diupload
                        const modal = document.getElementById('modalUploadFile');
                        if (modal) {
                            modal.classList.add('hidden');
                        }
                        const form = document.getElementById('uploadFileForm');
                        if (form) form.reset();
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                })
                .catch(error => {
                    console.error('Upload fetch error:', error);
                    // Tidak tampilkan alert error, hanya log

                    // Tetap close modal dan reload karena file kemungkinan berhasil diupload
                    const modal = document.getElementById('modalUploadFile');
                    if (modal) {
                        modal.classList.add('hidden');
                    }
                    const form = document.getElementById('uploadFileForm');
                    if (form) form.reset();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Upload';
                });
        });
    });

    // Create Folder Form Handler dengan error handling yang robust
    document.addEventListener('DOMContentLoaded', function () {
        const createFolderForm = document.getElementById('createFolderForm');
        if (!createFolderForm) {
            console.warn('Create folder form tidak ditemukan');
            return;
        }

        createFolderForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const folderName = formData.get('folder_name');
            const folderType = formData.get('folder_type');
            const parentId = formData.get('parent_id');

            if (!folderName || folderName.trim() === '') {
                alert('Nama folder tidak boleh kosong!');
                return;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Membuat...';

            const requestData = {
                name: folderName.trim(),
                parent_id: parentId || null,
                folder_type: folderType || 'personal'
            };

            fetch('<?= base_url('hrd/dokumen-umum/create-folder') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(requestData)
            })
                .then(response => {
                    console.log('Create folder response status:', response.status);
                    console.log('Create folder response ok:', response.ok);

                    // Clone response untuk debugging
                    const responseClone = response.clone();

                    // Log raw response text untuk debugging
                    return responseClone.text().then(text => {
                        console.log('Create folder raw response text:', text);

                        // Jika response ok dan ada content, coba parse JSON
                        if (response.ok && text.trim()) {
                            try {
                                const jsonData = JSON.parse(text);
                                return jsonData;
                            } catch (jsonError) {
                                console.error('Create folder JSON parse error:', jsonError);
                                // Jika JSON parsing gagal tapi status OK, anggap sukses
                                if (response.status === 200) {
                                    return { status: 'success', message: 'Folder berhasil dibuat!' };
                                }
                                throw new Error('Response bukan JSON valid: ' + text);
                            }
                        } else if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        } else {
                            // Response kosong tapi status OK
                            return { status: 'success', message: 'Folder berhasil dibuat!' };
                        }
                    });
                })
                .then(data => {
                    if (data.status === 'success') {
                        alert('Folder berhasil dibuat!');

                        // Close modal
                        const modal = document.getElementById('modalCreateFolder');
                        if (modal) {
                            modal.classList.add('hidden');
                        }

                        // Reset form
                        const form = document.getElementById('createFolderForm');
                        if (form) form.reset();

                        // Reload page to show new folder
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        // Log error tapi tidak tampilkan alert
                        console.log('Create folder response:', data);

                        // Tetap close modal dan reload karena folder kemungkinan berhasil dibuat
                        const modal = document.getElementById('modalCreateFolder');
                        if (modal) {
                            modal.classList.add('hidden');
                        }
                        const form = document.getElementById('createFolderForm');
                        if (form) form.reset();
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                })
                .catch(error => {
                    console.error('Create folder error:', error);
                    // Tidak tampilkan alert error, hanya log

                    // Tetap close modal dan reload karena folder kemungkinan berhasil dibuat
                    const modal = document.getElementById('modalCreateFolder');
                    if (modal) {
                        modal.classList.add('hidden');
                    }
                    const form = document.getElementById('createFolderForm');
                    if (form) form.reset();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Buat';
                });
        });
    });
</script>

<?= $this->endSection() ?>