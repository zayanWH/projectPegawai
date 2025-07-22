<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-semibold text-gray-800">Aktivitas Akses File</h1>
        </div>
        <div class="flex items-center space-x-4">
            <img src="<?= base_url('images/logo.png') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-start gap-4">
        <div class="relative">
            <label for="startDate" class="sr-only">Tanggal Awal</label>
            <input type="text"
                   id="startDate"
                   name="start_date"
                   placeholder="Pilih Tanggal Awal"
                   class="w-40 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer pr-10" value="1 July 2025"
                   >
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
                   placeholder="Pilih Tanggal Akhir"
                   class="w-40 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer pr-10" value="1 July 2025"
                   >
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
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-600">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Waktu</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Jabatan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama File</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">3 July 2025 14:12</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Nizar</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Staff</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm text-gray-900">libur.pdf</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">View</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">3 July 2025 14:12</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Messi</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Manager</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zM11 6V3.586L14.414 7H12a2 2 0 01-2-2zM6 10a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm0 3a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm text-gray-900">uang.docx</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Download</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">3 July 2025 14:12</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Ronaldo</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Direksi</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zM11 6V3.586L14.414 7H12a2 2 0 01-2-2z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm text-gray-900">laporan.docx</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">View</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">3 July 2025 14:12</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Neymar</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">SPV</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-orange-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm6 6a1 1 0 01-1 1H7a1 1 0 110-2h2a1 1 0 011 1zm0 3a1 1 0 01-1 1H7a1 1 0 110-2h2a1 1 0 011 1z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm text-gray-900">grup5.ppt</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Acces Failed</td>
                </tr>
                </tbody>
        </table>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    flatpickr("#startDate", {
        dateFormat: "d F Y", // Format tanggal seperti "1 July 2025"
        defaultDate: "2025-07-01" // Set default date to 1 July 2025
    });

    flatpickr("#endDate", {
        dateFormat: "d F Y", // Format tanggal seperti "1 July 2025"
        defaultDate: "2025-07-01" // Set default date to 1 July 2025
    });
});
</script>

<?= $this->endSection() ?>