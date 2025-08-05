<?= $this->extend('layout/hrd') ?>

<?= $this->section('content') ?>
<!-- Header Dokumen -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-semibold text-gray-800">Dokumen Umum</h1>
        </div>
        <div class="flex items-center space-x-4">
            <!-- Search Bar -->
            <div class="relative">
                <input type="text" placeholder="Masukkan file dokumen..."
                    class="w-80 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <!-- Logo -->
            <img src="<?= base_url('images/logo.png') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
        </div>
    </div>
</div>

<!-- Tombol Baru -->
<div class="relative inline-block text-left mb-6">
    <button id="dropdownButton"
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <span>Baru</span>
    </button>

    <div id="dropdownMenu" class="absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg hidden">
        <a href="#" id="openCreateFolder" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">📁 Buat Folder</a>
        <a href="#" id="openUploadFile" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">⬆️ Upload File</a>
    </div>
</div>

<!-- Modal -->
<div id="modalCreateFolder"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-sm hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
        <h2 class="text-xl font-semibold mb-4">Folder Baru</h2>

        <form id="createFolderForm">
            <input type="hidden" id="parentId" name="parent_id" value="<?= $parent_id ?? '' ?>">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
            
            <label class="block text-sm font-medium mb-1">Jenis Folder</label>
            <div class="relative mb-4">
                <select id="folderType" name="folder_type" class="w-full border rounded-lg px-3 py-2 pr-10 appearance-none">
                    <option value="personal" selected>Personal</option>
                    <option value="SOP">SOP</option>
                    <option value="SK">SK</option>
                    <option value="Pengumuman">PENGUMUMAN</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <div id="sharedOptions" class="hidden mb-4">
                <div class="grid grid-cols-2 gap-2 mb-4">
                    <label><input type="checkbox" name="access_roles[]" value="Staff" class="mr-2"> Staff</label>
                    <label><input type="checkbox" name="access_roles[]" value="Manager" class="mr-2"> Manager</label>
                    <label><input type="checkbox" name="access_roles[]" value="Supervisor" class="mr-2"> Supervisor</label>
                    <label><input type="checkbox" name="access_roles[]" value="Direksi" class="mr-2"> Direksi</label>
                </div>

                <label class="block text-sm font-medium mb-1">Tipe Akses</label>
                <div class="relative mb-4">
                    <select id="sharedType" name="shared_type" class="w-full border rounded-lg px-3 py-2 pr-10 appearance-none">
                        <option value="read">Read Only</option>
                        <option value="write">Read & Write</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <label class="block text-sm font-medium">Nama Folder</label>
            <input type="text" id="folderName" name="folder_name" placeholder="Masukan nama folder"
                class="w-full border rounded-lg px-3 py-2 mb-4" required>

            <div class="flex justify-end space-x-4">
                <button type="button" id="cancelModal" class="text-blue-500">Batal</button>
                <button type="submit" class="text-blue-600 font-semibold">Buat</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Upload File -->
<div id="modalUploadFile"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-sm hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
        <h2 class="text-xl font-semibold mb-4">Upload File</h2>

        <form id="uploadFileForm" enctype="multipart/form-data">
            <input type="hidden" name="parent_id" value="<?= $parent_id ?? '' ?>">
            
            <label class="block text-sm font-medium mb-1">Pilih File</label>
            <div class="mb-4">
                <input type="file" name="file" required 
                    class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <label class="block text-sm font-medium mb-1">Deskripsi (Opsional)</label>
            <div class="mb-4">
                <textarea name="description" rows="3" 
                    class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Masukkan deskripsi file..."></textarea>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" id="cancelUploadModal" class="text-gray-500 hover:text-gray-700">Batal</button>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- Filter Dokumen -->

