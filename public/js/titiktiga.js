const floatingMenu = document.getElementById('floatingMenu');
const modalInfoDetail = document.getElementById('modalInfoDetail');
const modalRename = document.getElementById('modalRename');
const newFileNameInput = document.getElementById('newFileName');
const modalDeleteConfirm = document.getElementById('modalDeleteConfirm');
const renameOption = document.getElementById('renameOption');
const infoDetailOption = document.getElementById('infoDetailOption');
const deleteOption = document.getElementById('deleteOption');

let currentFolderData = null;
let isModalOpening = false; // Flag untuk mencegah penutupan saat modal sedang dibuka

// Fungsi untuk menutup semua modal dan popup yang terbuka
function closeAllModalsAndPopups() {
    if (isModalOpening) return; // Jangan tutup jika sedang membuka modal
    
    if (floatingMenu) {
        floatingMenu.classList.add('hidden', 'opacity-0', 'scale-95');
        floatingMenu.classList.remove('opacity-100', 'scale-100');
    }

    if (modalRename) {
        const renameContent = modalRename.querySelector('.bg-white');
        if (renameContent) {
            renameContent.classList.remove('scale-100', 'opacity-100');
            renameContent.classList.add('scale-95', 'opacity-0');
        }
    }
    
    if (modalInfoDetail) {
        const infoContent = modalInfoDetail.querySelector('.bg-white');
        if (infoContent) {
            infoContent.classList.remove('scale-100', 'opacity-100');
            infoContent.classList.add('scale-95', 'opacity-0');
        }
    }

    if (modalDeleteConfirm) {
        const deleteContent = modalDeleteConfirm.querySelector('.bg-white');
        if (deleteContent) {
            deleteContent.classList.remove('scale-100', 'opacity-100');
            deleteContent.classList.add('scale-95', 'opacity-0');
        }
    }

    setTimeout(() => {
        if (modalRename) modalRename.classList.add('hidden');
        if (modalInfoDetail) modalInfoDetail.classList.add('hidden');
    }, 300);
}

// Fungsi untuk menampilkan/menyembunyikan menu konteks (titik tiga)
function toggleMenu(button) {
    // Prevent event bubbling
    event.stopPropagation();
    
    const row = button.closest('tr');
    if (!row) {
        console.error('Tidak dapat menemukan elemen <tr> terdekat.');
        return;
    }

    // Tangkap data dari data-attributes pada baris tabel yang diklik
    currentFolderData = {
        path: row.dataset.folderPath,
        id: row.dataset.folderId,
        name: row.dataset.folderName,
        folder_type: row.dataset.folderType,
        isShared: row.dataset.folderIsShared,
        sharedType: row.dataset.folderSharedType,
        ownerId: row.dataset.folderOwnerId,
        ownerName: row.dataset.folderOwnerName, 
        createdAt: row.dataset.folderCreatedAt,
        updatedAt: row.dataset.folderUpdatedAt
    };

    closeAllModalsAndPopups();

    if (!floatingMenu) {
        console.error('Elemen floatingMenu tidak ditemukan.');
        return;
    }

     // Pastikan elemen opsi menu ditemukan sebelum mencoba memanipulasi kelasnya
    if (renameOption) {
        if (currentFolderData.sharedType === 'read') {
            renameOption.classList.add('hidden'); // Sembunyikan jika hanya baca
        } else {
            renameOption.classList.remove('hidden'); // Tampilkan jika akses penuh atau bukan shared
        }
    }

    if (deleteOption) {
        if (currentFolderData.sharedType === 'read') {
            deleteOption.classList.add('hidden'); // Sembunyikan jika hanya baca
        } else {
            deleteOption.classList.remove('hidden'); // Tampilkan jika akses penuh atau bukan shared
        }
    }

    // Informasi Detail harus selalu terlihat
    if (infoDetailOption) {
        infoDetailOption.classList.remove('hidden');
    }

    // Hitung posisi menu
    const rect = button.getBoundingClientRect();
    const menuHeight = floatingMenu.offsetHeight || 170;
    const viewportBottom = window.innerHeight;
    const bottomSpace = viewportBottom - rect.bottom;

    let topPosition = rect.bottom + window.scrollY + 5;
    if (bottomSpace < menuHeight && rect.top - menuHeight - 5 > 0) {
        topPosition = rect.top + window.scrollY - menuHeight - 5;
    }

    const menuWidth = floatingMenu.offsetWidth || 192;
    let leftPosition = rect.left + window.scrollX - menuWidth + rect.width;

    floatingMenu.style.top = `${topPosition}px`;
    floatingMenu.style.left = `${leftPosition}px`;

    // Tampilkan menu dengan transisi
    floatingMenu.classList.remove('hidden', 'opacity-0', 'scale-95');
    requestAnimationFrame(() => {
        floatingMenu.classList.add('opacity-100', 'scale-100');
    });
}

