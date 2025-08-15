<?= $this->extend('layout/main') ?>

<?= $this->section('pageTitle') ?>
Dashboard
<?= $this->endSection() ?>

<?= $this->section('pageLogo') ?>
<img src="<?= base_url('images/logo.jpg') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Header -->
<div class="hidden md:block">
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-semibold text-gray-800">Dashboard HRD</h1>
            </div>

            <!-- Notification Badge -->
            <?php if ($unreadNotifications > 0): ?>
                <div class="flex items-center space-x-2">
                    <div class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">
                        🔔 <?= $unreadNotifications ?> notifikasi baru
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Total Folders -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <img src="<?= base_url('images/folder.png') ?>" alt="Folder Icon" class="w-8 h-7 mr-2">
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Total Folder</p>
                <p class="text-2xl font-semibold text-gray-800"><?= esc($totalFolders) ?></p>
            </div>
        </div>
    </div>

    <!-- User Folders -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <img src="<?= base_url('images/myfolder.png') ?>" alt="Folder Icon" class="w-8 h-7 mr-2">
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Folder Saya</p>
                <p class="text-2xl font-semibold text-gray-900"><?= number_format($userFolders) ?></p>
            </div>
        </div>
    </div>

    <!-- Total Documents -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <img src="<?= base_url('images/file-default.png') ?>" alt="Folder Icon" class="w-8 h-7 mr-2">
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Dokumen</p>
                <p class="text-2xl font-semibold text-gray-900"><?= number_format($totalDocuments) ?></p>
            </div>
        </div>
    </div>

    <!-- Total Users -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <img src="<?= base_url('images/totalusers.png') ?>" alt="Folder Icon" class="w-8 h-7 mr-2">
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total User</p>
                <p class="text-2xl font-semibold text-gray-900"><?= number_format($totalUser) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Area -->
<div class="overflow-x-auto">
    <!-- Recent Documents -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Dokumen Terbaru</h2>
                <a href="<?= base_url('hrd/dokumen-umum') ?>"
                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    Lihat Semua →
                </a>
            </div>

            <?php if (!empty($recentDocuments)): ?>
                <div class="space-y-3">
                    <?php foreach ($recentDocuments as $doc): ?>
                        <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-900"><?= esc($doc['name']) ?></p>
                                <p class="text-xs text-gray-500">
                                    <?= date('d M Y, H:i', strtotime($doc['created_at'])) ?>
                                    <?php if (isset($doc['category']) && $doc['category']): ?>
                                        • <?= esc($doc['category']) ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="text-xs text-gray-400">
                                    <?= number_format($doc['size'] / 1024, 1) ?> KB
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Belum ada dokumen</p>
                </div>
            <?php endif; ?>
        </div>
    </div>


</div>

<script>
    // Test notification system
    function testNotificationSystem() {
        if (window.SimpleNotificationManager && window.SimpleNotificationManager.client) {
            // Send test ping
            const success = window.SimpleNotificationManager.ping();
            if (success) {
                alert('✅ Test ping berhasil dikirim ke WebSocket server!');
            } else {
                alert('❌ WebSocket belum terhubung. Pastikan server berjalan di port 8080.');
            }
        } else {
            alert('❌ WebSocket client belum tersedia. Periksa simple-notification-client.js');
        }
    }

    // Update WebSocket status
    document.addEventListener('websocket-connected', function () {
        document.getElementById('websocket-status').innerHTML = '🟢 Terhubung';
        document.getElementById('websocket-status').className = 'text-sm text-green-500';
    });

    document.addEventListener('websocket-disconnected', function () {
        document.getElementById('websocket-status').innerHTML = '🔴 Terputus';
        document.getElementById('websocket-status').className = 'text-sm text-red-500';
    });

    // Check status periodically
    setInterval(function () {
        if (window.SimpleNotificationManager) {
            const status = window.SimpleNotificationManager.getStatus();
            const statusEl = document.getElementById('websocket-status');
            if (statusEl) {
                if (status.connected) {
                    statusEl.innerHTML = '🟢 Terhubung';
                    statusEl.className = 'text-sm text-green-500';
                } else {
                    statusEl.innerHTML = '🔴 Terputus';
                    statusEl.className = 'text-sm text-red-500';
                }
            }
        }
    }, 3000);
</script>

<?= $this->endSection() ?>