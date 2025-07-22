<?= $this->extend('layout/hrd') ?>

<?= $this->section('content') ?>
<!-- Header Dokumen -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-semibold text-gray-800">Aktivitas</h1>
        </div>
        <div class="flex items-center space-x-4">
            <!-- Search Bar -->
            <div class="relative">
                <input type="text" 
                       placeholder="Masukkan file dokumen..." 
                       class="w-80 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <!-- Logo -->
            <img src="<?= base_url('images/logo.png') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
        </div>
    </div>
</div>

<!-- Filter Dokumen -->
<div class="mb-6">
    <form method="GET" class="flex flex-wrap gap-4">
        <!-- Dropdown Wrapper -->
        <div class="relative">
            <select name="jenis" class="appearance-none border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 pr-10">
                <option value="">Jenis</option>
                <option value="pdf" <?= request()->getGet('jenis') == 'pdf' ? 'selected' : '' ?>>PDF</option>
                <option value="doc" <?= request()->getGet('jenis') == 'doc' ? 'selected' : '' ?>>Word</option>
                <option value="xls" <?= request()->getGet('jenis') == 'xls' ? 'selected' : '' ?>>Excel</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>

        <div class="relative w-[180px]">
            <select name="orang"
                class="appearance-none w-full border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 pr-10 truncate">
                <option value="">Orang</option>
                <option value="user2@gmail.com" <?= request()->getGet('orang') == 'user2@gmail.com' ? 'selected' : '' ?>>user1@gmail.com</option>
                <option value="user2@gmail.com" <?= request()->getGet('orang') == 'user2@gmail.com' ? 'selected' : '' ?>>user2@gmail.com</option>
                <option value="user3@gmail.com" <?= request()->getGet('orang') == 'user3@gmail.com' ? 'selected' : '' ?>>user3@gmail.com</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>


        <div class="relative">
            <select name="modifikasi" class="appearance-none border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 pr-10">
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

        <!-- Tombol Submit -->
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm">
            Terapkan
        </button>
    </form>
</div>


<!-- Tabel Dokumen -->
<div class="bg-white rounded-lg shadow-sm">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Dokumen Terbaru
        </h2>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-blue-600">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Jabatan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Aksi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama File</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Folder</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Waktu</th>
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
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <span class="text-sm text-gray-900">Staff</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <span class="text-sm text-gray-900">Upload</span>
                        </div>
                    </td>
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
                            <span class="text-sm text-gray-900">Dokumen Staff</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2 Jul 2024</td>
                    
                </tr>
               </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