// Global click listener dengan perbaikan
document.addEventListener('click', function (e) {
    // Jika sedang membuka modal, jangan tutup apapun
    if (isModalOpening) return;
    
    // Jangan tutup jika klik pada toggle button
    if (e.target.closest('button[onclick*="toggleMenu"]')) {
        return;
    }

    // Jangan tutup jika klik di dalam floating menu
    if (floatingMenu && floatingMenu.contains(e.target)) {
        return;
    }

    // Jangan tutup jika klik di dalam modal content
    if (modalRename && modalRename.contains(e.target)) {
        const modalContent = modalRename.querySelector('.bg-white');
        if (modalContent && modalContent.contains(e.target)) {
            return;
        }
    }

    if (modalInfoDetail && modalInfoDetail.contains(e.target)) {
        const modalContent = modalInfoDetail.querySelector('.bg-white');
        if (modalContent && modalContent.contains(e.target)) {
            return;
        }
    }

    if (modalDeleteConfirm && modalDeleteConfirm.contains(e.target)) {
        const modalContent = modalDeleteConfirm.querySelector('.bg-white');
        if (modalContent && modalContent.contains(e.target)) {
            return;
        }
    }

    // Tutup semua modal dan popup
    closeAllModalsAndPopups();
});

// Prevent modal content click from closing modal
if (modalRename) {
    const modalRenameContent = modalRename.querySelector('.bg-white');
    if (modalRenameContent) {
        modalRenameContent.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    }
}

if (modalInfoDetail) {
    const modalInfoDetailContent = modalInfoDetail.querySelector('.bg-white');
    if (modalInfoDetailContent) {
        modalInfoDetailContent.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    }
}

// Prevent modal delete content click from closing modal
if (modalDeleteConfirm) {
    const modalDeleteContent = modalDeleteConfirm.querySelector('.bg-white');
    if (modalDeleteContent) {
        modalDeleteContent.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    }
}

// --- Modal Ganti Nama ---
function showRenameModal() {
    event.stopPropagation(); // Prevent bubbling
    isModalOpening = true;
    
    setTimeout(() => {
        closeAllModalsAndPopups();
        
        if (currentFolderData && newFileNameInput) {
            newFileNameInput.value = currentFolderData.name;
        }
        
        if (modalRename) {
            modalRename.classList.remove('hidden');
            requestAnimationFrame(() => {
                const content = modalRename.querySelector('.bg-white');
                if (content) {
                    content.classList.remove('scale-95', 'opacity-0');
                    content.classList.add('scale-100', 'opacity-100');
                }
            });
        }
        
        isModalOpening = false;
    }, 100);
}

function closeRenameModal() {
    if (modalRename) {
        const content = modalRename.querySelector('.bg-white');
        if (content) {
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
        }
        setTimeout(() => {
            modalRename.classList.add('hidden');
        }, 300);
    }
    
    // Tutup juga floating menu setelah modal ditutup
    if (floatingMenu) {
        floatingMenu.classList.add('hidden', 'opacity-0', 'scale-95');
        floatingMenu.classList.remove('opacity-100', 'scale-100');
    }
}

