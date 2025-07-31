document.addEventListener('DOMContentLoaded', function() {
    let userIdToDelete = null;
    let userNameToDelete = null;

    // --- FUNGSI MODAL PESAN KUSTOM ---
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

    // --- FUNGSI UNTUK MENGUPDATE TABEL ---
    function removeUserRowFromTable(userId) {
        const row = document.querySelector(`tr[data-user-id="${userId}"]`);
        if (row) {
            row.style.transition = 'opacity 0.3s ease-out';
            row.style.opacity = '0';
            setTimeout(() => {
                row.remove();
                updateRowNumbers();
            }, 300);
        }
    }

    function updateRowNumbers() {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            const numberCell = row.querySelector('td:first-child');
            if (numberCell && !isNaN(numberCell.textContent) && row.querySelector('td:first-child').tagName === 'TD') {
                numberCell.textContent = index + 1;
            }
        });
    }

    // --- EVENT LISTENER UNTUK TOMBOL DELETE ---
    document.addEventListener('click', function(e) {
        const deleteButton = e.target.closest('.open-delete-user-modal');
        if (deleteButton) {
            e.preventDefault();
            
            // Ambil data dari button yang diklik
            userIdToDelete = deleteButton.getAttribute('data-user-id');
            userNameToDelete = deleteButton.getAttribute('data-user-name') || 'User ini';

            console.log('DEBUG: User ID to delete:', userIdToDelete);
            console.log('DEBUG: User Name to delete:', userNameToDelete);

            // Update pesan konfirmasi tanpa menampilkan nama user atau ID
            document.querySelector('#modalDeleteUser p').textContent = 'Yakin ingin menghapus data user ini? Tindakan ini tidak dapat dibatalkan.';
            document.getElementById('modalDeleteUser').classList.remove('hidden');
        }
    });

    // --- EVENT LISTENER UNTUK KONFIRMASI DELETE ---
    const confirmDeleteBtn = document.getElementById('confirmDeleteUserBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            console.log('DEBUG: Tombol konfirmasi delete diklik!');

            if (!userIdToDelete) {
                console.warn('WARNING: userIdToDelete kosong!');
                showMessageModal('Peringatan', 'Tidak ada user yang dipilih untuk dihapus.');
                document.getElementById('modalDeleteUser').classList.add('hidden');
                return;
            }

            // Disable button dan ubah text
            this.disabled = true;
            this.textContent = 'Menghapus...';

            console.log('DEBUG: Mengirim request DELETE untuk User ID:', userIdToDelete);

            const formData = new FormData();
            formData.append('id', userIdToDelete);

            fetch(`${window.location.origin}/admin/users/delete`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
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
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('DEBUG: Response data:', data);
                if (data.status === 'success') {
                    document.getElementById('modalDeleteUser').classList.add('hidden');
                    showMessageModal('Sukses!', data.message);
                    removeUserRowFromTable(userIdToDelete);

                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    let errorMessage = data.message || 'Gagal menghapus user.';
                    if (data.errors) {
                        errorMessage += '\nDetail: ' + Object.values(data.errors).join('\n');
                    }
                    showMessageModal('Error!', errorMessage);
                    document.getElementById('modalDeleteUser').classList.add('hidden');
                }
            })
            .catch(error => {
                console.error('ERROR: Fetch operation failed:', error);
                showMessageModal('Error!', 'Terjadi kesalahan: ' + error.message);
                document.getElementById('modalDeleteUser').classList.add('hidden');
            })
            .finally(() => {
                // Reset button state
                const deleteBtn = document.getElementById('confirmDeleteUserBtn');
                if (deleteBtn) {
                    deleteBtn.disabled = false;
                    deleteBtn.textContent = 'Hapus';
                }
                // Reset variables
                userIdToDelete = null;
                userNameToDelete = null;
            });
        });
    } else {
        console.error('CRITICAL ERROR: Element #confirmDeleteUserBtn tidak ditemukan!');
    }

    // --- EVENT LISTENER UNTUK TOMBOL BATAL ---
    const cancelDeleteUserModalBtn = document.getElementById('cancelDeleteUserModal');
    if (cancelDeleteUserModalBtn) {
        cancelDeleteUserModalBtn.addEventListener('click', function() {
            document.getElementById('modalDeleteUser').classList.add('hidden');
            userIdToDelete = null;
            userNameToDelete = null;
        });
    }

    // --- TUTUP MODAL JIKA KLIK DI LUAR ---
    const modalDeleteUserOverlay = document.getElementById('modalDeleteUser');
    if (modalDeleteUserOverlay) {
        modalDeleteUserOverlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
                userIdToDelete = null;
                userNameToDelete = null;
            }
        });
    }
});