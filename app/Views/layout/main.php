<!DOCTYPE html>
<html lang="en">
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script>
        window.baseUrl = '<?= base_url() ?>';
        console.log('main.php: window.baseUrl defined as', window.baseUrl);
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="<?= base_url('css/output.css') ?>" rel="stylesheet">
</head>

<body class="bg-gray-100 font-poppins">

    <div id="sidebar-overlay" class="fixed inset-0 z-40 hidden md:hidden bg-black/50"></div>

    <div class="flex h-screen overflow-hidden">

        <button id="hamburger-btn" class="fixed top-4 left-4 z-50 p-2 rounded-md text-black
                                            opacity-0 pointer-events-none transition-opacity duration-300 ease-in-out
                                            md:hidden"> <svg class="w-6 h-6" fill="none" stroke="currentColor"
                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                </path>
            </svg>
        </button>

        <aside id="sidebar" class="w-64 bg-[#161F32] flex-shrink-0 p-4 flex-col
                                    hidden md:flex
                                    fixed inset-y-0 left-0 z-50
                                    transform -translate-x-full opacity-0 scale-95 md:translate-x-0 md:opacity-100 md:scale-100
                                    transition-all duration-300 ease-in-out">
            <?php
            // Memastikan layanan sesi diinisialisasi
            $session = \Config\Services::session();

            // Mengambil nama pengguna dari sesi, default string kosong jika tidak ada
            $userName = $session->get('user_name') ?? '';

            // Menentukan inisial, default '-' jika nama pengguna kosong
            $initial = '-';
            if (!empty($userName)) {
                $initial = strtoupper(substr($userName, 0, 1));
            }
            ?>
            <div
                class="w-20 h-20 bg-blue-600 text-white rounded-full flex items-center justify-center mx-auto mb-6 text-2xl font-bold">
                <?= esc($initial) ?>
            </div>

            <?php
            // --- BAGIAN KRITIS UNTUK MEMPERBAIKI ERROR 'Undefined variable: role' ---
            // 1. Ambil role_id (integer) dari sesi. Ini adalah kunci yang benar yang disimpan di AuthController.
            $currentRoleId = $session->get('role_id');

            // 2. Inisialisasi variabel peran dengan nilai default 'guest'.
            // Ini penting agar $currentRole selalu terdefinisi, bahkan jika tidak ada role_id di sesi.
            $currentRole = 'guest';

            // 3. Lakukan pemetaan role_id (angka) ke nama peran (string)
            // Ini memastikan $currentRole memiliki nilai string ('staff', 'supervisor', dll.) yang cocok dengan logika tampilan.
            if ($currentRoleId !== null) {
                switch ($currentRoleId) {
                    case 1:
                        $currentRole = 'admin';
                        break;
                    case 2:
                        $currentRole = 'hrd';
                        break;
                    case 3:
                        $currentRole = 'direksi';
                        break;
                    case 4:
                        $currentRole = 'manajer';
                        break;
                    case 5:
                        $currentRole = 'supervisor';
                        break;
                    case 6:
                        $currentRole = 'staff';
                        break;
                    default:
                        // Jika role_id dari sesi tidak cocok dengan kasus yang diketahui
                        $currentRole = 'guest';
                        break;
                }
            }
            // --- AKHIR BAGIAN KRITIS ---
            ?>

            <?php if ($currentRole === 'admin'): ?>
                <ul class="space-y-1 text-base flex-1">
                    <li>
                        <a href="<?= site_url('admin/dashboard') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-layout-dashboard">
                                <rect width="7" height="9" x="3" y="3" rx="1" />
                                <rect width="7" height="5" x="14" y="3" rx="1" />
                                <rect width="7" height="9" x="14" y="12" rx="1" />
                                <rect width="7" height="5" x="3" y="16" rx="1" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url('admin/manajemen-user') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-users-icon lucide-users">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <path d="M16 3.128a4 4 0 0 1 0 7.744" />
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                <circle cx="9" cy="7" r="4" />
                            </svg>
                            <span>Manajemen User</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url('admin/manajemen-jabatan') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-briefcase">
                                <rect width="20" height="14" x="2" y="7" rx="2" ry="2" />
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                            </svg>
                            <span>Manajemen Jabatan</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url('admin/monitoring-storage') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-hard-drive">
                                <line x1="22" x2="2" y1="12" y2="12" />
                                <path
                                    d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 2H7.24a2 2 0 0 0-1.79 3.11z" />
                                <line x1="6" x2="6.01" y1="16" y2="16" />
                                <line x1="10" x2="10.01" y1="16" y2="16" />
                            </svg>
                            <span>Monitoring Storage</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url('admin/log-akses-file') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-file-text">
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
                                <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                                <path d="M10 9H8" />
                                <path d="M16 13H8" />
                                <path d="M16 17H8" />
                            </svg>
                            <span>Log Akses File</span>
                        </a>
                    </li>
                </ul>

            <?php elseif ($currentRole === 'hrd'): ?>
                <ul class="space-y-1 text-base flex-1">
                    <li>
                        <a href="<?= site_url('hrd/dashboard') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-layout-dashboard-icon lucide-layout-dashboard">
                                <rect width="7" height="9" x="3" y="3" rx="1" />
                                <rect width="7" height="5" x="14" y="3" rx="1" />
                                <rect width="7" height="9" x="14" y="12" rx="1" />
                                <rect width="7" height="5" x="3" y="16" rx="1" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url('hrd/dokumen-staff') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-file-icon lucide-file">
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
                                <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                            </svg>
                            <span>Dokumen Staff</span>
                        </a>
                    </li>
                    <li>
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
                    </li>
                    <li>
                        <a href="<?= site_url('hrd/dokumen-umum') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-files-icon lucide-files">
                                <path d="M20 7h-3a2 2 0 0 1-2-2V2" />
                                <path d="M9 18a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h7l4 4v10a2 2 0 0 1-2 2Z" />
                                <path d="M3 7.6v12.8A1.6 1.6 0 0 0 4.6 22h9.8" />
                            </svg>
                            <span>Dokumen Umum</span>
                        </a>
                    </li>
                </ul>

            <?php elseif ($currentRole === 'staff'): ?>
                <ul class="space-y-1 text-base flex-1">
                    <li>
                        <a href="<?= site_url('staff/dashboard') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-layout-dashboard-icon lucide-layout-dashboard">
                                <rect width="7" height="9" x="3" y="3" rx="1" />
                                <rect width="7" height="5" x="14" y="3" rx="1" />
                                <rect width="7" height="9" x="14" y="12" rx="1" />
                                <rect width="7" height="5" x="3" y="16" rx="1" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url('staff/dokumen-staff') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-file-icon lucide-file">
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
                                <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                            </svg>
                            <span>Dokumen Staff</span>
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
                        <a href="<?= site_url('staff/dokumen-umum') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-files-icon lucide-files">
                                <path d="M20 7h-3a2 2 0 0 1-2-2V2" />
                                <path d="M9 18a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h7l4 4v10a2 2 0 0 1-2 2Z" />
                                <path d="M3 7.6v12.8A1.6 1.6 0 0 0 4.6 22h9.8" />
                            </svg>
                            <span>Dokumen Umum</span>
                        </a>
                    </li>
                </ul>

            <?php elseif ($currentRole === 'supervisor'): ?>
                <ul class="space-y-1 text-base flex-1">
                    <li>
                        <a href="<?= site_url('supervisor/dashboard') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-layout-dashboard-icon lucide-layout-dashboard">
                                <rect width="7" height="9" x="3" y="3" rx="1" />
                                <rect width="7" height="5" x="14" y="3" rx="1" />
                                <rect width="7" height="9" x="14" y="12" rx="1" />
                                <rect width="7" height="5" x="3" y="16" rx="1" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                    <li>
                        <a href="<?= site_url('supervisor/dokumen-staff') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-file-icon lucide-file">
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
                                <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                            </svg>
                            <span>Dokumen Staff</span> </a>
                    </li>
                    <a href="<?= site_url('supervisor/dokumen-supervisor') ?>"
                        class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-file-icon lucide-file">
                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
                            <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                        </svg>
                        <span>Dokumen Supervisor</span> </a>
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
                        <a href="<?= site_url('supervisor/dokumen-umum') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-files-icon lucide-files">
                                <path d="M20 7h-3a2 2 0 0 1-2-2V2" />
                                <path d="M9 18a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h7l4 4v10a2 2 0 0 1-2 2Z" />
                                <path d="M3 7.6v12.8A1.6 1.6 0 0 0 4.6 22h9.8" />
                            </svg>
                            <span>Dokumen Umum</span>
                        </a>
                    </li>
                </ul>

            <?php elseif ($currentRole === 'manajer'): ?>
                <ul class="space-y-1 text-base flex-1">
                    <li>
                        <a href="<?= site_url('manager/dashboard') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-layout-dashboard-icon lucide-layout-dashboard">
                                <rect width="7" height="9" x="3" y="3" rx="1" />
                                <rect width="7" height="5" x="14" y="3" rx="1" />
                                <rect width="7" height="9" x="14" y="12" rx="1" />
                                <rect width="7" height="5" x="3" y="16" rx="1" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url('manager/dokumen-staff') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-file-icon lucide-file">
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
                                <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                            </svg>
                            <span>Dokumen Staff</span> </a>
                    </li>
                    <li>
                        <a href="<?= site_url('manager/dokumen-supervisor') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-file-icon lucide-file">
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
                                <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                            </svg>
                            <span>Dokumen Supervisor</span> </a>
                    </li>
                    <li>
                        <a href="<?= site_url('manager/dokumen-manager') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-file-icon lucide-file">
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
                                <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                            </svg>
                            <span>Dokumen Manager</span> </a>
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
                        <a href="<?= site_url('manager/dokumen-umum') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-files-icon lucide-files">
                                <path d="M20 7h-3a2 2 0 0 1-2-2V2" />
                                <path d="M9 18a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h7l4 4v10a2 2 0 0 1-2 2Z" />
                                <path d="M3 7.6v12.8A1.6 1.6 0 0 0 4.6 22h9.8" />
                            </svg>
                            <span>Dokumen Umum</span>
                        </a>
                    </li>
                </ul>

            <?php elseif ($currentRole === 'direksi'): ?>
                <ul class="space-y-1 text-base flex-1">
                    <li>
                        <a href="<?= site_url('direksi/dashboard') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-layout-dashboard-icon lucide-layout-dashboard">
                                <rect width="7" height="9" x="3" y="3" rx="1" />
                                <rect width="7" height="5" x="14" y="3" rx="1" />
                                <rect width="7" height="9" x="14" y="12" rx="1" />
                                <rect width="7" height="5" x="3" y="16" rx="1" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url('direksi/dokumen-staff') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-file-icon lucide-file">
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
                                <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                            </svg>
                            <span>Dokumen Staff</span> </a>
                    </li>
                    <li>
                        <a href="<?= site_url('direksi/dokumen-supervisor') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-file-icon lucide-file">
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
                                <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                            </svg>
                            <span>Dokumen Supervisor</span> </a>
                    </li>
                    <li>
                        <a href="<?= site_url('direksi/dokumen-manager') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-file-icon lucide-file">
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
                                <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                            </svg>
                            <span>Dokumen Manager</span> </a>
                    </li>
                    <li>
                        <a href="<?= site_url('direksi/dokumen-direksi') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-file-icon lucide-file">
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
                                <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                            </svg>
                            <span>Dokumen Direksi</span> </a>
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
                        <a href="<?= site_url('direksi/dokumen-umum') ?>"
                            class="flex items-center gap-4 text-white hover:bg-[#2B364F] hover:text-white rounded-md px-3 py-3 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-files-icon lucide-files">
                                <path d="M20 7h-3a2 2 0 0 1-2-2V2" />
                                <path d="M9 18a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h7l4 4v10a2 2 0 0 1-2 2Z" />
                                <path d="M3 7.6v12.8A1.6 1.6 0 0 0 4.6 22h9.8" />
                            </svg>
                            <span>Dokumen Umum</span>
                        </a>
                    </li>
                </ul>
            <?php endif; ?>

            <a href="<?= site_url('logout') ?>"
                class="bg-red-600 hover:bg-red-700 text-white text-center font-medium py-2 rounded-md mt-4">
                Logout
            </a>
        </aside>

        <div class="flex flex-col flex-1 md:ml-64 pt-16 md:pt-0">
            <main class="flex-1 overflow-y-auto p-6">
                <?= $this->renderSection('content') ?>
            </main>
            <footer class="bg-[#1E293B] p-4 text-center text-sm text-white">
                &copy; <?= date('Y') ?> - Dashboard Footer
            </footer>
        </div>
    </div>

    <div id="floatingMenu"
        class="fixed w-48 bg-white rounded-xl shadow-lg hidden transition ease-out duration-200 transform scale-95 opacity-0 z-[9998]">
        <ul class="text-sm text-gray-700 divide-y divide-gray-100">
            <li id="renameOption" onclick="showRenameModal()"
                class="flex items-center px-4 py-3 hover:bg-gray-100 cursor-pointer">
                <svg class="w-5 h-5 mr-3 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M17.414 2.586a2 2 0 00-2.828 0l-1.793 1.793 2.828 2.828 1.793-1.793a2 2 0 000-2.828zM2 13.586V17h3.414l9.793-9.793-2.828-2.828L2 13.586z" />
                </svg>
                Ganti nama
            </li>
            <li id="infoDetailOption" onclick="showInfoDetailModal()"
                class="flex items-center px-4 py-3 hover:bg-gray-100 cursor-pointer">
                <svg class="w-5 h-5 mr-3 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a7 7 0 100 14A7 7 0 009 2zM8 7h2v5H8V7zm1 8a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
                Informasi detail
            </li>
            <li id="deleteOption" onclick="showDeleteConfirmModal()"
                class="flex items-center px-4 py-3 hover:bg-gray-100 cursor-pointer text-red-600">
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
            <h2 class="text-xl font-semibold mb-4">Detail Informasi File/Folder</h2>
            <div class="text-sm text-gray-800">
                <p class="mb-2"><strong>Nama:</strong> <span id="detailName"></span></p>
                <p class="mb-2"><strong>Jenis:</strong> <span id="detailJenis"></span></p>
                <p class="mb-2"><strong>Ukuran:</strong> <span id="detailUkuran"></span></p>
                <p class="mb-2"><strong>Pemilik:</strong> <span id="detailPemilik"></span></p>
                <p class="mb-2"><strong>Dibuat:</strong> <span id="detailDibuat"></span></p>
                <p class="mb-2"><strong>Terakhir Diubah:</strong> <span id="detailUpdated"></span></p>
            </div>
            <div class="flex justify-end space-x-4 mt-4">
                <button onclick="closeInfoDetailModal()" class="text-blue-500">Tutup</button>
            </div>
        </div>
    </div>

    <div id="modalRename"
        class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/20 backdrop-blur-sm hidden">
        <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
            <h2 class="text-xl font-semibold mb-4">Ganti Nama Folder</h2>
            <form id="renameForm" onsubmit="event.preventDefault(); submitRename();">
                <label class="block text-sm font-medium mb-1">Nama Baru</label>
                <input type="text" id="newFileName"
                    class="w-full border rounded-lg px-3 py-2 mb-4 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Masukkan nama baru" required>
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeRenameModal()"
                        class="text-blue-500 hover:text-blue-700">Batal</button>
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus Folder -->
    <div id="modalDeleteConfirm" class="fixed inset-0 bg-opacity-50 flex items-center justify-center z-[10000] hidden">
        <div
            class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0">
            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                    </path>
                </svg>
            </div>

            <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Hapus Folder</h3>

            <p class="text-gray-600 text-center mb-6">
                Apakah Anda yakin ingin menghapus folder "<span id="deleteConfirmFolderName"
                    class="font-semibold text-gray-900"></span>"?
            </p>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-6">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-yellow-400 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <p class="text-sm text-yellow-800 font-medium">Peringatan!</p>
                        <p class="text-sm text-yellow-700">Aksi ini tidak dapat dibatalkan. Folder akan dihapus secara
                            permanen.</p>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button onclick="closeDeleteConfirmModal()"
                    class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                    Batal
                </button>
                <button onclick="confirmDeleteFolder()"
                    class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
                    Hapus
                </button>
            </div>
        </div>
    </div>

    <script src="<?= base_url('js/titiktiga.js') ?>"></script>

    <script>
        function downloadFolder() {
            if (window.selectedFolderId) {
                window.location.href = window.baseUrl + 'folder/download/' + window.selectedFolderId;
            } else {
                alert('Folder tidak diketahui.');
            }
        }
    </script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>