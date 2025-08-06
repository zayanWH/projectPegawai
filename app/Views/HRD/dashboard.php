<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!-- Header -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-semibold text-gray-800">Dashboard HRD</h1>
            <span class="text-sm text-gray-500">Selamat datang, <?= esc($userName) ?></span>
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

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Total Folders -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Folder</p>
                <p class="text-2xl font-semibold text-gray-900"><?= number_format($totalFolders) ?></p>
            </div>
        </div>
    </div>

    <!-- User Folders -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Folder Saya</p>
                <p class="text-2xl font-semibold text-gray-900"><?= number_format($userFolders) ?></p>
            </div>
        </div>
    </div>

    <!-- Total Documents -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
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
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total User</p>
                <p class="text-2xl font-semibold text-gray-900"><?= number_format($totalUser) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Area -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Recent Documents -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Dokumen Terbaru</h2>
                <a href="<?= base_url('hrd/dokumen-umum') ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="mt-2 text-sm text-gray-500">Belum ada dokumen</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Quick Actions & System Status -->
    <div class="space-y-6">
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Aksi Cepat</h2>
            <div class="space-y-3">
                <a href="<?= base_url('hrd/dokumen-umum') ?>" class="flex items-center p-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 border border-gray-200">
                    <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                    Kelola Dokumen
                </a>
                
                <a href="<?= base_url('hrd/notifications/dashboard') ?>" class="flex items-center p-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 border border-gray-200">
                    <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5V3h0z"></path>
                    </svg>
                    Test Notifikasi
                </a>
                
                <button onclick="testNotificationSystem()" class="w-full flex items-center p-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 border border-gray-200">
                    <svg class="w-5 h-5 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Test WebSocket
                </button>
            </div>
        </div>
        
        <!-- System Status -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Status Sistem</h2>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">WebSocket</span>
                    <span id="websocket-status" class="text-sm text-red-500">🔴 Terputus</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Database</span>
                    <span class="text-sm text-green-500">🟢 Aktif</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Email Service</span>
                    <span class="text-sm text-yellow-500">🟡 Siap</span>
                </div>
            </div>
        </div>
        
        <!-- Notification System Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Sistem Notifikasi Realtime</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>Sistem notifikasi WebSocket dan email Gmail telah diaktifkan. Upload file akan otomatis mengirim notifikasi ke semua user.</p>
                    </div>
                </div>
            </div>
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
document.addEventListener('websocket-connected', function() {
    document.getElementById('websocket-status').innerHTML = '🟢 Terhubung';
    document.getElementById('websocket-status').className = 'text-sm text-green-500';
});

document.addEventListener('websocket-disconnected', function() {
    document.getElementById('websocket-status').innerHTML = '🔴 Terputus';
    document.getElementById('websocket-status').className = 'text-sm text-red-500';
});

// Check status periodically
setInterval(function() {
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
