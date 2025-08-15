<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-4 sm:mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between space-y-3 sm:space-y-0">
        <div class="flex items-center space-x-2 sm:space-x-4">
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-800">Dashboard Admin</h1>
        </div>
        <div class="flex items-center space-x-2 sm:space-x-4 self-end sm:self-auto">
            <img src="<?= base_url('images/logo.jpg') ?>" alt="Logo USSI" class="h-8 sm:h-10 w-auto rounded-lg">
        </div>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-4 sm:mb-6">
    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 border-l-4 border-yellow-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <img src="<?= base_url('images/folder.png') ?>" alt="Folder Icon" class="w-8 h-7 mr-2">
            </div>
            <div class="ml-3 sm:ml-4">
                <p class="text-xs sm:text-sm text-gray-600">Total Folder</p>
                <p class="text-lg sm:text-2xl font-semibold text-gray-800"><?= esc($totalFolders ?? 0) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 border-l-4 border-blue-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <img src="<?= base_url('images/file-default.png') ?>" alt="File Icon" class="w-8 h-7 mr-2">
            </div>
            <div class="ml-3 sm:ml-4">
                <p class="text-xs sm:text-sm text-gray-600">Total File</p>
                <p class="text-lg sm:text-2xl font-semibold text-gray-800"><?= esc($totalFiles ?? 0) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 border-l-4 border-green-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 15a4 4 0 014-4h.26a6 6 0 0111.48 0H20a3 3 0 010 6H7a4 4 0 01-4-4z" />
                </svg>
            </div>
            <div class="ml-3 sm:ml-4">
                <p class="text-xs sm:text-sm text-gray-600">Storage</p>
                <p class="text-sm sm:text-xl font-semibold text-gray-800" id="totalStorageCard">
                    <?= esc($formattedUsedStorage ?? '0 B') ?> / <?= esc($formattedTotalLimit ?? '0 GB') ?>
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 border-l-4 border-red-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <img src="<?= base_url('images/totalusers.png') ?>" alt="User Icon" class="w-8 h-7 mr-2">
            </div>
            <div class="ml-3 sm:ml-4">
                <p class="text-xs sm:text-sm text-gray-600">Total User</p>
                <p class="text-lg sm:text-2xl font-semibold text-gray-800"><?= esc($totalUser ?? 0) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-4 sm:mb-6">
    <div
        class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 sm:mb-6 space-y-3 sm:space-y-0">
        <h2 class="text-base sm:text-lg font-semibold text-gray-800 flex items-center">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                </path>
            </svg>
            <span class="hidden sm:inline">Penggunaan Storage Berdasarkan Jabatan</span>
            <span class="sm:hidden">Storage per Jabatan</span>
        </h2>
        <div
            class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-4 w-full sm:w-auto">
            <div class="text-xs sm:text-sm text-gray-600">
                <span class="font-medium">Total: </span>
                <span class="text-blue-600 font-semibold" id="totalStorageDisplay">
                    <?= esc($formattedUsedStorage ?? '0 B') ?> / <?= esc($formattedTotalLimit ?? '0 GB') ?>
                </span>
            </div>
            <div class="flex items-center space-x-1">
                <div class="w-2 h-2 sm:w-3 sm:h-3 bg-blue-500 rounded-full"></div>
                <div class="w-2 h-2 sm:w-3 sm:h-3 bg-green-500 rounded-full"></div>
                <div class="w-2 h-2 sm:w-3 sm:h-3 bg-yellow-500 rounded-full"></div>
                <div class="w-2 h-2 sm:w-3 sm:h-3 bg-purple-500 rounded-full"></div>
                <div class="w-2 h-2 sm:w-3 sm:h-3 bg-red-500 rounded-full"></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <div class="flex justify-center items-center">
            <div class="w-full max-w-xs sm:max-w-sm lg:max-w-md">
                <div class="relative" style="height: 250px;">
                    <canvas id="storageChart" class="w-full h-full"></canvas>
                </div>
            </div>
        </div>

        <div class="space-y-3 sm:space-y-4" id="storageStatistics">
            <div class="flex justify-center items-center h-32">
                <div class="animate-spin rounded-full h-6 w-6 sm:h-8 sm:w-8 border-b-2 border-blue-500"></div>
                <span class="ml-2 text-sm sm:text-base text-gray-600">Memuat data storage...</span>
            </div>
        </div>
    </div>
</div>

<div id="errorAlert" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 sm:mb-6"
    role="alert">
    <strong class="font-bold">Error!</strong>
    <span class="block sm:inline text-sm" id="errorMessage">Terjadi kesalahan saat memuat data.</span>
