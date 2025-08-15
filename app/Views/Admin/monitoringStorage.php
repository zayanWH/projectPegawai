<!<?= $this->extend('layout/admin') ?>

    <?= $this->section('content') ?>
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-semibold text-gray-800">Monitoring Storage</h1>
            </div>
            <div class="flex items-center space-x-4">
                <img src="<?= base_url('images/logo.png') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
            </div>
        </div>
    </div>

    <!-- Storage by Position Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Penggunaan Storage Berdasarkan Jabatan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Jabatan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Jumlah
                            User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Total
                            File</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Total
                            Size</th>
                    </tr>
                </thead>
                <tbody id="storageByPositionTableBody" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Users Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Penggunaan Storage Berdasarkan User (Top 5)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama
                            User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Jabatan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Total
                            File</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Total
                            Size</th>
                    </tr>
                </thead>
                <tbody id="topUsersTableBody" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Largest Files Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">File Terbesar</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama
                            File</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Ukuran
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Uploader
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Jabatan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tanggal
                            Upload</th>
                    </tr>
                </thead>
                <tbody id="largestFilesTableBody" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let storageChart;

            // Load all data
            loadStorageByPosition();
            loadTopStorageUsers();
            loadLargestFiles();

            /**
             * Load storage data by position
             */
            function loadStorageByPosition() {
                fetch('<?= base_url('admin/getStorageByPosition') ?>', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            renderStorageByPositionTable(data.data);
                            renderStorageChart(data.data);
                            updateTotalStorageUsed(data.data);
                        } else {
                            console.error('Error loading storage by position:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }

            /**
             * Load top storage users
             */
            function loadTopStorageUsers() {
                fetch('<?= base_url('admin/getTopStorageUsers') ?>', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            renderTopUsersTable(data.data);
                        } else {
                            console.error('Error loading top users:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }

            /**
     * Dapatkan ikon file berdasarkan nama file atau ekstensi.
     * @param {string} fileName - Nama file.
     * @returns {string} Tag <img> dengan ikon yang sesuai.
     */
            function getFileIcon(fileName) {
                const fileExtension = fileName.split('.').pop().toLowerCase();
                let iconSrc;

                switch (fileExtension) {
                    case 'pdf':
                        iconSrc = '<?= base_url('images/pdf.png') ?>';
                        break;
                    case 'doc':
                    case 'docx':
                        iconSrc = '<?= base_url('images/word.png') ?>';
                        break;
                    case 'xls':
                    case 'xlsx':
                        iconSrc = '<?= base_url('images/excel.png') ?>';
                        break;
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                    case 'gif':
                        iconSrc = '<?= base_url('images/image.png') ?>';
                        break;
                    default:
                        iconSrc = '<?= base_url('images/file-default.png') ?>'; // Ikon default
                        break;
                }

                return `<img src="${iconSrc}" alt="${fileExtension} icon" class="w-5 h-5 mr-2">`;
            }

            /**
             * Load largest files
             */
            function loadLargestFiles() {
                fetch('<?= base_url('admin/getLargestFiles') ?>', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            renderLargestFilesTable(data.data);
                        } else {
                            console.error('Error loading largest files:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }

            /**
             * Render storage by position table
             */
            function renderStorageByPositionTable(data) {
                const tbody = document.getElementById('storageByPositionTableBody');

                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada data</td></tr>';
                    return;
                }

                tbody.innerHTML = data.map(item => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.jabatan}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.jumlah_user}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.total_file}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formatStorageSize(item.total_size_bytes)}</td>
        </tr>
    `).join('');
            }

            /**
             * Render top users table
             */
            function renderTopUsersTable(data) {
                const tbody = document.getElementById('topUsersTableBody');

                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada data</td></tr>';
                    return;
                }

                tbody.innerHTML = data.map(item => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.nama_user}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.jabatan}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.total_file}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.total_size}</td>
            </tr>
        `).join('');
            }

            /**
             * Render largest files table
             */
            function renderLargestFilesTable(data) {
                const tbody = document.getElementById('largestFilesTableBody');

                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak ada data</td></tr>';
                    return;
                }

                tbody.innerHTML = data.map(item => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    ${getFileIcon(item.nama_file)}
                    <span class="text-sm text-gray-900">${item.nama_file}</span>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.ukuran}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.uploader}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.jabatan}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.tanggal_upload}</td>
        </tr>
    `).join('');
            }

            /**
             * Render storage chart
             */
            function renderStorageChart(data) {
                const ctx = document.getElementById('storageChart').getContext('2d');

                // Destroy existing chart if it exists
                if (storageChart) {
                    storageChart.destroy();
                }

                const colors = ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444', '#06B6D4'];
                const borderColors = ['#1E40AF', '#059669', '#D97706', '#7C3AED', '#DC2626', '#0891B2'];

                const chartData = data.map((item, index) => ({
                    label: item.jabatan,
                    data: Math.round(item.total_size_bytes / (1024 * 1024)), // Convert to MB
                    backgroundColor: colors[index % colors.length],
                    borderColor: borderColors[index % borderColors.length]
                }));

                storageChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: chartData.map(item => item.label),
                        datasets: [{
                            data: chartData.map(item => item.data),
                            backgroundColor: chartData.map(item => item.backgroundColor),
                            borderColor: chartData.map(item => item.borderColor),
                            borderWidth: 2,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'right'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        const label = context.label || '';
                                        const value = context.parsed;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return `${label}: ${value} MB (${percentage}%)`;
                                    }
                                }
                            }
                        },
                        cutout: '60%',
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                });
            }

            /**
             * Update total storage used
             */
            function updateTotalStorageUsed(data) {
                const totalBytes = data.reduce((sum, item) => sum + item.total_size_bytes, 0);
                const totalStorageElement = document.getElementById('totalStorageUsed');

                // Format storage size
                let formattedSize;
                if (totalBytes >= (1024 * 1024 * 1024)) {
                    formattedSize = (totalBytes / (1024 * 1024 * 1024)).toFixed(2) + ' GB';
                } else if (totalBytes >= (1024 * 1024)) {
                    formattedSize = (totalBytes / (1024 * 1024)).toFixed(1) + ' MB';
                } else if (totalBytes >= 1024) {
                    formattedSize = (totalBytes / 1024).toFixed(1) + ' KB';
                } else {
                    formattedSize = totalBytes + ' B';
                }

                totalStorageElement.textContent = formattedSize;
            }

            function formatStorageSize(bytes) {
                const units = ['B', 'KB', 'MB', 'GB', 'TB'];
                let i = 0;
                while (bytes >= 1024 && i < units.length - 1) {
                    bytes /= 1024;
                    i++;
                }
                return `${bytes.toFixed(2)} ${units[i]}`;
            }
        });
    </script>
    <?= $this->endSection() ?>