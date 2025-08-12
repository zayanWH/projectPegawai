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


<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const searchResults = document.getElementById('searchResults');

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.trim();

                    if (query.length < 2) {
                        searchResults.innerHTML = '';
                        searchResults.classList.add('hidden');
                        return;
                    }

                    fetch('<?= site_url('manager/searchSPV') ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: `q=${encodeURIComponent(query)}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            searchResults.innerHTML = '';

                            // Check if there's an error
                            if (data.status && data.status === 'error') {
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'px-4 py-2 text-red-500';
                                errorDiv.textContent = data.message;
                                searchResults.appendChild(errorDiv);
                                searchResults.classList.remove('hidden');
                                return;
                            }

                            if (data.length > 0) {
                                data.forEach(item => {
                                    const a = document.createElement('a');
                                    let url = '#';

                                    if (item.type === 'folder') {
                                        // URL untuk melihat folder staff
                                        url = `<?= site_url('manager/view-supervisor-folder/') ?>${item.id}`;
                                    } else if (item.type === 'file') {
                                        if (item.folder_id) {
                                            // File ada dalam folder
                                            url = `<?= site_url('manager/view-supervisor-folder/') ?>${item.folder_id}`;
                                        } else {
                                            // File tanpa folder (orphan files)
                                            url = `<?= site_url('manager/dokumen-supervisor') ?>`;
                                        }
                                    }

                                    a.href = url;
                                    a.className = 'block px-4 py-2 text-gray-700 hover:bg-gray-100 border-b border-gray-100';

                                    // Create icon and text
                                    const icon = item.type === 'folder' ? '📁' : '📄';
                                    a.innerHTML = `
                                <div class="flex items-center">
                                    <span class="mr-2">${icon}</span>
                                    <div>
                                        <div class="font-medium text-sm">${item.name}</div>
                                        <div class="text-xs text-gray-500">${item.type === 'folder' ? 'Folder' : 'File'}</div>
                                    </div>
                                </div>
                            `;

                                    searchResults.appendChild(a);
                                });
                                searchResults.classList.remove('hidden');
                            } else {
                                const noResult = document.createElement('div');
                                noResult.className = 'px-4 py-2 text-gray-500';
                                noResult.textContent = 'Tidak ada hasil ditemukan';
                                searchResults.appendChild(noResult);
                                searchResults.classList.remove('hidden');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            searchResults.innerHTML = '<div class="px-4 py-2 text-red-500">Error saat mencari.</div>';
                            searchResults.classList.remove('hidden');
                        });
                });

                // Hide search results when clicking outside
                document.addEventListener('click', function(event) {
                    if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
                        searchResults.classList.add('hidden');
                    }
                });
            }
        });
</script>

<?= $this->endSection() ?>