</div>

<div class="bg-white rounded-lg shadow-sm">
    <div class="p-4 sm:p-6 border-b border-gray-200">
        <h2 class="text-base sm:text-lg font-semibold text-gray-800 flex items-center">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
            Log Akses Terbaru
        </h2>
    </div>

    <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
            <thead class="bg-blue-600">
                <tr>
                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">User
                    </th>
                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                        Jabatan</th>
                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Aksi
                    </th>
                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama
                        File</th>
                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                        Folder</th>
                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                        Waktu</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($latestLogs)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            Tidak ada data log akses terbaru yang ditemukan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($latestLogs as $log): ?>
                        <tr>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= esc($log['user_name'] ?? 'Guest') ?>
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= esc($log['role_name'] ?? 'N/A') ?>
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= esc($log['aksi']) ?></td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <?php
                                    $fileExtension = pathinfo($log['file_name'] ?? '', PATHINFO_EXTENSION);
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
                                    <span><?= esc($log['file_name'] ?? 'Tidak Ditemukan') ?></span>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <img src="<?= base_url('images/folder.png') ?>" alt="Folder Icon" class="w-5 h-5 mr-2">
                                    <span><?= esc($log['folder_name'] ?? 'N/A') ?></span>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= date('d M Y H:i', strtotime($log['timestamp'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="md:hidden divide-y divide-gray-200">
        <?php if (empty($latestLogs)): ?>
            <div class="p-4 text-center text-gray-500">
                Tidak ada data log akses terbaru yang ditemukan.
            </div>
        <?php else: ?>
            <?php foreach ($latestLogs as $log): ?>
                <div class="p-4 space-y-2">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900 text-sm"><?= esc($log['user_name'] ?? 'Guest') ?></p>
                            <p class="text-xs text-gray-600"><?= esc($log['role_name'] ?? 'N/A') ?></p>
                        </div>
                        <span class="inline-flex px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full ml-2">
                            <?= esc($log['aksi']) ?>
                        </span>
                    </div>
                    <div class="text-xs text-gray-600 space-y-1">
                        <p>
                            <span class="font-medium">File:</span>
                        <div class="flex items-center">
                            <?php
                            $fileExtension = pathinfo($log['file_name'] ?? '', PATHINFO_EXTENSION);
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
                            <img src="<?= $iconSrc ?>" alt="File Icon" class="w-4 h-4 mr-1">
                            <span><?= esc($log['file_name'] ?? 'Tidak Ditemukan') ?></span>
                        </div>
                        </p>
                        <p><span class="font-medium">Folder:</span> <?= esc($log['folder_name'] ?? 'N/A') ?></p>
                        <p><span class="font-medium">Waktu:</span> <?= date('d M Y H:i', strtotime($log['timestamp'])) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('storageChart').getContext('2d');
        let storageChart;

        // Fungsi helper untuk memformat ukuran byte ke unit yang lebih mudah dibaca (KB, MB, GB)
        function formatStorageSize(bytes) {
            const units = ['B', 'KB', 'MB', 'GB', 'TB'];
            let i = 0;
            let absoluteBytes = Math.abs(bytes);
            if (absoluteBytes === 0) return '0 B';

            while (absoluteBytes >= 1024 && i < units.length - 1) {
                absoluteBytes /= 1024;
                i++;
            }
            return `${absoluteBytes.toFixed(2)} ${units[i]}`;
        }

        // Fungsi untuk menampilkan error
        function showError(message) {
            const errorAlert = document.getElementById('errorAlert');
            const errorMessage = document.getElementById('errorMessage');
            errorMessage.textContent = message;
            errorAlert.classList.remove('hidden');
        }

        // Fungsi untuk menyembunyikan error
        function hideError() {
            const errorAlert = document.getElementById('errorAlert');
            errorAlert.classList.add('hidden');
        }

        // Fungsi untuk memuat data storage berdasarkan jabatan
        async function loadStorageData() {
            try {
                hideError();

                const response = await fetch('<?= base_url('admin/getStorageByPosition') ?>', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();

                if (result.status === 'success') {
                    const dataFromAPI = result.data || [];
                    const totalUsedBytes = dataFromAPI.reduce((sum, item) => sum + (item.total_size_bytes || 0), 0);
                    const totalLimitGB = 5;
                    const totalLimitBytes = totalLimitGB * 1024 * 1024 * 1024;

                    updateTotalStorageCards(totalUsedBytes, totalLimitBytes);
                    updateChart(dataFromAPI);
                    updateStatistics(dataFromAPI);
                } else {
                    throw new Error(result.message || 'Gagal memuat data storage');
                }
            } catch (error) {
                console.error('Error loading storage data:', error);
                showError('Gagal memuat data storage: ' + error.message);

                updateTotalStorageCards(0, 5 * 1024 * 1024 * 1024);
                updateChart([]);
                updateStatistics([]);
            }
        }

        // Fungsi untuk mengupdate card storage di bagian atas
        function updateTotalStorageCards(totalUsedBytes, totalLimitBytes) {
            const totalStorageCard = document.getElementById('totalStorageCard');
            const totalStorageDisplay = document.getElementById('totalStorageDisplay');

            const formattedUsedStorage = formatStorageSize(totalUsedBytes);
            const formattedTotalLimit = formatStorageSize(totalLimitBytes);

            totalStorageCard.innerHTML = `${formattedUsedStorage} / ${formattedTotalLimit}`;
            totalStorageDisplay.innerHTML = `${formattedUsedStorage} / ${formattedTotalLimit}`;
        }

        // Fungsi untuk update chart
        function updateChart(data) {
            const labels = data.map(item => item.jabatan);
            const chartData = data.map(item => (item.total_size_bytes || 0));
            const backgroundColors = getChartColors(data.length);
            const borderColors = getChartBorderColors(data.length);

            if (storageChart) {
                storageChart.destroy();
            }

            storageChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: chartData,
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const label = context.label || '';
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;

                                    const formattedValue = formatStorageSize(value);
                                    return `${label}: ${formattedValue} (${percentage}%)`;
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

        // Fungsi untuk update statistik detail
        function updateStatistics(data) {
            const statisticsContainer = document.getElementById('storageStatistics');

            if (data.length === 0) {
                statisticsContainer.innerHTML = `
                    <div class="text-center text-gray-500 py-6">
                        <svg class="w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <p class="text-sm">Belum ada data storage tersedia</p>
                    </div>
                `;
                return;
            }

            let statisticsHTML = '';
            const totalBytes = data.reduce((sum, item) => sum + (item.total_size_bytes || 0), 0);

            data.forEach((stat, index) => {
                const percentage = totalBytes > 0 ? (((stat.total_size_bytes || 0) / totalBytes) * 100).toFixed(1) : 0;
                const color = getChartColors(data.length)[index];
                const gradientClass = getGradientClass(color);
                const borderClass = getBorderClass(color);

                statisticsHTML += `
                    <div class="bg-gradient-to-r ${gradientClass} rounded-lg p-3 sm:p-4 border-l-4 ${borderClass}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 sm:w-4 sm:h-4 rounded-full mr-2 sm:mr-3" style="background-color: ${color}"></div>
                                <div>
                                    <p class="text-xs sm:text-sm font-medium text-gray-700">${stat.jabatan}</p>
                                    <p class="text-xs text-gray-500">${stat.jumlah_user} pengguna</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm sm:text-lg font-semibold" style="color: ${color}">${formatStorageSize(stat.total_size_bytes || 0)}</p>
                                <p class="text-xs text-gray-500">${percentage}%</p>
                            </div>
                        </div>
                    </div>
                `;
            });

            statisticsContainer.innerHTML = statisticsHTML;
        }

        // Helper functions untuk gradient dan border classes
        function getGradientClass(color) {
            const colorMap = {
                '#3B82F6': 'from-blue-50 to-blue-100',
                '#10B981': 'from-green-50 to-green-100',
                '#F59E0B': 'from-yellow-50 to-yellow-100',
                '#8B5CF6': 'from-purple-50 to-purple-100',
                '#EF4444': 'from-red-50 to-red-100',
                '#06B6D4': 'from-cyan-50 to-cyan-100'
            };
            return colorMap[color] || 'from-gray-50 to-gray-100';
        }

        function getBorderClass(color) {
            const colorMap = {
                '#3B82F6': 'border-blue-500',
                '#10B981': 'border-green-500',
                '#F59E0B': 'border-yellow-500',
                '#8B5CF6': 'border-purple-500',
                '#EF4444': 'border-red-500',
                '#06B6D4': 'border-cyan-500'
            };
            return colorMap[color] || 'border-gray-500';
        }

        function getChartColors(count) {
            const colors = ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444', '#06B6D4'];
            return colors.slice(0, Math.min(count, colors.length));
        }

        function getChartBorderColors(count) {
            const borderColors = ['#1E40AF', '#059669', '#D97706', '#7C3AED', '#DC2626', '#0891B2'];
            return borderColors.slice(0, Math.min(count, borderColors.length));
        }

        // Load data saat halaman dimuat
        loadStorageData();
    });
</script>
<?= $this->endSection() ?>