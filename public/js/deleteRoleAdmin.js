// public/js/manajemen_jabatan.js

document.addEventListener('DOMContentLoaded', function() {
    let jabatanIdToDelete = null;
    let jabatanNameToDelete = null;

    // --- FUNGSI MODAL PESAN KUSTOM (Salin dari skrip user Anda) ---
    function showMessageModal(title, message) {
        const messageModal = document.getElementById('messageModal');
        const messageModalTitle = document.getElementById('messageModalTitle');
        const messageModalContent = document.getElementById('messageModalContent');

        if (messageModal && messageModalTitle && messageModalContent) {
            messageModalTitle.textContent = title;
            messageModalContent.textContent = message;
            messageModal.classList.remove('hidden');
        } else {
            console.error('ERROR: Elemen modal pesan tidak ditemukan. Menggunakan alert sebagai fallback.');
            alert(`${title}: ${message}`);
        }
    }

    const closeMessageModalBtn = document.getElementById('closeMessageModal');
    if (closeMessageModalBtn) {
        closeMessageModalBtn.addEventListener('click', function() {
            document.getElementById('messageModal').classList.add('hidden');
        });
    }

    const messageModalOverlay = document.getElementById('messageModal');
    if (messageModalOverlay) {
        messageModalOverlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    }

    // --- FUNGSI UNTUK MENGUPDATE TABEL (Disesuaikan untuk Jabatan) ---
    function removeJabatanRowFromTable(jabatanId) {
        // Asumsi baris tabel jabatan memiliki atribut data-jabatan-id
        const row = document.querySelector(`tr[data-jabatan-id="${jabatanId}"]`);
        if (row) {
            row.style.transition = 'opacity 0.3s ease-out';
            row.style.opacity = '0';
            setTimeout(() => {
                row.remove();
                updateJabatanRowNumbers(); // Panggil fungsi untuk update nomor baris
            }, 300);
        }
    }

    function updateJabatanRowNumbers() {
        const rows = document.querySelectorAll('#manajemen-jabatan-table tbody tr'); // Sesuaikan selector jika perlu
        rows.forEach((row, index) => {
            const numberCell = row.querySelector('td:first-child');
            // Memastikan sel pertama adalah nomor dan elemennya adalah TD
            if (numberCell && !isNaN(numberCell.textContent) && numberCell.tagName === 'TD') {
                numberCell.textContent = index + 1;
            }
        });
    }

    // --- EVENT LISTENER UNTUK TOMBOL DELETE JABATAN ---
    // Selector .open-delete-jabatan-modal harus ada di tombol delete di HTML Anda
    document.addEventListener('click', function(e) {
        const deleteButton = e.target.closest('.open-delete-jabatan-modal');
        if (deleteButton) {
            e.preventDefault();

            // Ambil data dari button yang diklik
            jabatanIdToDelete = deleteButton.getAttribute('data-jabatan-id');
            jabatanNameToDelete = deleteButton.getAttribute('data-jabatan-name') || 'Jabatan ini';

            console.log('DEBUG: Jabatan ID to delete:', jabatanIdToDelete);
            console.log('DEBUG: Jabatan Name to delete:', jabatanNameToDelete);

            // Update pesan konfirmasi di modal
            const modalMessageP = document.querySelector('#modalDeleteJabatan p');
            const jabatanNameSpan = document.getElementById('jabatanNameToDelete');
            
            if (modalMessageP && jabatanNameSpan) {
                jabatanNameSpan.textContent = jabatanNameToDelete;
                modalMessageP.innerHTML = `Yakin ingin menghapus jabatan <strong>${jabatanNameToDelete}</strong> ini? Tindakan ini tidak dapat dibatalkan.`;
            } else {
                console.warn('WARNING: Elemen pesan modal delete jabatan tidak ditemukan.');
            }

            // Tampilkan modal delete jabatan
            document.getElementById('modalDeleteJabatan').classList.remove('hidden');
        }
    });

    // --- EVENT LISTENER UNTUK KONFIRMASI DELETE JABATAN ---
    const confirmDeleteJabatanBtn = document.getElementById('confirmDeleteJabatanBtn');
    if (confirmDeleteJabatanBtn) {
        confirmDeleteJabatanBtn.addEventListener('click', function() {
            console.log('DEBUG: Tombol konfirmasi delete jabatan diklik!');

            if (!jabatanIdToDelete) {
                console.warn('WARNING: jabatanIdToDelete kosong!');
                showMessageModal('Peringatan', 'Tidak ada jabatan yang dipilih untuk dihapus.');
                document.getElementById('modalDeleteJabatan').classList.add('hidden');
                return;
            }

            // Disable button dan ubah text
            this.disabled = true;
            this.textContent = 'Menghapus...';

            console.log('DEBUG: Mengirim request DELETE untuk Jabatan ID:', jabatanIdToDelete);

            const formData = new FormData();
            formData.append('id', jabatanIdToDelete);

            // Perubahan di sini: Menggunakan window.location.origin sama seperti delete user
            fetch(`${window.location.origin}/admin/jabatan/delete`, { // Perhatikan: sesuaikan path ke 
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest', // Penting untuk cek isAJAX() di CI4
                    'Accept': 'application/json' // Mengharapkan respons JSON
                }
            })
            .then(response => {
                console.log('DEBUG: Response status:', response.status);
                if (!response.ok) {
                    return response.text().then(text => {
                        try {
                            const errorData = JSON.parse(text);
                            throw new Error(errorData.message || `HTTP error! Status: ${response.status}`);
                        } catch (e) {
                            throw new Error(`HTTP error! Status: ${response.status} - ${text}`);
                        }
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('DEBUG: Response data:', data);
                if (data.status === 'success') {
                    document.getElementById('modalDeleteJabatan').classList.add('hidden');
                    showMessageModal('Sukses!', data.message);
                    removeJabatanRowFromTable(jabatanIdToDelete); // Hapus baris dari tabel

                    // Opsional: Reload halaman setelah sedikit delay untuk memastikan semua terupdate
                    setTimeout(() => {
                        location.reload();
                    }, 1500); 

                } else {
                    let errorMessage = data.message || 'Gagal menghapus jabatan.';
                    if (data.errors) {
                        errorMessage += '\nDetail: ' + Object.values(data.errors).join('\n');
                    }
                    showMessageModal('Error!', errorMessage);
                    document.getElementById('modalDeleteJabatan').classList.add('hidden');
                }
            })
            .catch(error => {
                console.error('ERROR: Fetch operation failed:', error);
                showMessageModal('Error!', 'Terjadi kesalahan: ' + error.message);
                document.getElementById('modalDeleteJabatan').classList.add('hidden');
            })
            .finally(() => {
                // Reset button state
                const deleteBtn = document.getElementById('confirmDeleteJabatanBtn');
                if (deleteBtn) {
                    deleteBtn.disabled = false;
                    deleteBtn.textContent = 'Hapus';
                }
                // Reset variables
                jabatanIdToDelete = null;
                jabatanNameToDelete = null;
            });
        });
    } else {
        console.error('CRITICAL ERROR: Element #confirmDeleteJabatanBtn tidak ditemukan!');
    }

    // --- EVENT LISTENER UNTUK TOMBOL BATAL JABATAN ---
    const cancelDeleteJabatanModalBtn = document.getElementById('cancelDeleteJabatanModal');
    if (cancelDeleteJabatanModalBtn) {
        cancelDeleteJabatanModalBtn.addEventListener('click', function() {
            document.getElementById('modalDeleteJabatan').classList.add('hidden');
            jabatanIdToDelete = null;
            jabatanNameToDelete = null;
        });
    }

    // --- TUTUP MODAL JIKA KLIK DI LUAR JABATAN ---
    const modalDeleteJabatanOverlay = document.getElementById('modalDeleteJabatan');
    if (modalDeleteJabatanOverlay) {
        modalDeleteJabatanOverlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
                jabatanIdToDelete = null;
                jabatanNameToDelete = null;
            }
        });
    }
});