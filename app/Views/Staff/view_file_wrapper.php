<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($fileName) ?></title>
    <link href="<?= base_url('css/output.css') ?>" rel="stylesheet">
</head>
<body class="bg-gray-200">
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
            <button id="closeButton" title="Tutup" aria-label="Tutup" class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </header>

    <main>
        <iframe src="<?= site_url('staff/serve-file/' . $fileId) ?>" class="w-full h-screen pt-16" frameborder="0" title="Tampilan File: <?= esc($fileName) ?>"></iframe>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const backButton = document.getElementById('backButton');
            const closeButton = document.getElementById('closeButton');

            if (backButton) {
                backButton.addEventListener('click', function() {
                    history.back();
                });
            }

            if (closeButton) {
                closeButton.addEventListener('click', function() {
                    // Ini akan mencoba menutup tab. Mungkin tidak berhasil jika tab tidak dibuka oleh skrip.
                    window.close();
                });
            }
        });
    </script>
</body>
</html> 