function submitRename() {
    if (!newFileNameInput) {
        alert('Elemen input nama file baru tidak ditemukan.');
        return;
    }
    
    const newName = newFileNameInput.value.trim();
    if (newName === '') {
        alert('Nama baru tidak boleh kosong');
        return;
    }

    if (!currentFolderData || !currentFolderData.id) {
        alert('Tidak ada folder yang dipilih untuk diganti namanya.');
        return;
    }

    // Disable button untuk prevent double submit
    const submitButton = document.querySelector('#modalRename button[onclick="submitRename()"]');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = 'Menyimpan...';
    }

    // Kirim AJAX request
    fetch('/folders/rename', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
            // --- PENTING: Tambahkan CSRF token jika CodeIgniter kamu menggunakannya ---
            // 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token-name-in-html"]').getAttribute('content') 
            // Sesuaikan 'csrf-token-name-in-html' dengan nama meta tag atau input hidden di HTML kamu
        },
        body: `folder_id=${currentFolderData.id}&new_name=${encodeURIComponent(newName)}`
    })
    .then(response => {
        // Cek jika response tidak OK (misal HTTP 400, 500)
        if (!response.ok) {
            // Coba parse JSON error dari server jika ada
            return response.json().then(err => {
                throw new Error(err.message || 'Server error occurred with status ' + response.status);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            // --- SIMPAN NOTIFIKASI KE SESSION STORAGE SEBELUM RELOAD ---
            sessionStorage.setItem('notificationMessage', 'Nama folder berhasil diubah!');
            sessionStorage.setItem('notificationType', 'success');
            
            // --- RELOAD HALAMAN ---
            location.reload(); 
            // Setelah ini, semua kode di bawahnya tidak akan dieksekusi sampai halaman baru dimuat
        } else {
            // Tampilkan pesan error jika server merespon 'fail'
            let errorMessage = 'Gagal mengubah nama folder.';
            if (data.messages && typeof data.messages === 'object') {
                errorMessage = Object.values(data.messages).join(', ');
            } else if (data.message) {
                errorMessage = data.message;
            }
            showNotification(errorMessage, 'error');
        }
    })
    .catch(error => {
        // Tangani error jaringan atau error dari throw di .then() sebelumnya
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat mengubah nama folder: ' + error.message, 'error');
    })
    .finally(() => {
        // Enable button kembali
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Simpan';
        }
    });
}

// Fungsi untuk update nama folder di tabel tanpa refresh (tidak digunakan jika pakai reload)
function updateFolderNameInTable(folderId, newName) {
    // Fungsi ini tidak akan dieksekusi jika location.reload() dipanggil
    // Namun tetap dipertahankan karena bagian lain mungkin membutuhkannya
    const tableRows = document.querySelectorAll('tbody tr');
    
    tableRows.forEach(row => {
        const folderData = JSON.parse(row.getAttribute('data-folder') || '{}');
        if (folderData.id == folderId) {
            const nameCell = row.querySelector('td:nth-child(2)');
            if (nameCell) {
                nameCell.textContent = newName;
            }
            folderData.name = newName;
            row.setAttribute('data-folder', JSON.stringify(folderData));
        }
    });
}

