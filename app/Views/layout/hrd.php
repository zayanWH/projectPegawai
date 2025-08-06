<!-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="<?= base_url('css/output.css') ?>" rel="stylesheet">
    <script>
        // Pastikan ini ada dan ditempatkan sebelum createFolder.js dimuat
        window.baseUrl = '<?= base_url() ?>';
        // Jika Anda juga menggunakan currentFolderId atau currentUserId di JS
        // window.currentFolderId = <?= isset($folderId) ? json_encode($folderId) : 'null' ?>;
        // window.currentUserId = <?= session()->get('user_id') ? json_encode(session()->get('user_id')) : 'null' ?>;
    </script>
</head>

<body class="bg-gray-100 font-poppins">
    <div class="flex h-screen overflow-hidden">
        <aside class="w-64 bg-[#161F32] flex-shrink-0 p-4 flex flex-col">
            <div
                class="w-20 h-20 bg-blue-600 text-white rounded-full flex items-center justify-center mx-auto mb-6 text-2xl font-bold">
                A
            </div>

            <ul class="space-y-1 text-base flex-1">
                <li>
                    <a href="<?= site_url('hrd/dashboard') ?>"
                        class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                        <img src="<?= base_url('images/dashboard.png') ?>" alt="Dashboard Icon"
                            class="w-6 h-6 filter invert">
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="<?= site_url('hrd/dokumen-staff') ?>"
                        class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                        <img src="<?= base_url('images/dokumenJabatan.png') ?>" alt="Dokumen Staff Icon"
                            class="w-6 h-6 filter invert">
                        <span>Dokumen Staff</span>
                    </a>
                </li>
                <li>
                    <a href="<?= site_url('hrd/dokumen-spv') ?>"
                        class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                        <img src="<?= base_url('images/dokumenJabatan.png') ?>" alt="Dokumen Bersama Icon"
                            class="w-6 h-6 filter invert">
                        <span>Dokumen SPV</span>
                    </a>
                </li>
                <li>
                    <a href="<?= site_url('hrd/dokumen-manager') ?>"
                        class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                        <img src="<?= base_url('images/dokumenJabatan.png') ?>" alt="Dokumen Umum Icon"
                            class="w-6 h-6 filter invert">
                        <span>Dokumen Manager</span>
                    </a>
                </li>

                <li>
                    <a href="<?= site_url('hrd/dokumen-direksi') ?>"
                        class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                        <img src="<?= base_url('images/dokumenJabatan.png') ?>" alt="Dashboard Icon"
                            class="w-6 h-6 filter invert">
                        <span>Dokumen Direksi</span>
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('umum/dokumen-bersama') ?>"
                        class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-users-icon lucide-users">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <path d="M16 3.128a4 4 0 0 1 0 7.744" />
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                            <circle cx="9" cy="7" r="4" />
                        </svg>
                        <span>Share Folder</span>
                    </a>
                </li>
                <li>
                    <a href="<?= site_url('hrd/dokumen-umum') ?>"
                        class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                        <img src="<?= base_url('images/dokumenUmum.png') ?>" alt="Dokumen Bersama Icon"
                            class="w-6 h-6 filter invert">
                        <span>Dokumen Umum</span>
                    </a>
                </li>
                <li>
                    <a href="<?= site_url('hrd/aktivitas') ?>"
                        class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                        <img src="<?= base_url('images/clock.png') ?>" alt="Dokumen Umum Icon"
                            class="w-6 h-6 filter invert">
                        <span>Aktivitas</span>
                    </a>
                </li>
            </ul>

            <a href="<?= site_url('logout') ?>"
                class="bg-red-600 hover:bg-red-700 text-white text-center font-medium py-2 rounded-md mt-4">
                Logout
            </a>
        </aside>

        <div class="flex flex-col flex-1">
            <main class="flex-1 overflow-y-auto p-6">
                <?= $this->renderSection('content') ?>
            </main>
        </div>
    </div>

    <div id="floatingMenu"
        class="fixed w-48 bg-white rounded-xl shadow-lg hidden transition ease-out duration-200 transform scale-95 opacity-0 z-[9998]">
        <ul class="text-sm text-gray-700 divide-y divide-gray-100">
            <li onclick="showRenameModal()" class="flex items-center px-4 py-3 hover:bg-gray-100 cursor-pointer">
                <svg class="w-5 h-5 mr-3 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M17.414 2.586a2 2 0 00-2.828 0l-1.793 1.793 2.828 2.828 1.793-1.793a2 2 0 000-2.828zM2 13.586V17h3.414l9.793-9.793-2.828-2.828L2 13.586z" />
                </svg>
                Ganti nama
            </li>
            <li class="flex items-center px-4 py-3 hover:bg-gray-100 cursor-pointer">
                <svg class="w-5 h-5 mr-3 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 3a1 1 0 011-1h3v2H5v12h10V4h-2V2h3a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V3z" />
                </svg>
                Download
            </li>
            <li onclick="showInfoDetailModal()" class="flex items-center px-4 py-3 hover:bg-gray-100 cursor-pointer">
                <svg class="w-5 h-5 mr-3 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a7 7 0 100 14A7 7 0 009 2zM8 7h2v5H8V7zm1 8a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
                Informasi detail
            </li>
            <li class="flex items-center px-4 py-3 hover:bg-gray-100 cursor-pointer text-red-600">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M6 2a1 1 0 00-1 1v1H3a1 1 0 000 2h14a1 1 0 100-2h-2V3a1 1 0 00-1-1H6zM5 7a1 1 0 011 1v9a2 2 0 002 2h4a2 2 0 002-2V8a1 1 0 112 0v9a4 4 0 01-4 4H8a4 4 0 01-4-4V8a1 1 0 011-1z" />
                </svg>
                Hapus
            </li>
        </ul>
    </div>

    <div id="modalInfoDetail"
        class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/20 backdrop-blur-sm hidden">
        <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
            <h2 class="text-xl font-semibold mb-4">Detail Informasi File</h2>
            <div class="text-sm text-gray-800">
                <p class="mb-2"><strong>Jenis:</strong> <span id="detailJenis"></span></p>
                <p class="mb-2"><strong>Ukuran:</strong> <span id="detailUkuran"></span></p>
                <p class="mb-2"><strong>Pemilik:</strong> <span id="detailPemilik"></span></p>
                <p class="mb-2"><strong>Dibuat:</strong> <span id="detailDibuat"></span></p>
            </div>
            <div class="flex justify-end space-x-4 mt-4">
                <button onclick="closeInfoDetailModal()" class="text-blue-500">Tutup</button>
            </div>
        </div>
    </div>

    <div id="modalRename"
        class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/20 backdrop-blur-sm hidden">
        <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
            <h2 class="text-xl font-semibold mb-4">Ganti Nama File</h2>
            <label class="block text-sm font-medium mb-1">Nama Baru</label>
            <input type="text" id="newFileName" class="w-full border rounded-lg px-3 py-2 mb-4"
                placeholder="Masukkan nama baru">
            <div class="flex justify-end space-x-4">
                <button onclick="closeRenameModal()" class="text-blue-500">Batal</button>
                <button onclick="submitRename()" class="text-blue-600 font-semibold">Simpan</button>
            </div>
        </div>
    </div>


    <?php 
    // Jangan load popupFileFolder.js dan createFolder.js di halaman dokumen umum untuk menghindari konflik
    $currentUri = uri_string();
    if (strpos($currentUri, 'dokumen-umum') === false): 
    ?>
    <script src="<?= base_url('js/popupFileFolder.js') ?>"></script>
    <script src="<?= base_url('js/createFolder.js') ?>"></script>
    <?php endif; ?>
    <script src="<?= base_url('js/titiktiga.js') ?>"></script>
    
    <!-- Load simple notification WebSocket client -->
    <script src="<?= base_url('assets/js/simple-notification-client.js') ?>"></script>
    <script>
        // Initialize Simple WebSocket with current user ID
        if (window.SimpleNotificationManager) {
            window.SimpleNotificationManager.init(<?= session()->get('user_id') ?? 'null' ?>);
        }
    </script>
    <script>
        // Set user ID for notification system
        document.body.dataset.userId = '<?= session()->get('user_id') ?? '' ?>';
        window.currentUserId = '<?= session()->get('user_id') ?? '' ?>';
    </script>


</body>

</html> -->