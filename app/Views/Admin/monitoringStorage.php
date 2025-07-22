<?= $this->extend('layout/admin') ?>

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

<div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Penggunaan Storage Berdasarkan Jabatan</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-600">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Jabatan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Jumlah User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Total File</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Total Size</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Staff</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">120</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">725</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2.5GB</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Supervisor</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">83</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">621</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">1.3GB</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Manager</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">20</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">421</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">700mb</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Direksi</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">4</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">285</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">248mb</td>
                </tr>
                </tbody>
        </table>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Penggunaan Storage Berdasarkan User (Top 5)</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-600">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Jabatan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Total File</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Total Size</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Ahmad Alpudin</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Staff</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">725</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2.5GB</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Basar Jengka</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Supervisor</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">621</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">1.3GB</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dacita Bagas</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Manager</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">421</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">700mb</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Georgina Siu</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Direksi</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">285</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">248mb</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Lionel Messi</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Direksi</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">285</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">248mb</td>
                </tr>
                </tbody>
        </table>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">File Terbesar</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-600">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama File</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Ukuran</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Uploader</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Jabatan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tanggal Upload</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm text-gray-900">flutter.sdk</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2.5GB</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Agus Apip</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Staff</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2 Juli 2025</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-4 4 4 4-4v4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm text-gray-900">iduladha.mp4</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">1.3GB</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">dadang Ducment</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Supervisor</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2 Juli 2025</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zM11 6V3.586L14.414 7H12a2 2 0 01-2-2z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm text-gray-900">keuangan.docx</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">700mb</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Malin Kundang</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Manager</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">03 Juni 2025</td>
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