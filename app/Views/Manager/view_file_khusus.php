<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi File: <?= esc($fileName) ?></title>
    <link href="<?= base_url('css/output.css') ?>" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
    <header class="fixed top-0 left-0 right-0 bg-white shadow-md z-10 h-16 flex items-center justify-between px-6">
        <div class="flex items-center space-x-4 min-w-0">
            <button id="backButton" title="Kembali" aria-label="Kembali" class="text-gray-600 hover:text-gray-900 flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </button>
            <h1 class="text-lg font-semibold text-gray-800 truncate" title="<?= esc($fileName) ?>">
                <?= esc($fileName) ?>
            </h1>
        </div>
        <div class="flex items-center space-x-4">
            <a href="<?= site_url('manager/download-file/' . $fileId) ?>" 
               id="downloadButtonHeader" 
               title="Unduh File" 
               aria-label="Unduh File" 
               class="text-gray-600 hover:text-gray-900"
               download>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            </a>
            <button id="closeButton" title="Tutup" aria-label="Tutup" class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </header>

    <main class="flex-grow flex flex-col items-center justify-center pt-20 pb-10 px-4">
        <div class="bg-white p-8 rounded-lg shadow-xl max-w-2xl w-full text-center">
            <i class="fas fa-exclamation-triangle text-yellow-500 text-5xl mb-6"></i>
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Pratinjau Tidak Tersedia</h2>
            
            <p class="text-lg text-gray-700 mb-6">
                Browser tidak dapat menampilkan file jenis *<?= esc(strtoupper(pathinfo($fileName, PATHINFO_EXTENSION))) ?>* secara langsung. Untuk melihat kontennya, Anda perlu mengunduhnya.
            </p>

            <div class="bg-gray-50 p-6 rounded-md border border-gray-200 mb-8 text-left">
                <h3 class="text-xl font-semibold text-gray-800 mb-3">Detail File</h3>
                <p class="text-gray-700 mb-2"><strong>Nama File:</strong> <?= esc($fileName) ?></p>
                <p class="text-gray-700 mb-2"><strong>Ukuran:</strong> <?= number_format($file['file_size'] / 1024, 2) ?> KB</p>
                <p class="text-gray-700 mb-2"><strong>Tanggal Unggah:</strong> <?= date('d M Y, H:i', strtotime($file['created_at'])) ?></p>
                <?php if (isset($file['uploaded_by_username'])): // Asumsi Anda punya kolom ini atau ambil dari relasi ?>
                    <p class="text-gray-700 mb-2"><strong>Diunggah Oleh:</strong> <?= esc($file['uploaded_by_username']) ?></p>
                <?php endif; ?>
                <?php if (isset($file['description'])): // Asumsi ada kolom deskripsi ?>
                    <p class="text-gray-700"><strong>Deskripsi:</strong> <?= esc($file['description']) ?></p>
                <?php endif; ?>
            </div>

            <p class="text-md text-gray-600 mb-8">
                *Untuk mengakses isi file, silakan klik tombol "Unduh File" di bawah ini.* Setelah diunduh, Anda dapat membukanya dengan aplikasi yang sesuai (seperti Microsoft Word, Excel, atau PowerPoint) di perangkat Anda.
            </p>

            <a href="<?= site_url('manager/download-file/' . $fileId) ?>" 
               class="inline-flex items-center px-8 py-4 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition-colors duration-300 transform hover:scale-105">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Unduh File (<?= esc(strtoupper(pathinfo($fileName, PATHINFO_EXTENSION))) ?>)
            </a>
            <p class="text-sm text-gray-500 mt-4">File ini akan diunduh ke perangkat Anda.</p>
        </div>
    </main>

    <footer class="p-4 text-center text-gray-500 text-sm">
        &copy; <?= date('Y') ?> Nama Aplikasi Anda.
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const backButton = document.getElementById('backButton');
            const closeButton = document.getElementById('closeButton');

            if (backButton) {
                backButton.addEventListener('click', function() {
                    history.back(); // Kembali ke halaman sebelumnya
                });
            }

            if (closeButton) {
                closeButton.addEventListener('click', function() {
                    window.close(); // Coba tutup tab/jendela. Mungkin tidak berfungsi di semua browser tergantung pengaturan.
                    // Alternatif: history.back() jika tidak bisa menutup
                });
            }
        });
    </script>
</body>
</html>