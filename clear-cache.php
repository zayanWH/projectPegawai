<?php
/**
 * Script untuk clear cache PHP Opcache dan restart services
 * Jalankan via browser: http://localhost/projectPegawai-master/clear-cache.php
 */

echo "<h2>🧹 Clearing PHP Cache & Opcache</h2>";

// Clear Opcache
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "✅ Opcache berhasil di-reset<br>";
    } else {
        echo "❌ Gagal reset Opcache<br>";
    }
} else {
    echo "ℹ️ Opcache tidak aktif<br>";
}

// Clear session files
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_destroy();
echo "✅ Session cleared<br>";

// Clear any file cache
$cacheDir = __DIR__ . '/writable/cache';
if (is_dir($cacheDir)) {
    $files = glob($cacheDir . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    echo "✅ File cache cleared<br>";
}

// Force reload autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✅ Autoloader akan di-reload<br>";
}

echo "<br><h3>🔄 Langkah Selanjutnya:</h3>";
echo "1. Restart Laragon Apache<br>";
echo "2. Refresh halaman notification dashboard<br>";
echo "3. Test kembali semua fitur<br>";
echo "<br><a href='/projectPegawai-master/public/hrd/notifications/dashboard' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔗 Go to Notification Dashboard</a>";
?>
