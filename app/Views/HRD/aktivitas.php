<?= $this->extend('layout/hrd') ?>

<?= $this->section('content') ?>
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-semibold text-gray-800">Log Aktivitas File HRD</h1>
        </div>
        <div class="flex items-center space-x-4">
            <form action="<?= url_to('hrd_activity_logs') ?>" method="GET" class="relative">
                <input type="text"
                    name="search"
                    placeholder="Cari User/File/Aksi..."
                    class="w-80 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    value="<?= esc($searchQuery ?? '') ?>">
                <button type="submit" class="absolute right-3 top-2.5 h-5 w-5 text-gray-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </button>
            </form>
            <img src="<?= base_url('images/logo.png') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <form action="<?= url_to('hrd_activity_logs') ?>" method="GET">
        <div class="flex flex-col md:flex-row md:items-center md:justify-start gap-4">
            <div class="relative">
                <label for="startDate" class="sr-only">Tanggal Awal</label>
                <input type="text"
                    id="startDate"
                    name="start_date"
                    placeholder="Dari Tanggal"
                    class="w-40 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer pr-10"
                    value="<?= esc($startDate ?? '') ?>">
                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h.01M12 11h.01M15 11h.01M7 15h.01M11 15h.01M15 15h.01M17 19H7a2 2 0 01-2-2V5a2 2 0 012-2h10a2 2 0 012 2v12a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>

            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                </svg>
            </div>

            <div class="relative">
                <label for="endDate" class="sr-only">Tanggal Akhir</label>
                <input type="text"
                    id="endDate"
                    name="end_date"
                    placeholder="Ke Tanggal"
                    class="w-40 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer pr-10"
                    value="<?= esc($endDate ?? '') ?>">
                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h.01M12 11h.01M15 11h.01M7 15h.01M11 15h.01M15 15h.01M17 19H7a2 2 0 01-2-2V5a2 2 0 012-2h10a2 2 0 012 2v12a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm flex items-center gap-1 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V19a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                Filter
            </button>
            <?php if (!empty($startDate) || !empty($endDate) || !empty($searchQuery)): ?>
                <a href="<?= url_to('hrd_activity_logs') ?>" class="bg-gray-400 text-white px-4 py-2 rounded-lg hover:bg-gray-500 text-sm flex items-center gap-1 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Reset Filter
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="bg-white rounded-lg shadow-sm">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Log Aktivitas Terbaru
        </h2>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-max">
            <thead class="bg-blue-600">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Jabatan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Aksi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Target</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tipe Target</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Waktu</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($logs)): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900"><?= esc($log['user_name'] ?? 'N/A') ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900"><?= esc($log['role_name'] ?? 'N/A') ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <!-- 🔥 KODE BARU: Menampilkan action dengan lebih deskriptif -->
                                <?php
                                $actionText = ucfirst($log['action'] ?? 'N/A');
                                if ($log['action'] === 'rename') {
                                    $actionText = 'rename';
                                }
                                ?>
                                <span class="text-sm text-gray-900"><?= esc($actionText) ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php if (isset($log['target_type']) && $log['target_type'] == 'file'): ?>
                                        <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-sm text-gray-900"><?= esc($log['target_name'] ?? 'Tidak Ditemukan') ?></span>
                                    <?php elseif (isset($log['target_type']) && $log['target_type'] == 'folder'): ?>
                                        <svg class="w-5 h-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                                        </svg>
                                        <!-- 🔥 KODE BARU: Mengambil detail untuk log rename -->
                                        <?php
                                        $targetName = $log['target_name'] ?? 'Tidak Ditemukan';
                                        if ($log['action'] === 'rename_folder' && !empty($log['details'])) {
                                            $details = json_decode($log['details'], true);
                                            if (isset($details['old_name']) && isset($details['new_name'])) {
                                                $targetName = "Dari '{$details['old_name']}' menjadi '{$details['new_name']}'";
                                            }
                                        }
                                        ?>
                                        <span class="text-sm text-gray-900"><?= esc($targetName) ?></span>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-900">N/A</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900"><?= esc(ucfirst($log['target_type'] ?? 'N/A')) ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= esc(date('d M Y H:i:s', strtotime($log['timestamp']))) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">Tidak ada data log yang tersedia.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr("#startDate", {
            dateFormat: "d F Y",
            altInput: true,
            altFormat: "d F Y",
        });
        flatpickr("#endDate", {
            dateFormat: "d F Y",
            altInput: true,
            altFormat: "d F Y",
        });
    });
</script>

<?= $this->endSection() ?>