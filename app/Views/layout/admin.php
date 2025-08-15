<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= base_url('css/output.css') ?>" rel="stylesheet">
</head>

<body class="bg-gray-100 font-poppins">
    <div class="flex h-screen overflow-hidden">
        <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden hidden"></div>

        <aside id="sidebar"
            class="w-64 bg-[#161F32] flex-shrink-0 p-4 flex flex-col transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out fixed md:relative z-50">
            <div
                class="w-20 h-20 bg-blue-600 text-white rounded-full flex items-center justify-center mx-auto mb-6 text-2xl font-bold">
                A
            </div>

            <ul class="space-y-1 text-base flex-1">
                <li>
                    <a href="<?= site_url('admin/dashboard') ?>"
                        class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                        <img src="<?= base_url('images/dashboard.png') ?>" alt="Dashboard Icon"
                            class="w-6 h-6 filter invert">
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="<?= site_url('admin/manajemen-user') ?>"
                        class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                        <img src="<?= base_url('images/users.png') ?>" alt="Dokumen Staff Icon"
                            class="w-6 h-6 filter invert">
                        <span>Manajemen User</span>
                    </a>
                </li>
                <li>
                    <a href="<?= site_url('admin/manajemen-jabatan') ?>"
                        class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                        <img src="<?= base_url('images/id-card.png') ?>" alt="Dokumen Bersama Icon"
                            class="w-6 h-6 filter invert">
                        <span>Manajemen Jabatan</span>
                    </a>
                </li>
                <li>
                    <a href="<?= site_url('admin/monitoring-storage') ?>"
                        class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                        <img src="<?= base_url('images/cloud-computing.png') ?>" alt="Dokumen Umum Icon"
                            class="w-6 h-6 filter invert">
                        <span>Monitoring Storage</span>
                    </a>
                </li>
                <li>
                    <a href="<?= site_url('admin/log-akses-file') ?>"
                        class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                        <img src="<?= base_url('images/clock.png') ?>" alt="Dokumen Umum Icon"
                            class="w-6 h-6 filter invert">
                        <span>Log Akses File</span>
                    </a>
                </li>
            </ul>

            <a href="<?= site_url('logout') ?>"
                class="bg-red-600 hover:bg-red-700 text-white text-center font-medium py-2 rounded-md mt-4">
                Logout
            </a>
        </aside>

        <div class="flex flex-col flex-1">
            <header class="bg-white p-4 shadow-sm md:hidden flex items-center justify-between z-40">
                <button id="mobileMenuButton" class="text-gray-800">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <span class="text-lg font-semibold text-gray-800">Dashboard</span>
                <div class="w-6 h-6"></div> </header>

            <main class="flex-1 overflow-y-auto p-6">
                <?= $this->renderSection('content') ?>
            </main>
            <footer class="bg-[#1E293B] p-4 text-center text-sm text-white flex-shrink-0">
                &copy; <?= date('Y') ?> - Dashboard Footer
            </footer>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // === LOGIKA SIDEBAR RESPONSIVE ===
            const sidebar = document.getElementById('sidebar');
            const mobileMenuButton = document.getElementById('mobileMenuButton');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            mobileMenuButton.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                sidebarOverlay.classList.toggle('hidden');
            });

            sidebarOverlay.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            });

            // === MODAL TAMBAH USER ===
            const openAddUserModalBtn = document.getElementById('openAddUserModal');
            const modalAddUser = document.getElementById('modalAddUser');
            const cancelAddUserModalBtn = document.getElementById('cancelAddUserModal');

            if (openAddUserModalBtn) {
                openAddUserModalBtn.addEventListener('click', function () {
                    modalAddUser.classList.remove('hidden');
                });
            }

            if (cancelAddUserModalBtn) {
                cancelAddUserModalBtn.addEventListener('click', function () {
                    modalAddUser.classList.add('hidden');
                });
            }

            // Close modal when clicking outside
            if (modalAddUser) {
                modalAddUser.addEventListener('click', function (e) {
                    if (e.target === modalAddUser) {
                        modalAddUser.classList.add('hidden');
                    }
                });
            }


            // === MODAL EDIT USER ===
            const openEditUserModalBtns = document.querySelectorAll('.open-edit-user-modal');
            const modalEditUser = document.getElementById('modalEditUser');
            const cancelEditUserModalBtn = document.getElementById('cancelEditUserModal');

            openEditUserModalBtns.forEach(button => {
                button.addEventListener('click', function () {
                    // Anda bisa mengisi data user ke dalam form modal di sini
                    // Berdasarkan `data-user-id` yang ada pada tombol edit
                    const userId = this.getAttribute('data-user-id');
                    console.log('Edit User ID:', userId);
                    // Contoh mengisi data (ini harusnya diambil dari AJAX/database)
                    document.getElementById('editNamaLengkap').value = "Nizar Hadabi Erawan";
                    document.getElementById('editEmail').value = "nizarmanager@gmail.com";
                    document.getElementById('editJabatan').value = "Manager";
                    document.getElementById('editStatus').value = "Aktif";
                    modalEditUser.classList.remove('hidden');
                });
            });

            if (cancelEditUserModalBtn) {
                cancelEditUserModalBtn.addEventListener('click', function () {
                    modalEditUser.classList.add('hidden');
                });
            }

            // Close modal when clicking outside
            if (modalEditUser) {
                modalEditUser.addEventListener('click', function (e) {
                    if (e.target === modalEditUser) {
                        modalEditUser.classList.add('hidden');
                    }
                });
            }

            // === MODAL HAPUS USER ===
            const openDeleteUserModalBtns = document.querySelectorAll('.open-delete-user-modal');
            const modalDeleteUser = document.getElementById('modalDeleteUser');
            const cancelDeleteUserModalBtn = document.getElementById('cancelDeleteUserModal');
            const confirmDeleteUserBtn = document.getElementById('confirmDeleteUserBtn');

            openDeleteUserModalBtns.forEach(button => {
                button.addEventListener('click', function () {
                    const userId = this.getAttribute('data-user-id');
                    // Simpan userId untuk digunakan saat konfirmasi hapus
                    confirmDeleteUserBtn.setAttribute('data-confirm-user-id', userId);
                    console.log('Delete User ID:', userId);
                    modalDeleteUser.classList.remove('hidden');
                });
            });

            if (cancelDeleteUserModalBtn) {
                cancelDeleteUserModalBtn.addEventListener('click', function () {
                    modalDeleteUser.classList.add('hidden');
                });
            }

            // Aksi konfirmasi hapus
            if (confirmDeleteUserBtn) {
                confirmDeleteUserBtn.addEventListener('click', function () {
                    const userIdToDelete = this.getAttribute('data-confirm-user-id');
                    alert('User dengan ID ' + userIdToDelete + ' akan dihapus!');
                    // Di sini Anda akan melakukan AJAX request untuk menghapus user dari database
                    modalDeleteUser.classList.add('hidden');
                    // Refresh halaman atau hapus baris dari tabel secara dinamis
                });
            }

            // Close modal when clicking outside
            if (modalDeleteUser) {
                modalDeleteUser.addEventListener('click', function (e) {
                    if (e.target === modalDeleteUser) {
                        modalDeleteUser.classList.add('hidden');
                    }
                });
            }

            // === Toggle Password Visibility (untuk form Tambah dan Edit User) ===
            document.querySelectorAll('.toggle-password-visibility').forEach(button => {
                button.addEventListener('click', function () {
                    const passwordInput = this.previousElementSibling; // Ambil input password sebelumnya
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    // Ganti ikon mata
                    this.querySelector('svg').innerHTML = type === 'password' ?
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>' :
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.879 16.121zm-.707.707l-2.828-2.828a1 1 0 00-1.414 0l-2.828 2.828M9.5 7.5a2 2 0 10-4 0 2 2 0 004 0z"></path>';
                });
            });

            // === MODAL TAMBAH JABATAN ===
            const openAddJabatanModalBtn = document.getElementById('openAddJabatanModal');
            const modalAddJabatan = document.getElementById('modalAddJabatan');
            const cancelAddJabatanModalBtn = document.getElementById('cancelAddJabatanModal');

            if (openAddJabatanModalBtn) {
                openAddJabatanModalBtn.addEventListener('click', function () {
                    modalAddJabatan.classList.remove('hidden');
                });
            }

            if (cancelAddJabatanModalBtn) {
                cancelAddJabatanModalBtn.addEventListener('click', function () {
                    modalAddJabatan.classList.add('hidden');
                });
            }

            // Close modal when clicking outside
            if (modalAddJabatan) {
                modalAddJabatan.addEventListener('click', function (e) {
                    if (e.target === modalAddJabatan) {
                        modalAddJabatan.classList.add('hidden');
                    }
                });
            }

            // === MODAL EDIT JABATAN ===
            const openEditJabatanModalBtns = document.querySelectorAll('.open-edit-jabatan-modal');
            const modalEditJabatan = document.getElementById('modalEditJabatan');
            const cancelEditJabatanModalBtn = document.getElementById('cancelEditJabatanModal');

            openEditJabatanModalBtns.forEach(button => {
                button.addEventListener('click', function () {
                    const jabatanId = this.getAttribute('data-jabatan-id');
                    const namaJabatan = this.getAttribute('data-nama-jabatan');
                    const level = this.getAttribute('data-level');
                    const maxStorage = this.getAttribute('data-max-storage');

                    console.log('Edit Jabatan ID:', jabatanId);
                    document.getElementById('editNamaJabatan').value = namaJabatan;
                    document.getElementById('editLevel').value = level;
                    document.getElementById('editMaxStorage').value = maxStorage;

                    modalEditJabatan.classList.remove('hidden');
                });
            });

            if (cancelEditJabatanModalBtn) {
                cancelEditJabatanModalBtn.addEventListener('click', function () {
                    modalEditJabatan.classList.add('hidden');
                });
            }

            // Close modal when clicking outside
            if (modalEditJabatan) {
                modalEditJabatan.addEventListener('click', function (e) {
                    if (e.target === modalEditJabatan) {
                        modalEditJabatan.classList.add('hidden');
                    }
                });
            }

            // === MODAL HAPUS JABATAN ===
            const openDeleteJabatanModalBtns = document.querySelectorAll('.open-delete-jabatan-modal');
            const modalDeleteJabatan = document.getElementById('modalDeleteJabatan');
            const cancelDeleteJabatanModalBtn = document.getElementById('cancelDeleteJabatanModal');
            const confirmDeleteJabatanBtn = document.getElementById('confirmDeleteJabatanBtn');

            openDeleteJabatanModalBtns.forEach(button => {
                button.addEventListener('click', function () {
                    const jabatanId = this.getAttribute('data-jabatan-id');
                    // Simpan jabatanId untuk digunakan saat konfirmasi hapus
                    confirmDeleteJabatanBtn.setAttribute('data-confirm-jabatan-id', jabatanId);
                    console.log('Delete Jabatan ID:', jabatanId);
                    modalDeleteJabatan.classList.remove('hidden');
                });
            });

            if (cancelDeleteJabatanModalBtn) {
                cancelDeleteJabatanModalBtn.addEventListener('click', function () {
                    modalDeleteJabatan.classList.add('hidden');
                });
            }

            // Aksi konfirmasi hapus
            if (confirmDeleteJabatanBtn) {
                confirmDeleteJabatanBtn.addEventListener('click', function () {
                    const jabatanIdToDelete = this.getAttribute('data-confirm-jabatan-id');
                    alert('Jabatan dengan ID ' + jabatanIdToDelete + ' akan dihapus!');
                    // Di sini Anda akan melakukan AJAX request untuk menghapus jabatan dari database
                    modalDeleteJabatan.classList.add('hidden');
                    // Refresh halaman atau hapus baris dari tabel secara dinamis
                });
            }

            // Close modal when clicking outside
            if (modalDeleteJabatan) {
                modalDeleteJabatan.addEventListener('click', function (e) {
                    if (e.target === modalDeleteJabatan) {
                        modalDeleteJabatan.classList.add('hidden');
                    }
                });
            }
        });
    </script>
</body>

</html>