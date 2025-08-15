<?= $this->extend('layout/main') ?>

<?= $this->section('pageTitle') ?>
Dokumen Staff
<?= $this->endSection() ?>

<?= $this->section('pageLogo') ?>
<img src="<?= base_url('images/logo.jpg') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="hidden md:block">
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-semibold text-gray-800">Dokumen Staff</h1>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Masukkan file dokumen..."
                        class="w-80 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <div id="searchResults"
                        class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden">
                    </div>
                </div>
                <img src="<?= base_url('images/logo.jpg') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
            </div>
        </div>
    </div>
</div>

<div class="hidden md:block">
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                Folder Staff
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama
                            Folder
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Dibuat
                            Oleh
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tipe
                            Folder
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tanggal
                            Dibuat</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($personalFolders)): ?>
                        <?php foreach ($personalFolders as $folder): ?>
                            <tr class="hover:bg-gray-50" data-folder-id="<?= esc($folder['id']) ?>"
                                data-folder-name="<?= esc($folder['name']) ?>"
                                data-folder-type="<?= esc($folder['folder_type']) ?>"
                                data-folder-is-shared="<?= esc($folder['is_shared'] ?? 0) ?>"
                                data-folder-shared-type="<?= esc($folder['shared_type'] ?? '') ?>"
                                data-folder-owner-id="<?= esc($folder['owner_id']) ?>"
                                data-folder-owner-name="<?= esc($folder['owner_name'] ?? 'Unknown') ?>"
                                data-folder-created-at="<?= esc($folder['created_at']) ?>"
                                data-folder-updated-at="<?= esc($folder['updated_at']) ?>"
                                data-folder-path="<?= esc($folder['path'] ?? $folder['name']) ?>">

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="<?= base_url('manager/view-staff-folder/' . $folder['id']) ?>"
                                        class="block h-full w-full text-sm text-gray-900 hover:text-blue-700 hover:underline">
                                        <div class="flex items-center">
                                            <img src="<?= base_url('images/folder.png') ?>" alt="Folder Icon"
                                                class="w-5 h-5 mr-2">
                                            <?= esc($folder['name']) ?>
                                        </div>
                                    </a>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="text-sm text-gray-500">
                                        <?= esc($folder['owner_email'] ?? '') ?>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= esc(ucfirst($folder['folder_type'])) ?>
                                    <?php if (isset($folder['is_shared']) && $folder['is_shared'] == 1): ?>
                                        <?= esc(ucfirst($folder['shared_type'])) ?>
                                    <?php endif; ?>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= date('d M Y', strtotime($folder['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                Belum ada folder dari staff yang tersedia.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($orphanFiles)): ?>
            <div class="bg-white rounded-lg shadow-sm mt-6">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        File Tanpa Folder (Staff)
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-blue-600">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama
                                    File</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                    Diupload
                                    Oleh</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                    Jenis
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                    Ukuran
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                    Tanggal
                                    Unggah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Aksi
                                </th>
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
                                            <span
                                                class="text-sm text-gray-900"><?= esc($file['original_name'] ?? $file['file_name']) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-6 w-6">
                                                <div class="h-6 w-6 rounded-full bg-green-500 flex items-center justify-center">
                                                    <span class="text-xs font-medium text-white">
                                                        <?= strtoupper(substr($file['uploader_name'] ?? 'U', 0, 1)) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-2">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?= esc($file['uploader_name'] ?? 'Unknown') ?>
                                                </div>
                                            </div>
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
                                        <a href="<?= base_url('supervisor/download-staff-file/' . $file['id']) ?>"
                                            class="text-blue-600 hover:text-blue-900 mr-2">Unduh</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="block md:hidden">
    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
        <div class="relative">
            <input type="text" id="searchInputMobile" placeholder="Masukkan file dokumen..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <div id="searchResultsMobile"
                class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden"></div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Folder Terbaru</h2>
        </div>
        <div class="divide-y divide-gray-200">
            <?php if (!empty($personalFolders)): ?>
                <?php foreach ($personalFolders as $folder): ?>
                    <div class="relative flex items-center justify-between px-4 py-3 hover:bg-gray-50"
                        data-folder-id="<?= esc($folder['id']) ?>" data-folder-name="<?= esc($folder['name']) ?>"
                        data-folder-type="<?= esc($folder['folder_type']) ?>"
                        data-folder-is-shared="<?= esc($folder['is_shared'] ?? 0) ?>"
                        data-folder-shared-type="<?= esc($folder['shared_type'] ?? '') ?>"
                        data-folder-owner-id="<?= esc($folder['owner_id']) ?>"
                        data-folder-owner-name="<?= esc($folder['owner_display'] ?? $folder['owner_name'] ?? 'Unknown') ?>"
                        data-folder-created-at="<?= esc($folder['created_at']) ?>"
                        data-folder-updated-at="<?= esc($folder['updated_at']) ?>"
                        data-folder-path="<?= esc($folder['path'] ?? $folder['name']) ?>">
                        <div class="flex items-center space-x-4">
                            <img src="<?= base_url('images/folder.png') ?>" alt="Folder Icon" class="w-6 h-6">
                            <div>
                                <a href="<?= base_url('manager/view-staff-folder/' . $folder['id']) ?>"
                                    class="block font-medium text-gray-900 hover:text-blue-700 hover:underline">
                                    <?= esc($folder['name']) ?>
                                </a>
                                <div class="text-gray-500 text-xs">
                                    <?= date('d M Y', strtotime($folder['created_at'])) ?> |
                                    <?= esc($folder['owner_email'] ?? '') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="flex items-center justify-center px-6 py-4 text-gray-500">
                    Tidak ada folder yang tersedia.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInputDesktop = document.getElementById('searchInput');
        const searchInputMobile = document.getElementById('searchInputMobile');
        const searchResults = document.getElementById('searchResults');
        const searchResultsMobile = document.getElementById('searchResultsMobile');
        const searchApiUrl = '<?= base_url('manager/searchStaff') ?>';

        // Fungsi utama untuk menangani pencarian
        // Ubah fungsi agar menerima elemen hasil pencarian sebagai parameter
        const handleSearch = (inputElement, resultsElement) => {
            if (!inputElement || !resultsElement) {
                return;
            }

            inputElement.addEventListener('input', function () {
                const query = this.value.trim();

                if (query.length < 2) {
                    resultsElement.innerHTML = '';
                    resultsElement.classList.add('hidden');
                    return;
                }

                // Lakukan fetch dengan metode POST
                fetch(searchApiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `q=${encodeURIComponent(query)}`
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Gunakan resultsElement yang dinamis
                        resultsElement.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(item => {
                                const a = document.createElement('a');
                                let url = '#';
                                if (item.type === 'folder') {
                                    url = `<?= base_url('manager/view-staff-folder/') ?>${item.id}`;
                                } else {
                                    url = item.folder_id ? `<?= base_url('manager/view-staff-folder/') ?>${item.folder_id}` : `<?= base_url('staff/dokumen-staff') ?>`;
                                }
                                a.href = url;
                                a.className = 'block px-4 py-2 text-gray-700 hover:bg-gray-100';
                                a.textContent = `${item.type === 'folder' ? '📁' : '📄'} ${item.name}`;
                                resultsElement.appendChild(a);
                            });
                            resultsElement.classList.remove('hidden');
                        } else {
                            const noResult = document.createElement('div');
                            noResult.className = 'px-4 py-2 text-gray-500';
                            noResult.textContent = 'Tidak ada hasil ditemukan';
                            resultsElement.appendChild(noResult);
                            resultsElement.classList.remove('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        resultsElement.innerHTML = `<div class="px-4 py-2 text-red-500">Error saat mencari: ${error.message}</div>`;
                        resultsElement.classList.remove('hidden');
                    });
            });

            document.addEventListener('click', function (event) {
                // Pastikan untuk menggunakan inputElement dan resultsElement yang dinamis
                if (!inputElement.contains(event.target) && !resultsElement.contains(event.target)) {
                    resultsElement.classList.add('hidden');
                }
            });
        };

        // Terapkan fungsi handleSearch ke kedua input pencarian
        if (searchInputDesktop && searchResults) {
            handleSearch(searchInputDesktop, searchResults);
        }
        if (searchInputMobile && searchResultsMobile) {
            handleSearch(searchInputMobile, searchResultsMobile);
        }
    });
</script>
<?= $this->endSection() ?>