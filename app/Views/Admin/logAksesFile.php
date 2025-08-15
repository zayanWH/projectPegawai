<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<div class="w-full min-h-screen">

    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-semibold text-gray-800">Aktivitas Akses File</h1>
            </div>
            <div class="flex items-center space-x-4">
                <img src="<?= base_url('images/logo.jpg') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <form action="<?= url_to('DokumenControllerAdmin::logAksesFile') ?>" method="GET">
            <div class="flex flex-col md:flex-row md:items-center md:justify-start gap-4">
                <div class="relative">
                    <label for="startDate" class="sr-only">Tanggal Awal</label>
                    <input type="text" id="startDate" name="start_date" placeholder="Dari Tanggal"
                        class="w-40 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer pr-10"
                        value="<?= esc($startDate ?? '') ?>">
                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h.01M12 11h.01M15 11h.01M7 15h.01M11 15h.01M15 15h.01M17 19H7a2 2 0 01-2-2V5a2 2 0 012-2h10a2 2 0 012 2v12a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                </div>

                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </div>

                <div class="relative">
                    <label for="endDate" class="sr-only">Tanggal Akhir</label>
                    <input type="text" id="endDate" name="end_date" placeholder="Ke Tanggal"
                        class="w-40 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer pr-10"
                        value="<?= esc($endDate ?? '') ?>">
                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h.01M12 11h.01M15 11h.01M7 15h.01M11 15h.01M15 15h.01M17 19H7a2 2 0 01-2-2V5a2 2 0 012-2h10a2 2 0 012 2v12a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                </div>

                <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm flex items-center gap-1 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V19a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                        </path>
                    </svg>
                    Filter
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Waktu
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama
                            User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Jabatan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama
                            File</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                Tidak ada data log akses yang ditemukan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= date('d M Y H:i', strtotime($log['timestamp'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= esc($log['user_name'] ?? 'N/A') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= esc($log['role_name'] ?? 'N/A') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php
                                        $fileExtension = pathinfo($log['file_name'], PATHINFO_EXTENSION);
                                        $iconSrc = '';

                                        switch (strtolower($fileExtension)) {
                                            case 'pptx':
                                                $iconSrc = base_url('images/ppt.png');
                                                break;
                                            case 'docx':
                                                $iconSrc = base_url('images/word.png');
                                                break;
                                            case 'xlsx':
                                                $iconSrc = base_url('images/excell.png');
                                                break;
                                            case 'pdf':
                                                $iconSrc = base_url('images/pdf.png');
                                                break;
                                            case 'png':
                                            case 'jpg':
                                            case 'jpeg':
                                            case 'gif':
                                                $iconSrc = base_url('images/image.png');
                                                break;
                                            default:
                                                // Ikon default jika ekstensi tidak dikenali
                                                $iconSrc = base_url('images/file-default.png');
                                                break;
                                        }
                                        ?>
                                        <img src="<?= $iconSrc ?>" alt="<?= esc($fileExtension) ?> File Icon"
                                            class="w-5 h-5 mr-2">
                                        <span class="text-sm text-gray-900"><?= esc($log['file_name']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= esc(ucfirst($log['aksi'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Inisialisasi Flatpickr untuk startDate
        flatpickr("#startDate", {
            dateFormat: "d F Y", // Format tanggal seperti "1 July 2025"
            // Jika ada nilai default dari controller, gunakan itu
            defaultDate: "<?= esc($startDate ?? '') ?>" ? "<?= esc($startDate) ?>" : null
        });

        // Inisialisasi Flatpickr untuk endDate
        flatpickr("#endDate", {
            dateFormat: "d F Y", // Format tanggal seperti "1 July 2025"
            // Jika ada nilai default dari controller, gunakan itu
            defaultDate: "<?= esc($endDate ?? '') ?>" ? "<?= esc($endDate) ?>" : null
        });
    });
</script>

<?= $this->endSection() ?>