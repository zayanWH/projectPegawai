<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-semibold text-gray-800">Dokumen Supervisor</h1>
        </div>
        <div class="flex items-center space-x-4">
            <div class="relative">
                <input type="text"
                    id="searchInput" placeholder="Masukkan file dokumen..."
                    class="w-80 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <div id="searchResults" class="absolute z-20 w-80 bg-white border border-gray-200 rounded-lg shadow-lg mt-1 hidden">
                </div>
            </div>
            <img src="<?= base_url('images/logo.png') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm mt-4">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
            </svg>
            Folder Supervisor
        </h2>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-blue-600">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama Folder</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Dibuat Oleh</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tipe Folder</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tanggal Dibuat</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($personalFolders)): ?>
                    <?php foreach ($personalFolders as $folder): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="<?= base_url('manager/view-supervisor-folder/' . $folder['id']) ?>"
                                    class="block h-full w-full text-sm text-gray-900 hover:text-blue-700 hover:underline">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                                        </svg>
                                        <?= esc($folder['name']) ?>
                                    </div>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= esc($folder['owner_email'] ?? '') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= esc(ucfirst($folder['folder_type'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= date('d M Y', strtotime($folder['created_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                            Belum ada folder yang tersedia
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (!empty($orphanFiles)): ?>
    <div class="bg-white rounded-lg shadow-sm mt-6">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                File Tanpa Folder Pribadi
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama File</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Jenis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Ukuran</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tanggal Unggah</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($orphanFiles as $file): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php
                                    $fileExtension = pathinfo($file['file_name'], PATHINFO_EXTENSION);
                                    $iconSvg = '';
                                    switch (strtolower($fileExtension)) {
                                        case 'pdf':
                                            $iconSvg = '<svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path></svg>';
                                            break;
                                        case 'doc':
                                        case 'docx':
                                            $iconSvg = '<svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a2 2 0 00-2 2v4a2 2 0 002 2h2a2 2 0 002-2V4a2 2 0 00-2-2H9z"></path><path d="M5 9a2 2 0 00-2 2v4a2 2 0 002 2h10a2 2 0 002-2v-4a2 2 0 00-2-2H5z"></path></svg>';
                                            break;
                                        case 'xls':
                                        case 'xlsx':
                                            $iconSvg = '<svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4h12a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zM6 8v6h2V8H6zm4 0v6h2V8h-2zm4 0v6h2V8h-2z"></path></svg>';
                                            break;
                                        case 'png':
                                        case 'jpg':
                                        case 'jpeg':
                                        case 'gif':
                                            $iconSvg = '<svg class="w-5 h-5 text-purple-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-4 4 4 4-4v4z" clip-rule="evenodd"></path></svg>';
                                            break;
                                        default:
                                            $iconSvg = '<svg class="w-5 h-5 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path></svg>';
                                            break;
                                    }
                                    echo $iconSvg;
                                    ?>
                                    <span class="text-sm text-gray-900"><?= esc($file['original_name'] ?? $file['file_name']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= esc(strtoupper($fileExtension)) ?> File
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= esc(round($file['file_size'] / 1024, 2)) ?> KB
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= date('d M Y', strtotime($file['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <a href="<?= base_url('supervisor/download-my-file/' . $file['id']) ?>"
                                    class="text-blue-600 hover:text-blue-900 mr-2">Unduh</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Global Variables (Ensure these are passed from Controller) ---
        // Example in Controller:
        // $data['currentFolderId'] = $folderId ?? null;
        // $data['currentUserId'] = $this->session->get('user_id');
        // $data['userRoleName'] = 'Supervisor';
        window.currentFolderId = <?= json_encode($currentFolderId ?? null) ?>;
        window.currentUserId = <?= json_encode($currentUserId ?? null) ?>;
        window.currentUserRole = <?= json_encode($userRoleName ?? null) ?>;

        // --- Elements for "Baru" Dropdown and "Create Folder" Modal ---
        const dropdownButton = document.getElementById('dropdownButton');
        const dropdownMenu = document.getElementById('dropdownMenu');
        const openCreateFolder = document.getElementById('openCreateFolder');
        const modalCreateFolder = document.getElementById('modalCreateFolder');
        const cancelModal = document.getElementById('cancelModal');
        const createFolderBtn = document.getElementById('createFolderBtn');
        const folderNameInput = document.getElementById('folderName');
        const folderTypeSelect = document.getElementById('folderType');
        const folderAccessSelect = document.getElementById('folderAccess');
        const accessRolesContainer = document.getElementById('accessRolesContainer');
        const accessRolesCheckboxes = document.querySelectorAll('input[name="accessRoles[]"]');

        // --- Elements for "Upload File" Modal ---
        const openUploadFile = document.getElementById('openUploadFile');
        const modalUploadFile = document.getElementById('modalUploadFile');
        const cancelUploadModal = document.getElementById('cancelUploadModal');
        const uploadFileBtn = document.getElementById('uploadFileBtn');
        const fileInput = document.getElementById('fileInput');
        const fileDescription = document.getElementById('fileDescription');

        // --- Element for "Upload Folder" ---
        const openUploadFolder = document.getElementById('openUploadFolder');
        const folderUploadInput = document.getElementById('folderUploadInput'); // Make sure this input is in your HTML with webkitdirectory attribute

        // --- Search Elements ---
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');


        // --- CORE FUNCTIONS FOR DROPDOWNS AND MODALS ---

        /**
         * Shows a dropdown menu with transition effects.
         * @param {HTMLElement} element The dropdown menu element.
         */
        function showDropdown(element) {
            element.classList.remove('invisible', 'opacity-0', 'scale-95');
            element.classList.add('opacity-100', 'scale-100');
        }

        /**
         * Hides a dropdown menu with transition effects.
         * @param {HTMLElement} element The dropdown menu element.
         */
        function hideDropdown(element) {
            element.classList.remove('opacity-100', 'scale-100');
            element.classList.add('opacity-0', 'scale-95');
            // Use a timeout to fully hide the element AFTER the transition finishes
            // This prevents immediate clicks on underlying elements
            setTimeout(() => {
                element.classList.add('invisible');
            }, 200); // Must match your transition duration
        }

        /**
         * Shows a modal.
         * @param {HTMLElement} modalElement The modal element.
         */
        function showModal(modalElement) {
            modalElement.classList.remove('hidden');
        }

        /**
         * Hides a modal.
         * @param {HTMLElement} modalElement The modal element.
         */
        function hideModal(modalElement) {
            modalElement.classList.add('hidden');
        }

        function resetCreateFolderForm() {
            folderNameInput.value = '';
            folderTypeSelect.value = ''; // Reset select to default 'Pilih jenis folder'
            folderAccessSelect.value = ''; // Reset select to default 'Pilih akses'
            if (accessRolesContainer) { // Ensure element exists before accessing
                accessRolesContainer.classList.add('hidden');
            }
            accessRolesCheckboxes.forEach(checkbox => checkbox.checked = false);
        }

        function resetUploadFileForm() {
            fileInput.value = '';
            fileDescription.value = '';
        }

        // --- MAIN EVENT LISTENERS ---

        // Event Listener for the "Baru" dropdown button
        if (dropdownButton && dropdownMenu) {
            dropdownButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Prevent event from bubbling up to document click listener

                // Close all other table dropdown menus (if any exist)
                document.querySelectorAll('.menu-dropdown').forEach(otherMenu => {
                    hideDropdown(otherMenu);
                });

                // Toggle the "Baru" dropdown visibility
                const isVisible = dropdownMenu.classList.contains('invisible') || dropdownMenu.classList.contains('opacity-0');
                if (isVisible) {
                    showDropdown(dropdownMenu);
                } else {
                    hideDropdown(dropdownMenu);
                }
            });
        }

        // Close the "Baru" dropdown if user clicks outside of it or its button
        document.addEventListener('click', function(event) {
            if (dropdownButton && dropdownMenu && !dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                hideDropdown(dropdownMenu);
            }
        });

        // Event Listener for "Buat Folder" link in dropdown
        if (openCreateFolder && modalCreateFolder) {
            openCreateFolder.addEventListener('click', function(e) {
                e.preventDefault();
                hideDropdown(dropdownMenu); // Close "Baru" dropdown
                resetCreateFolderForm(); // Reset form before opening
                showModal(modalCreateFolder); // Show folder modal
            });
        }

        // Event Listener for "Unggah File" link in dropdown
        if (openUploadFile && modalUploadFile) {
            openUploadFile.addEventListener('click', function(e) {
                e.preventDefault();
                hideDropdown(dropdownMenu); // Close "Baru" dropdown
                resetUploadFileForm(); // Reset form before opening
                showModal(modalUploadFile); // Show upload modal
            });
        }

        // --- Event Listener for Upload Folder ---
        // Ensure you have <input type="file" id="folderUploadInput" webkitdirectory directory multiple class="hidden"> in your HTML
        if (openUploadFolder && folderUploadInput) {
            openUploadFolder.addEventListener('click', function(e) {
                e.preventDefault();
                hideDropdown(dropdownMenu);
                folderUploadInput.click(); // Programmatically click the hidden input
            });

            folderUploadInput.addEventListener('change', async function(e) {
                const files = e.target.files;
                if (files.length === 0) {
                    return;
                }

                const progressDiv = document.createElement('div');
                progressDiv.className = 'fixed bottom-4 right-4 bg-blue-600 text-white p-4 rounded-lg shadow-lg z-50'; // Added z-index
                progressDiv.textContent = `Mengunggah 0 dari ${files.length} file...`;
                document.body.appendChild(progressDiv);

                for (let i = 0; i < files.length; i++) {
                    progressDiv.textContent = `Mengunggah ${i + 1} dari ${files.length} file: ${files[i].name}`;
                    const formData = new FormData();
                    formData.append('file', files[i]);
                    formData.append('relativePath', files[i].webkitRelativePath);
                    formData.append('parent_id', window.currentFolderId || null); // Will be null if at root
                    formData.append('user_id', window.currentUserId); // Pass user ID

                    try {
                        const response = await fetch('<?= base_url('supervisor/upload-from-folder') ?>', { // CONFIRM THIS URL!
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const result = await response.json();
                        if (result.status !== 'success') {
                            alert(`Gagal mengunggah ${files[i].name}: ${result.message || 'Terjadi kesalahan.'}`);
                            document.body.removeChild(progressDiv);
                            return;
                        }
                    } catch (error) {
                        console.error('Error during folder upload:', error);
                        alert(`Terjadi kesalahan jaringan saat mengunggah ${files[i].name}.`);
                        document.body.removeChild(progressDiv);
                        return;
                    }
                }

                progressDiv.textContent = 'Semua file berhasil diunggah!';
                setTimeout(() => {
                    document.body.removeChild(progressDiv);
                    window.location.reload(); // Reload page to show new files/folders
                }, 2000);
            });
        }

        // Event Listener for "Batal" button in "Create Folder" modal
        if (cancelModal && modalCreateFolder) {
            cancelModal.addEventListener('click', function() {
                hideModal(modalCreateFolder);
            });
        }

        // Close "Create Folder" modal when clicking outside
        if (modalCreateFolder) {
            modalCreateFolder.addEventListener('click', function(e) {
                if (e.target === modalCreateFolder) {
                    hideModal(modalCreateFolder);
                }
            });
        }

        // Event Listener for "Batal" button in "Upload File" modal
        if (cancelUploadModal && modalUploadFile) {
            cancelUploadModal.addEventListener('click', function() {
                hideModal(modalUploadFile);
            });
        }

        // Close "Upload File" modal when clicking outside
        if (modalUploadFile) {
            modalUploadFile.addEventListener('click', function(e) {
                if (e.target === modalUploadFile) {
                    hideModal(modalUploadFile);
                }
            });
        }

        // ADDITIONAL LOGIC: Show/Hide role checkboxes based on folder type
        if (folderTypeSelect && accessRolesContainer) {
            folderTypeSelect.addEventListener('change', function() {
                if (this.value === 'shared') {
                    accessRolesContainer.classList.remove('hidden');
                } else {
                    accessRolesContainer.classList.add('hidden');
                    accessRolesCheckboxes.forEach(checkbox => checkbox.checked = false);
                }
            });
        }

        // --- FETCH LOGIC FOR CREATING FOLDER ---
        if (createFolderBtn && folderNameInput && folderTypeSelect && folderAccessSelect) {
            createFolderBtn.addEventListener('click', function() {
                const folderName = folderNameInput.value.trim();
                const folderType = folderTypeSelect.value;
                const folderAccess = folderAccessSelect.value;
                let selectedAccessRoles = [];

                if (folderType === 'shared') {
                    selectedAccessRoles = Array.from(accessRolesCheckboxes)
                        .filter(checkbox => checkbox.checked)
                        .map(checkbox => checkbox.value);
                }

                // Frontend Validation
                if (folderName === '') {
                    alert('Nama folder tidak boleh kosong!');
                    return;
                }
                if (folderType === '') {
                    alert('Silakan pilih jenis folder!');
                    return;
                }
                if (folderType === 'shared' && folderAccess === '') {
                    alert('Silakan pilih jenis akses untuk Shared Folder!');
                    return;
                }
                if (folderType === 'shared' && selectedAccessRoles.length === 0) {
                    alert('Untuk Shared Folder, minimal satu peran akses harus dipilih!');
                    return;
                }

                // CONFIRM THIS URL FOR SUPERVISOR
                fetch('<?= base_url('supervisor/create-folder') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            name: folderName,
                            folder_type: folderType,
                            is_shared: folderType === 'shared' ? 1 : 0,
                            shared_type: folderType === 'shared' ? folderAccess : null,
                            owner_id: window.currentUserId,
                            access_roles: folderType === 'shared' ? selectedAccessRoles : null,
                            parent_id: window.currentFolderId // Will be null if at root supervisor folder
                        })
                    })
                    .then(response => {
                        const contentType = response.headers.get('content-type');
                        if (contentType && contentType.indexOf('application/json') !== -1) {
                            return response.json();
                        } else {
                            return response.text().then(text => {
                                console.error('Server returned non-JSON response:', text);
                                throw new Error('Server returned non-JSON response. Check PHP error logs. Response: ' + text);
                            });
                        }
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            alert(data.message);
                            hideModal(modalCreateFolder);
                            window.location.reload(); // Reload page to show new folder
                        } else {
                            alert('Error: ' + (data.message || 'Terjadi kesalahan.'));
                            if (data.errors) {
                                let errorMessages = '';
                                for (const key in data.errors) {
                                    errorMessages += `${data.errors[key]}\n`;
                                }
                                alert('Validasi Gagal:\n' + errorMessages);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error saat membuat folder:', error);
                        alert('Terjadi kesalahan saat berkomunikasi dengan server untuk membuat folder.');
                    });
            });
        }

        // --- FETCH LOGIC FOR UPLOADING FILE ---
        if (uploadFileBtn && fileInput) {
            uploadFileBtn.addEventListener('click', function() {
                const file = fileInput.files[0];
                const description = fileDescription.value.trim();

                if (!file) {
                    alert('Silakan pilih file untuk diunggah!');
                    return;
                }

                const formData = new FormData();
                formData.append('file', file);
                formData.append('description', description);
                formData.append('parent_id', window.currentFolderId);
                formData.append('user_id', window.currentUserId);

                fetch('<?= base_url('supervisor/upload-file') ?>', { // CONFIRM THIS URL!
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => {
                        const contentType = response.headers.get('content-type');
                        if (contentType && contentType.indexOf('application/json') !== -1) {
                            return response.json();
                        } else {
                            return response.text().then(text => {
                                console.error('Server returned non-JSON response for upload:', text);
                                throw new Error('Server returned non-JSON response for upload. Check PHP error logs. Response: ' + text);
                            });
                        }
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            alert(data.message);
                            hideModal(modalUploadFile);
                            window.location.reload(); // Reload page to show new file
                        } else {
                            alert('Error unggah: ' + (data.message || 'Terjadi kesalahan saat mengunggah file.'));
                            if (data.errors) {
                                let errorMessages = '';
                                for (const key in data.errors) {
                                    errorMessages += `${data.errors[key]}\n`;
                                }
                                alert('Validasi Unggah Gagal:\n' + errorMessages);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error saat mengunggah file:', error);
                        alert('Terjadi kesalahan saat berkomunikasi dengan server untuk unggah file.');
                    });
            });
        }

        // Logic for table dropdown menus (optional, if you want to add them)
        window.toggleMenu = function(button) {
            // You'll need to add a dropdown menu element right after your button in the HTML.
            // For example:
            // <button onclick="toggleMenu(this)" class="text-blue-600 hover:text-blue-900">⋮</button>
            // <div class="menu-dropdown absolute bg-white shadow-lg rounded-md hidden z-20">
            //    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Opsi 1</a>
            //    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Opsi 2</a>
            // </div>

            const menu = button.nextElementSibling; // Assumes menu is right after the button
            if (menu && menu.classList.contains('menu-dropdown')) { // Make sure this is the correct dropdown menu
                // Close the "Baru" dropdown if open
                hideDropdown(dropdownMenu);

                // Close all other table dropdown menus
                document.querySelectorAll('.menu-dropdown').forEach(otherMenu => {
                    if (otherMenu !== menu) {
                        hideDropdown(otherMenu);
                    }
                });

                // Toggle the clicked menu
                const isVisible = menu.classList.contains('invisible') || menu.classList.contains('opacity-0');
                if (isVisible) {
                    showDropdown(menu);
                } else {
                    hideDropdown(menu);
                }
            }
        };

        // Close table dropdown menus if clicked outside
        document.addEventListener('click', function(event) {
            document.querySelectorAll('.menu-dropdown').forEach(menu => {
                const button = menu.previousElementSibling; // Assumes button is right before the menu
                if (menu && button && !button.contains(event.target) && !menu.contains(event.target)) {
                    hideDropdown(menu);
                }
            });
        });

        // Initialize dropdownMenu to be hidden with transition classes on page load
        // This ensures it starts with opacity-0, invisible, and scale-95
        hideDropdown(dropdownMenu);

    });
</script>

<?= $this->endSection() ?>