// Fungsi untuk menampilkan notifikasi
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-[10001] px-6 py-4 rounded-lg shadow-lg text-white font-medium transition-all duration-300 transform translate-x-full`;
    
    if (type === 'success') {
        notification.classList.add('bg-green-500');
    } else if (type === 'error') {
        notification.classList.add('bg-red-500');
    } else {
        notification.classList.add('bg-blue-500');
    }
    
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
        notification.classList.add('translate-x-0');
    }, 100);
    
    setTimeout(() => {
        notification.classList.remove('translate-x-0');
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000); // Notifikasi akan hilang setelah 3 detik
}

// --- Modal Informasi Detail ---
function showInfoDetailModal() {
    event.stopPropagation(); // Prevent bubbling
    isModalOpening = true;

    if (!currentFolderData || !currentFolderData.id) {
        alert('Tidak ada data folder yang dipilih.');
        isModalOpening = false;
        return;
    }

    setTimeout(() => {
        closeAllModalsAndPopups();
        
        if (modalInfoDetail) {
            modalInfoDetail.classList.remove('hidden');
            requestAnimationFrame(() => {
                const content = modalInfoDetail.querySelector('.bg-white');
                if (content) {
                    content.classList.remove('scale-95', 'opacity-0');
                    content.classList.add('scale-100', 'opacity-100');
                }
            });
        }

        fillModalWithCurrentData();
        isModalOpening = false;
    }, 100);
}

function fillModalWithCurrentData() {
    const nameElement = document.getElementById('detailName');
    if (nameElement) {
        nameElement.textContent = currentFolderData.name || 'N/A';
    }

    let jenisText = 'N/A';
    if (currentFolderData.folder_type === 'personal') {
        jenisText = 'Folder Personal';
    } else if (currentFolderData.folder_type === 'shared' || currentFolderData.isShared == '1') {
        jenisText = 'Folder Berbagi';
        if (currentFolderData.sharedType) {
            jenisText += ` (${currentFolderData.sharedType === 'full' ? 'Akses Penuh' : 'Hanya Baca'})`;
        }
    } else {
        jenisText = 'Folder Biasa';
    }
    
    const jenisElement = document.getElementById('detailJenis');
    if (jenisElement) {
        jenisElement.textContent = jenisText;
    }

    const ukuranElement = document.getElementById('detailUkuran');
    if (ukuranElement) {
        ukuranElement.textContent = 'Menghitung...';
        calculateFolderSize(currentFolderData.id);
    }

    const pemilikElement = document.getElementById('detailPemilik');
    if (pemilikElement) {
        if (currentFolderData.ownerName && currentFolderData.ownerName !== 'Unknown') {
            pemilikElement.textContent = currentFolderData.ownerName;
        } else if (currentFolderData.ownerId) {
            pemilikElement.textContent = 'Memuat...';
            fetchUserName(currentFolderData.ownerId);
        } else {
            pemilikElement.textContent = 'Tidak diketahui';
        }
    }

    const dibuatElement = document.getElementById('detailDibuat');
    if (dibuatElement) {
        if (currentFolderData.createdAt) {
            const createdDate = new Date(currentFolderData.createdAt);
            dibuatElement.textContent = createdDate.toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } else {
            dibuatElement.textContent = 'N/A';
        }
    }

    const updatedElement = document.getElementById('detailUpdated');
    if (updatedElement) {
        if (currentFolderData.updatedAt) {
            const updatedDate = new Date(currentFolderData.updatedAt);
            updatedElement.textContent = updatedDate.toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } else {
            updatedElement.textContent = 'N/A';
        }
    }
}

// Fungsi untuk mengambil nama user berdasarkan ID
function fetchUserName(userId) {
    fetch(`/api/folder/user/${userId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const pemilikElement = document.getElementById('detailPemilik');
            if (pemilikElement && data.status === 'success') {
                pemilikElement.textContent = data.data.name ||` User ID: ${userId}`;
            } else {
                pemilikElement.textContent = `User ID: ${userId}`;
            }
        })
        .catch(error => {
            console.error('Error fetching user name:', error);
            const pemilikElement = document.getElementById('detailPemilik');
            if (pemilikElement) {
                pemilikElement.textContent =` User ID: ${userId}`;
            }
        });
}

// Fungsi untuk menghitung ukuran folder (opsional)
function calculateFolderSize(folderId) {
    const ukuranElement = document.getElementById('detailUkuran');
    if (ukuranElement) {
        ukuranElement.textContent = '0 KB'; // Placeholder
        
        /*
        fetch(/api/folder/size/${folderId})
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    ukuranElement.textContent = data.data.size || '0 KB';
                }
            })
            .catch(error => {
                console.error('Error calculating folder size:', error);
                ukuranElement.textContent = '0 KB';
            });
        */
    }
}

function closeInfoDetailModal() {
    if (modalInfoDetail) {
        const content = modalInfoDetail.querySelector('.bg-white');
        if (content) {
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
        }
        setTimeout(() => {
            modalInfoDetail.classList.add('hidden');
        }, 300);
    }
    
    if (floatingMenu) {
        floatingMenu.classList.add('hidden', 'opacity-0', 'scale-95');
        floatingMenu.classList.remove('opacity-100', 'scale-100');
    }
}

