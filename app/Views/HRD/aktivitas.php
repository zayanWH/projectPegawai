<?= $this->extend('layout/main') ?>

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
            <img src="<?= base_url('images/logo.jpg') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
        </div>
    </div>
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
                                <?php
                                $actionText = ucfirst($log['action'] ?? 'N/A');
                                if ($log['action'] === 'rename_folder') {
                                    $actionText = 'Rename Folder';
                                } elseif ($log['action'] === 'create_folder') {
                                    $actionText = 'Buat Folder';
                                } elseif ($log['action'] === 'delete_folder') {
                                    $actionText = 'Hapus Folder';
                                }
                                ?>
                                <span class="text-sm text-gray-900"><?= esc($actionText) ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php if (isset($log['target_type']) && $log['target_type'] == 'file'): ?>
                                        <?php
                                        $fileExtension = pathinfo($log['target_name'] ?? '', PATHINFO_EXTENSION);
                                        $iconSrc = '';
                                        switch (strtolower($fileExtension)) {
                                            case 'pdf': $iconSrc = base_url('images/pdf.png'); break;
                                            case 'doc': case 'docx': $iconSrc = base_url('images/word.png'); break;
                                            case 'xls': case 'xlsx': $iconSrc = base_url('images/excel.png'); break;
                                            case 'pptx': $iconSrc = base_url('images/ppt.png'); break;
                                            case 'png': case 'jpg': case 'jpeg': case 'gif': $iconSrc = base_url('images/image.png'); break;
                                            default: $iconSrc = base_url('images/file-default.png'); break;
                                        }
                                        ?>
                                        <img src="<?= $iconSrc ?>" alt="File Icon" class="w-5 h-5 mr-2">
                                        <span class="text-sm text-gray-900"><?= esc($log['target_name'] ?? 'Tidak Ditemukan') ?></span>
                                    <?php elseif (isset($log['target_type']) && $log['target_type'] == 'folder'): ?>
                                        <img src="<?= base_url('images/folder.png') ?>" alt="Folder Icon" class="w-5 h-5 mr-2">
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