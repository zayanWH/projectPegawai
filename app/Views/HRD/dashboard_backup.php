<?= $this->extend('layout/hrd') ?>

<?= $this->section('content') ?>

<!-- Header -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-semibold text-gray-800">Dashboard HRD</h1>
            <span class="text-sm text-gray-500">Selamat datang, <?= esc($userName) ?></span>
        </div>
        <div class="flex items-center space-x-4">
            <!-- Notification Status -->
            <div class="flex items-center space-x-2">
                <span id="notification-status" class="text-sm text-gray-500">🔴 Terputus</span>
                <?php if ($unreadNotifications > 0): ?>
                <span id="notification-badge" class="bg-red-500 text-white text-xs rounded-full px-2 py-1">
                    <?= $unreadNotifications ?>
                </span>
                <?php endif; ?>
            </div>
            <img src="<?= base_url('images/logo.png') ?>" alt="Logo" class="h-10 w-auto rounded-lg">
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Total Documents -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Dokumen</p>
                <p class="text-2xl font-semibold text-gray-900"><?= number_format($totalDocuments) ?></p>
            </div>
        </div>
    </div>

    <!-- Total Folders -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Folder</p>
                <p class="text-2xl font-semibold text-gray-900"><?= number_format($totalFolders) ?></p>
            </div>
        </div>
    </div>

    <!-- User Folders -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Folder Saya</p>
                <p class="text-2xl font-semibold text-gray-900"><?= number_format($userFolders) ?></p>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5V3h0z"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Notifikasi Baru</p>
                <p class="text-2xl font-semibold text-gray-900"><?= number_format($unreadNotifications) ?></p>
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
                            <?php if ($doc['category']): ?>
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
    if (window.NotificationManager && window.NotificationManager.websocket) {
        // Send test ping
        window.NotificationManager.websocket.ping();
        alert('Test ping dikirim ke WebSocket server!');
    } else {
        alert('WebSocket belum terhubung. Pastikan server WebSocket berjalan.');
    }
}

// Update WebSocket status
if (window.NotificationManager) {
    const originalOnConnected = window.NotificationManager.websocket?.onConnected;
    const originalOnDisconnected = window.NotificationManager.websocket?.onDisconnected;
    
    if (window.NotificationManager.websocket) {
        window.NotificationManager.websocket.onConnected = function(event) {
            document.getElementById('websocket-status').innerHTML = '🟢 Terhubung';
            document.getElementById('websocket-status').className = 'text-sm text-green-500';
            if (originalOnConnected) originalOnConnected(event);
        };
        
        window.NotificationManager.websocket.onDisconnected = function(event) {
            document.getElementById('websocket-status').innerHTML = '🔴 Terputus';
            document.getElementById('websocket-status').className = 'text-sm text-red-500';
            if (originalOnDisconnected) originalOnDisconnected(event);
        };
    }
}
</script>

<?= $this->endSection() ?>
  <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
    <h2 class="text-xl font-semibold mb-4">Folder Baru</h2>

    <label class="block text-sm font-medium mb-1">Jenis Folder</label>
    <div class="relative mb-4">
      <select id="folderType" class="w-full border rounded-lg px-3 py-2 pr-10 appearance-none">
        <option disabled selected>Pilih jenis folder</option>
        <option value="personal">Personal Folder</option>
        <option value="shared">Shared Folder</option>
      </select>
      <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-2 mb-4">
      <label><input type="checkbox" value="Staff" class="mr-2"> Staff</label>
      <label><input type="checkbox" value="Manager" class="mr-2"> Manager</label>
      <label><input type="checkbox" value="Supervisor" class="mr-2"> Supervisor</label>
      <label><input type="checkbox" value="Direksi" class="mr-2"> Direksi</label>
    </div>

    <label class="block text-sm font-medium mb-1">Akses</label>
    <div class="relative mb-4">
      <select id="folderAccess" class="w-full border rounded-lg px-3 py-2 pr-10 appearance-none">
        <option disabled selected>Pilih akses</option>
        <option value="full">Full Access</option>
        <option value="read">Read Only</option>
      </select>
      <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </div>

    <label class="block text-sm font-medium">Nama Folder</label>
    <input type="text" id="folderName" placeholder="Masukan nama folder" class="w-full border rounded-lg px-3 py-2 mb-4">

    <div class="flex justify-end space-x-4">
      <button id="cancelModal" class="text-blue-500">Batal</button>
      <button id="createFolderBtn" class="text-blue-600 font-semibold">Buat</button>
    </div>
  </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                </svg>
            </div>
            <div class="ml-4">
    <p class="text-sm text-gray-600">Total Folder</p>
    <p class="text-2xl font-semibold text-gray-800"><?= esc($totalFolders) ?></p>