// Function untuk mendapatkan data detail lengkap dari server
function fetchDetailFromServer() {
    if (!currentFolderData || !currentFolderData.id) {
        console.warn('Tidak ada ID folder untuk diambil detail dari server.');
        return;
    }

    fetch(`/api/folder/detail/${currentFolderData.id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                const info = data.data;
                
                const pemilikElement = document.getElementById('detailPemilik');
                if (pemilikElement && info.owner && info.owner !== 'Unknown') {
                    pemilikElement.textContent = info.owner;
                }
                
                const jenisElement = document.getElementById('detailJenis');
                if (jenisElement && info.folder_type) {
                    let jenisText = 'Folder';
                    if (info.folder_type === 'personal') {
                        jenisText = 'Folder Personal';
                    } else if (info.folder_type === 'shared') {
                        jenisText = `Folder Berbagi`;
                        if (info.shared_type) {
                            jenisText += ` (${info.shared_type === 'full' ? 'Akses Penuh' : 'Hanya Baca'})`;
                        }
                    }
                    jenisElement.textContent = jenisText;
                }
            }
        })
        .catch(error => {
            console.error('Error fetching additional details:', error);
        });
}

// Fungsi untuk menampilkan modal konfirmasi delete
function showDeleteConfirmModal() {
    event.stopPropagation();
    isModalOpening = true;
    
    setTimeout(() => {
        closeAllModalsAndPopups();
        
        // Set nama folder yang akan dihapus
        const folderNameSpan = document.getElementById('deleteConfirmFolderName');
        if (folderNameSpan && currentFolderData) {
            folderNameSpan.textContent = currentFolderData.name;
        }
        
        if (modalDeleteConfirm) {
            modalDeleteConfirm.classList.remove('hidden');
            requestAnimationFrame(() => {
                const content = modalDeleteConfirm.querySelector('.bg-white');
                if (content) {
                    content.classList.remove('scale-95', 'opacity-0');
                    content.classList.add('scale-100', 'opacity-100');
                }
            });
        }
        
        isModalOpening = false;
    }, 100);
}

// Fungsi untuk menutup modal konfirmasi delete
function closeDeleteConfirmModal() {
    if (modalDeleteConfirm) {
        const content = modalDeleteConfirm.querySelector('.bg-white');
        if (content) {
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
        }
        setTimeout(() => {
            modalDeleteConfirm.classList.add('hidden');
        }, 300);
    }
    
    // Tutup juga floating menu
    if (floatingMenu) {
        floatingMenu.classList.add('hidden', 'opacity-0', 'scale-95');
        floatingMenu.classList.remove('opacity-100', 'scale-100');
    }
}

// Fungsi untuk konfirmasi dan menjalankan delete
function confirmDeleteFolder() {
    if (!currentFolderData || !currentFolderData.id) {
        alert('Tidak ada folder yang dipilih untuk dihapus.');
        return;
    }

    // Disable button untuk prevent double submit
    const deleteButton = document.querySelector('#modalDeleteConfirm button[onclick="confirmDeleteFolder()"]');
    if (deleteButton) {
        deleteButton.disabled = true;
        deleteButton.innerHTML = 'Menghapus...';
    }

    // Kirim AJAX request
    fetch('/folders/delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
            // Tambahkan CSRF token jika diperlukan
        },
        body: `folder_id=${currentFolderData.id}`
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Server error occurred with status ' + response.status);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            // Simpan notifikasi ke session storage
            sessionStorage.setItem('notificationMessage', 'Folder berhasil dihapus!');
            sessionStorage.setItem('notificationType', 'success');
            
            // Reload halaman
            location.reload();
        } else {
            // Tampilkan pesan error
            let errorMessage = 'Gagal menghapus folder.';
            if (data.messages && typeof data.messages === 'object') {
                errorMessage = Object.values(data.messages).join(', ');
            } else if (data.message) {
                errorMessage = data.message;
            }
            showNotification(errorMessage, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat menghapus folder: ' + error.message, 'error');
    })
    .finally(() => {
        // Enable button kembali
        if (deleteButton) {
            deleteButton.disabled = false;
            deleteButton.innerHTML = 'Hapus';
        }
    });
}


// --- LOGIKA UNTUK MENAMPILKAN NOTIFIKASI SETELAH REFRESH ---
document.addEventListener('DOMContentLoaded', () => {
    // Ambil pesan dan tipe notifikasi dari sessionStorage
    const message = sessionStorage.getItem('notificationMessage');
    const type = sessionStorage.getItem('notificationType');

    // Jika ada notifikasi yang tersimpan
    if (message && type) {
        // Hapus notifikasi dari sessionStorage agar tidak muncul lagi
        sessionStorage.removeItem('notificationMessage');
        sessionStorage.removeItem('notificationType');

        // Tampilkan notifikasi setelah delay 2 detik
        setTimeout(() => {
            showNotification(message, type);
        }, 200); // 2000 milidetik = 2 detik
    }
});