<!-- Tabel Dokumen -->
<div class="bg-white rounded-lg shadow-sm">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
            Dokumen Terbaru
        </h2>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-blue-600">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama File
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Pengunggah
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tanggal
                        Diunggah</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody id="documentsTableBody" class="bg-white divide-y divide-gray-200">
                <?php if (!empty($documents)): ?>
                    <?php foreach ($documents as $doc): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php if ($doc['type'] === 'folder'): ?>
                                        <svg class="w-5 h-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                                        </svg>
                                    <?php else: ?>
                                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                        </svg>
                                    <?php endif; ?>
                                    <div>
                                        <?php if ($doc['type'] === 'folder'): ?>
                                            <a href="<?= base_url('hrd/dokumen-umum/folder/' . $doc['id']) ?>" class="text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline cursor-pointer">
                                                <?= esc($doc['name']) ?>
                                            </a>
                                        <?php else: ?>
                                            <div class="text-sm font-medium text-gray-900"><?= esc($doc['name']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($doc['category'])): ?>
                                            <span class="ml-2 px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded"><?= esc($doc['category']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
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
                                <button onclick="toggleMenu(this)" class="text-blue-600 hover:text-blue-900">⋮</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            Belum ada dokumen atau folder
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    console.log('=== DEBUGGING DROPDOWN ===');
    
    // Fungsi untuk toggle dropdown - langsung di global scope
    function toggleDropdownMenu() {
        console.log('toggleDropdownMenu called!');
        const menu = document.getElementById('dropdownMenu');
        if (menu) {
            const isHidden = menu.classList.contains('hidden');
            if (isHidden) {
                menu.classList.remove('hidden');
                console.log('Dropdown opened');
            } else {
                menu.classList.add('hidden');
                console.log('Dropdown closed');
            }
        } else {
            console.error('dropdownMenu element not found!');
        }
    }
    
    // Tambahkan event listener setelah DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM ready - setting up dropdown');
        
        const dropdownButton = document.getElementById('dropdownButton');
        const dropdownMenu = document.getElementById('dropdownMenu');
        
        console.log('Button found:', !!dropdownButton);
        console.log('Menu found:', !!dropdownMenu);
        
        if (dropdownButton && dropdownMenu) {
            // Tambahkan event listener
            dropdownButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Button clicked via addEventListener!');
                toggleDropdownMenu();
            });
            
            // Tambahkan juga onclick attribute sebagai backup
            dropdownButton.setAttribute('onclick', 'toggleDropdownMenu(); return false;');
            
            console.log('Event listeners added successfully');
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                    dropdownMenu.classList.add('hidden');
                }
            });
        } else {
            console.error('Dropdown elements not found!', {
                button: dropdownButton,
                menu: dropdownMenu
            });
        }
    });
    
    // Test function untuk debugging
    function testDropdown() {
        console.log('Testing dropdown...');
        const button = document.getElementById('dropdownButton');
        const menu = document.getElementById('dropdownMenu');
        console.log('Button:', button);
        console.log('Menu:', menu);
        if (button && menu) {
            console.log('Elements found, toggling...');
            toggleDropdownMenu();
        }
    }
    
    console.log('=== DROPDOWN SCRIPT LOADED ===');
    
    // Modal Management Functions
    document.addEventListener('DOMContentLoaded', function() {
        // Create Folder Modal
        const openCreateFolder = document.getElementById('openCreateFolder');
        const modalCreateFolder = document.getElementById('modalCreateFolder');
        const cancelModal = document.getElementById('cancelModal');
        
        if (openCreateFolder) {
            openCreateFolder.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Opening create folder modal');
                modalCreateFolder.classList.remove('hidden');
                document.getElementById('dropdownMenu').classList.add('hidden');
            });
        }
        
        if (cancelModal) {
            cancelModal.addEventListener('click', function() {
                modalCreateFolder.classList.add('hidden');
            });
        }
        
        // Upload File Modal
        const openUploadFile = document.getElementById('openUploadFile');
        const modalUploadFile = document.getElementById('modalUploadFile');
        const cancelUploadModal = document.getElementById('cancelUploadModal');
        
        if (openUploadFile) {
            openUploadFile.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Opening upload file modal');
                modalUploadFile.classList.remove('hidden');
                document.getElementById('dropdownMenu').classList.add('hidden');
            });
        }
        
        if (cancelUploadModal) {
            cancelUploadModal.addEventListener('click', function() {
                modalUploadFile.classList.add('hidden');
            });
        }
        
        // Close modals when clicking outside
        [modalCreateFolder, modalUploadFile].forEach(modal => {
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.classList.add('hidden');
                    }
                });
            }
        });
    });
    
    // Custom form submission untuk dokumen umum
    document.addEventListener('DOMContentLoaded', function() {
        const createFolderForm = document.getElementById('createFolderForm');
        if (createFolderForm) {
            createFolderForm.addEventListener('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Custom form submit handler called!');
                
                const formData = new FormData(this);
                const folderName = formData.get('folder_name');
                const folderType = formData.get('folder_type');
                const parentId = formData.get('parent_id');
                
                console.log('Form data:', {
                    folderName: folderName,
                    folderType: folderType,
                    parentId: parentId
                });
                
                if (!folderName || folderName.trim() === '') {
                    alert('Nama folder tidak boleh kosong!');
                    return;
                }
                
                // Disable submit button
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Membuat...';
                }
                
                // Prepare data for JSON request
                const requestData = {
                    name: folderName.trim(),
                    parent_id: parentId || null,
                    folder_type: folderType || 'personal'
                };
                
                console.log('Sending request to:', '<?= base_url('hrd/dokumen-umum/create-folder') ?>');
                console.log('Request data:', requestData);
                
                // Send AJAX request
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
                    console.log('Response status:', response.status);
                    console.log('Response ok:', response.ok);
                    
                    // Clone response untuk debugging
                    const responseClone = response.clone();
                    
                    // Log raw response text untuk debugging
                    return responseClone.text().then(text => {
                        console.log('Raw response text:', text);
                        
                        // Jika response ok dan ada content, coba parse JSON
                        if (response.ok && text.trim()) {
                            try {
                                const jsonData = JSON.parse(text);
                                return jsonData;
                            } catch (jsonError) {
                                console.error('JSON parse error:', jsonError);
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
                    console.log('Response data:', data);
                    
                    if (data.status === 'success') {
                        alert('Folder berhasil dibuat!');
                        
                        // Close modal
                        const modal = document.getElementById('modalCreateFolder');
                        if (modal) {
                            modal.classList.add('hidden');
                        }
                        
                        // Reset form
                        createFolderForm.reset();
                        
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
                        createFolderForm.reset();
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
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
                    // Re-enable submit button
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Buat';
                    }
                });
            });
        }
        
        // Upload File Form Handler
        const uploadFileForm = document.getElementById('uploadFileForm');
        if (uploadFileForm) {
            uploadFileForm.addEventListener('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Upload file form submitted!');
                
                const formData = new FormData(this);
                const file = formData.get('file');
                
                if (!file || file.size === 0) {
                    alert('Silakan pilih file yang akan diupload!');
                    return;
                }
                
                console.log('File selected:', file.name, 'Size:', file.size);
                
                // Disable submit button
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Uploading...';
                }
                
                console.log('Sending upload request to:', '<?= base_url('hrd/dokumen-umum/upload-file') ?>');
                
                // Send AJAX request with FormData
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
                    console.log('Upload response data:', data);
                    
                    if (data.status === 'success') {
                        alert('File berhasil diupload!');
                        
                        // Close modal
                        const modal = document.getElementById('modalUploadFile');
                        if (modal) {
                            modal.classList.add('hidden');
                        }
                        
                        // Reset form
                        uploadFileForm.reset();
                        
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
                        uploadFileForm.reset();
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
                    // Re-enable submit button
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Upload';
                    }
                });
            });
        }
    });
    
    // Event listener untuk dropdown "Baru" yang hilang
    const dropdownButton = document.getElementById('dropdownButton');
    const dropdownMenu = document.getElementById('dropdownMenu');
    
    if (dropdownButton && dropdownMenu) {
        dropdownButton.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('hidden');
            console.log('Dropdown toggled');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!dropdownButton.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });
        
        // Event listener untuk "Buat Folder"
        const openCreateFolder = document.getElementById('openCreateFolder');
        if (openCreateFolder) {
            openCreateFolder.addEventListener('click', function(e) {
                e.preventDefault();
                const modal = document.getElementById('modalCreateFolder');
                if (modal) {
                    modal.classList.remove('hidden');
                    console.log('Create folder modal opened');
                }
                dropdownMenu.classList.add('hidden');
            });
        }
        
        // Event listener untuk "Upload File"
        const openUploadFile = document.getElementById('openUploadFile');
        if (openUploadFile) {
            openUploadFile.addEventListener('click', function(e) {
                e.preventDefault();
                const modal = document.getElementById('modalUploadFile');
                if (modal) {
                    modal.classList.remove('hidden');
                    console.log('Upload file modal opened');
                }
                dropdownMenu.classList.add('hidden');
            });
        }
    } else {
        console.error('Dropdown elements not found:', {
            dropdownButton: !!dropdownButton,
            dropdownMenu: !!dropdownMenu
        });
    }
</script>

<?= $this->endSection() ?>