</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 3h8l5 5v13a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M14 3v5h5" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Total File</p>
                <p class="text-2xl font-semibold text-gray-800"><?= esc($totalFiles) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 3h8l5 5v13a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 3v5h5" />
                </svg>
            </div>
            <div class="ml-4">
    <p class="text-sm text-gray-600">File HRD</p>
    <p class="text-2xl font-semibold text-gray-800"><?= esc($totalHrdFiles) ?></p>
</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500"> <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5.121 17.804A7 7 0 0112 15a7 7 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Total User</p>
    <p class="text-2xl font-semibold text-gray-800"><?= esc($totalUser) ?></p>
            </div>
        </div>
    </div>
</div>

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
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama Dokumen</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Kategori/Folder Induk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Pengunggah/Pemilik</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tanggal Diunggah</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tipe</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <?php if (empty($latestDocuments)): ?>
                <tr>
                  <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Tidak ada dokumen terbaru.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($latestDocuments as $doc): ?>
                  <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <svg class="<?= esc($doc['icon_class']) ?>" fill="currentColor" viewBox="0 0 20 20">
                          <?= $doc['icon_path'] ?>
                        </svg>
                        <span class="text-sm text-gray-900"><?= esc($doc['display_name']) ?></span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <?php if ($doc['type'] === 'folder'): // Tampilkan ikon folder hanya untuk folder induk ?>
                            <svg class="w-5 h-5 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 6a2 2 0 012-2h5l2 2h7a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                            </svg>
                        <?php endif; ?>
                        <span class="text-sm text-gray-900"><?= esc($doc['parent_folder_name']) ?></span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="w-5 h-5 bg-blue-400 rounded-full mr-2 flex items-center justify-center text-white text-xs">
                          <?= strtoupper(substr(esc($doc['uploader_name']), 0, 1)) ?>
                        </div>
                        <span class="text-sm text-gray-900"><?= esc($doc['uploader_name']) ?></span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      <?= esc(date('d M Y', strtotime($doc['created_at']))) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 capitalize">
                      <?= esc($doc['type']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"> 
                        <button onclick="toggleMenu(this)" class="text-blue-600 hover:text-blue-900">⋮</button> 
                        <?php if ($doc['type'] === 'file'): ?>
                            <a href="<?= base_url('hrd/dokumen/download-file/' . $doc['id']) ?>" class="text-green-600 hover:text-green-900 ml-2" title="Download">⬇️</a>
                            <a href="<?= base_url('hrd/dokumen/view-file/' . $doc['id']) ?>" target="_blank" class="text-purple-600 hover:text-purple-900 ml-2" title="View">👁️</a>
                        <?php elseif ($doc['type'] === 'folder'): ?>
                            <a href="<?= base_url('hrd/dokumen-staff/' . $doc['id']) ?>" class="text-indigo-600 hover:text-indigo-900 ml-2" title="Buka Folder">📁</a>
                        <?php elseif ($doc['type'] === 'hrd_doc'): ?>
                            <?php 
                                // Jika hrd_documents punya file_id dan file_id bisa di-resolve ke tabel files
                                if (isset($doc['file_id']) && $doc['file_id']):
                            ?>
                                <a href="<?= base_url('hrd/dokumen/download-file/' . $doc['file_id']) ?>" class="text-green-600 hover:text-green-900 ml-2" title="Download">⬇️</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>