<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-semibold text-gray-800">Dashboard Admin</h1>
        </div>
        <div class="flex items-center space-x-4">
            <div class="relative">
                <input type="text"
                       placeholder="Masukkan file dokumen..."
                       class="w-80 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <img src="<?= base_url('images/logo.png') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
        </div>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Total Folder</p>
                <p class="text-2xl font-semibold text-gray-800">10</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 3h8l5 5v13a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M14 3v5h5" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Total File</p>
                <p class="text-2xl font-semibold text-gray-800">100</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M3 15a4 4 0 014-4h.26a6 6 0 0111.48 0H20a3 3 0 010 6H7a4 4 0 01-4-4z" />
</svg>

            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Storage</p>
                <p class="text-2xl font-semibold text-gray-800">2GB/5GB</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500"> <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M5.121 17.804A7 7 0 0112 15a7 7 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
</svg>

            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Total User</p>
                <p class="text-2xl font-semibold text-gray-800">250</p>
            </div>
        </div>
    </div>
</div>

<!-- Card Grafik Penggunaan Storage Berdasarkan Jabatan -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            Penggunaan Storage Berdasarkan Jabatan
        </h2>
        <div class="flex items-center space-x-2">
            <div class="text-sm text-gray-600">
                <span class="font-medium">Total: </span>
                <span class="text-blue-600 font-semibold">2.4GB / 5GB</span>
            </div>
            <div class="flex items-center space-x-1">
                <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Grafik -->
        <div class="flex justify-center items-center">
            <canvas id="storageChart" width="300" height="300"></canvas>
        </div>
        
        <!-- Statistik Detail -->
        <div class="space-y-4">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-blue-500 rounded-full mr-3"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Staff</p>
                            <p class="text-xs text-gray-500">45 pengguna</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-blue-600">800MB</p>
                        <p class="text-xs text-gray-500">33.3%</p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-4 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-green-500 rounded-full mr-3"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Manager</p>
                            <p class="text-xs text-gray-500">12 pengguna</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-green-600">600MB</p>
                        <p class="text-xs text-gray-500">25%</p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-lg p-4 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-yellow-500 rounded-full mr-3"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Supervisor</p>
                            <p class="text-xs text-gray-500">8 pengguna</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-yellow-600">500MB</p>
                        <p class="text-xs text-gray-500">20.8%</p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg p-4 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-purple-500 rounded-full mr-3"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Admin</p>
                            <p class="text-xs text-gray-500">5 pengguna</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-purple-600">500MB</p>
                        <p class="text-xs text-gray-500">20.8%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Log Akses Terbaru
        </h2>
    </div>

    <div class="p-6"> <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="relative flex-1 min-w-[120px] max-w-[200px]"> <label for="jenis" class="sr-only">Jenis</label>
                <select id="jenis" name="jenis" class="appearance-none border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-full px-4 py-2 pr-10">
                    <option value="">Jenis</option>
                    <option value="pdf" <?= request()->getGet('jenis') == 'pdf' ? 'selected' : '' ?>>PDF</option>
                    <option value="doc" <?= request()->getGet('jenis') == 'doc' ? 'selected' : '' ?>>Word</option>
                    <option value="xls" <?= request()->getGet('xls') == 'xls' ? 'selected' : '' ?>>Excel</option>
                    </select>
                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <div class="relative flex-1 min-w-[180px] max-w-[250px]">
                <label for="orang" class="sr-only">Orang</label>
                <select id="orang" name="orang"
                    class="appearance-none w-full border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 pr-10 truncate">
                    <option value="">Orang</option>
                    <option value="user1@gmail.com" <?= request()->getGet('orang') == 'user1@gmail.com' ? 'selected' : '' ?>>user1@gmail.com</option>
                    <option value="user2@gmail.com" <?= request()->getGet('orang') == 'user2@gmail.com' ? 'selected' : '' ?>>user2@gmail.com</option>
                    <option value="user3@gmail.com" <?= request()->getGet('orang') == 'user3@gmail.com' ? 'selected' : '' ?>>user3@gmail.com</option>
                    </select>
                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <div class="relative flex-1 min-w-[140px] max-w-[200px]">
                <label for="modifikasi" class="sr-only">Dimodifikasi</label>
                <select id="modifikasi" name="modifikasi" class="appearance-none border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-full px-4 py-2 pr-10">
                    <option value="">Dimodifikasi</option>
                    <option value="today" <?= request()->getGet('modifikasi') == 'today' ? 'selected' : '' ?>>Hari ini</option>
                    <option value="week" <?= request()->getGet('modifikasi') == 'week' ? 'selected' : '' ?>>Minggu ini</option>
                    <option value="month" <?= request()->getGet('modifikasi') == 'month' ? 'selected' : '' ?>>Bulan ini</option>
                    </select>
                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm flex-shrink-0">
                Terapkan
            </button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jabatan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama File</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Folder</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                          <div class="w-5 h-5 bg-red-500 rounded-full mr-2"></div>
                          <span class="text-sm text-gray-900">user@gmail.com</span>
                      </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Staff</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Download</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-900">laporan.pdf</span>
                      </div>
                    </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                          <svg class="w-5 h-5 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M2 6a2 2 0 012-2h5l2 2h7a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                            </svg>
                            <span class="text-sm text-gray-900">I. SOP</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2 Jul 2024</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('storageChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Staff', 'Manager', 'Supervisor', 'Admin'],
            datasets: [{
                data: [800, 600, 500, 500], // dalam MB
                backgroundColor: [
                    '#3B82F6', // Blue
                    '#10B981', // Green
                    '#F59E0B', // Yellow
                    '#8B5CF6'  // Purple
                ],
                borderColor: [
                    '#1E40AF',
                    '#059669',
                    '#D97706',
                    '#7C3AED'
                ],
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
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value}MB (${percentage}%)`;
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
});
</script>
<?= $this->endSection() ?>