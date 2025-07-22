// createFolder.js

document.addEventListener('DOMContentLoaded', function() {
    const openCreateFolderBtn = document.getElementById('openCreateFolder');
    const modalCreateFolder = document.getElementById('modalCreateFolder');
    const cancelModalBtn = document.getElementById('cancelModal');
    const createFolderBtn = document.getElementById('createFolderBtn');
    const folderNameInput = document.getElementById('folderName');

    // Fungsi untuk membuka modal
    if (openCreateFolderBtn) {
        openCreateFolderBtn.addEventListener('click', function(event) {
            event.preventDefault();
            modalCreateFolder.classList.remove('hidden');
            // Opsional: Sembunyikan dropdown "Baru" jika sedang terbuka
            const dropdownMenu = document.getElementById('dropdownMenu');
            if (dropdownMenu && !dropdownMenu.classList.contains('hidden')) {
                dropdownMenu.classList.add('hidden');
            }
        });
    }

    // Fungsi untuk menutup modal
    if (cancelModalBtn) {
        cancelModalBtn.addEventListener('click', function() {
            modalCreateFolder.classList.add('hidden');
            folderNameInput.value = ''; // Bersihkan input saat ditutup
        });
    }

    // Fungsi untuk menutup modal saat mengklik di luar konten modal
    if (modalCreateFolder) {
        modalCreateFolder.addEventListener('click', function(event) {
            if (event.target === modalCreateFolder) {
                modalCreateFolder.classList.add('hidden');
                folderNameInput.value = ''; // Bersihkan input saat ditutup
            }
        });
    }

    // Fungsi untuk membuat folder (Contoh menggunakan Fetch API)
    if (createFolderBtn) {
        createFolderBtn.addEventListener('click', function() {
            const folderName = folderNameInput.value.trim();

            if (!folderName) {
                alert('Nama sub-folder tidak boleh kosong.');
                return;
            }

            // Gunakan window.baseUrl dan window.currentFolderId dari variabel global
            const parentFolderId = window.currentFolderId || null; // Jika ini root, bisa null atau ID root default

            fetch(`${window.baseUrl}api/create-folder`, { // Sesuaikan endpoint API Anda
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest' // Penting untuk CodeIgniter/framework lain
                },
                body: JSON.stringify({
                    parent_id: parentFolderId,
                    folder_name: folderName,
                    user_id: window.currentUserId // Kirim user_id jika diperlukan untuk otentikasi/otorisasi
                })
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
                    alert('Sub-folder berhasil dibuat!');
                    modalCreateFolder.classList.add('hidden');
                    folderNameInput.value = '';
                    location.reload(); // Muat ulang halaman untuk melihat folder baru
                } else {
                    alert('Gagal membuat sub-folder: ' + (data.message || 'Terjadi kesalahan.'));
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
                console.error('Error saat membuat folder:', error);
                alert('Terjadi kesalahan saat berkomunikasi dengan server. Silakan cek konsol.');
            });
        });
    }
});