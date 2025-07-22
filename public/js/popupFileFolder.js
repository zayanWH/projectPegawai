// popupFileFolder.js

document.addEventListener('DOMContentLoaded', function() {
    const dropdownButton = document.getElementById('dropdownButton');
    const dropdownMenu = document.getElementById('dropdownMenu');
    const uploadFileLink = document.getElementById('uploadFileLink');
    const fileInput = document.getElementById('fileInput');

    // Toggle dropdown menu
    if (dropdownButton) {
        dropdownButton.addEventListener('click', function(event) {
            event.stopPropagation(); // Mencegah klik di body menutup dropdown segera
            dropdownMenu.classList.toggle('hidden');
        });
    }

    // Handle 'Upload File' click
    if (uploadFileLink) {
        uploadFileLink.addEventListener('click', function(event) {
            event.preventDefault(); // Mencegah navigasi link
            fileInput.click(); // Memicu klik pada input file tersembunyi
            dropdownMenu.classList.add('hidden'); // Sembunyikan dropdown setelah klik
        });
    }

    // Handle file selection (upload logic - ini contoh, Anda perlu endpoint backend)
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                const formData = new FormData();
                formData.append('file', file);
                formData.append('parent_id', window.currentFolderId || null);
                formData.append('user_id', window.currentUserId);

                // Contoh: Kirim file ke server
                fetch(`${window.baseUrl}api/upload-file`, { // Sesuaikan endpoint API Anda
                    method: 'POST',
                    body: formData,
                    // Jangan set Content-Type header secara manual untuk FormData, browser akan melakukannya
                })
                .then(response => {
                    if (!response.ok) {
                         // Coba baca respons JSON untuk pesan error
                         return response.json().then(err => { throw err; }).catch(() => {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        alert('File berhasil diunggah!');
                        location.reload(); // Muat ulang halaman untuk melihat file baru
                    } else {
                        alert('Gagal mengunggah file: ' + (data.message || 'Terjadi kesalahan.'));
                        if (data.errors) {
                            let errorMessages = '';
                            for (const key in data.errors) {
                                errorMessages += `${data.errors[key]}\n`;
                            }
                            alert('Validasi Gagal:\n' + errorMessages);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error saat mengunggah file:', error);
                    alert('Terjadi kesalahan saat berkomunikasi dengan server. Silakan cek konsol.');
                });
            }
        });
    }

    // Close dropdown menu when clicking outside
    window.addEventListener('click', function(event) {
        if (dropdownMenu && !dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
            dropdownMenu.classList.add('hidden');
        }
    });
});