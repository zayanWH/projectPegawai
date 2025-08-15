const floatingMenu = document.getElementById('floatingMenu');
const modalInfoDetail = document.getElementById('modalInfoDetail');
const modalRename = document.getElementById('modalRename');
const newFileNameInput = document.getElementById('newFileName');
const modalDeleteConfirm = document.getElementById('modalDeleteConfirm');
const renameOption = document.getElementById('renameOption');
const infoDetailOption = document.getElementById('infoDetailOption');
const deleteOption = document.getElementById('deleteOption');

let currentFileData = null;
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
    event.stopPropagation();
    
    const parentItem = button.closest('[data-folder-id], [data-file-id]');
    if (!parentItem) {
        console.error('Tidak dapat menemukan elemen <tr> terdekat.');
        return;
    }

    // DEBUG: Log semua data attributes
    // DEBUG: Log semua data attributes
    console.log('=== DEBUG DATA ATTRIBUTES ===');
    console.log('Item Type:', parentItem.dataset.itemType);
    console.log('File ID:', parentItem.dataset.fileId);
    console.log('File Name:', parentItem.dataset.fileName);
    console.log('File Size:', parentItem.dataset.fileSize);
    console.log('File Type:', parentItem.dataset.fileType);
    console.log('File Path:', parentItem.dataset.filePath);
    console.log('File Owner ID:', parentItem.dataset.fileOwnerId);
    console.log('File Created At:', parentItem.dataset.fileCreatedAt);
    console.log('File Updated At:', parentItem.dataset.fileUpdatedAt);
    console.log('=== END DEBUG ===');

    const itemType = parentItem.dataset.itemType;
    
    if (itemType === 'file') {
        // Perbaikan: Ganti 'row' menjadi 'parentItem' di sini
        currentFileData = {
            id: parentItem.dataset.fileId,
            name: parentItem.dataset.fileName,
            size: parentItem.dataset.fileSize,
            path: parentItem.dataset.filePath,
            ownerId: parentItem.dataset.fileOwnerId,
            ownerName: parentItem.dataset.fileOwnerName,
            createdAt: parentItem.dataset.fileCreatedAt,
            updatedAt: parentItem.dataset.fileUpdatedAt,
            type: 'file'
        };
    } else {
        // Perbaikan: Ganti 'row' menjadi 'parentItem' di sini
        currentFileData = {
            id: parentItem.dataset.folderId,
            name: parentItem.dataset.folderName,
            folder_type: parentItem.dataset.folderType,
            isShared: parentItem.dataset.folderIsShared,
            sharedType: parentItem.dataset.folderSharedType,
            ownerId: parentItem.dataset.folderOwnerId,
            ownerName: parentItem.dataset.folderOwnerName,
            createdAt: parentItem.dataset.folderCreatedAt,
            updatedAt: parentItem.dataset.folderUpdatedAt,
            type: 'folder'
        };
    }

    // Tampilkan menu...
    closeAllModalsAndPopups();

    if (!floatingMenu) {
        console.error('Elemen floatingMenu tidak ditemukan.');
        return;
    }

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
    // Perbaikan: Tambahkan log di awal fungsi
    console.log('showRenameModal function entered.');
    event.stopPropagation();
    isModalOpening = true;
    
    // Perbaikan: Tambahkan pemeriksaan untuk memastikan data sudah tersedia
    try {
        if (!currentFileData || !currentFileData.name || !newFileNameInput) {
            console.error('currentFileData atau newFileNameInput tidak tersedia saat membuka modal ganti nama.');
            showNotification('Gagal membuka modal ganti nama. Silakan coba lagi.', 'error');
            isModalOpening = false;
            return;
        }

        // DEBUG: Log data saat modal akan dibuka
        console.log('=== DEBUG SHOW RENAME MODAL ===');
        console.log('currentFileData:', currentFileData);
        console.log('newFileNameInput:', newFileNameInput);
        console.log('currentFileData.name:', currentFileData.name);
        console.log('=== END DEBUG ===');
        
        setTimeout(() => {
            closeAllModalsAndPopups();
            
            console.log('Setting input value to:', currentFileData.name);
            newFileNameInput.value = currentFileData.name;
            
            // DEBUG: Cek apakah value benar-benar di-set
            console.log('Input value after setting:', newFileNameInput.value);
            
            // Update title modal berdasarkan tipe item
            const modalTitle = modalRename.querySelector('h2');
            if (modalTitle) {
                if (currentFileData.itemType === 'file') {
                    modalTitle.textContent = 'Ganti Nama File';
                } else {
                    modalTitle.textContent = 'Ganti Nama Folder';
                }
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
    } catch (e) {
        // Perbaikan: Tangkap dan log error secara eksplisit
        console.error('An unexpected error occurred in showRenameModal:', e);
        showNotification('Terjadi kesalahan tak terduga saat membuka modal. Silakan periksa konsol.', 'error');
    }
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
        alert('Elemen input nama baru tidak ditemukan.');
        return;
    }
    
    const newName = newFileNameInput.value.trim();
    if (newName === '') {
        alert('Nama baru tidak boleh kosong');
        return;
    }

    if (!currentFileData || !currentFileData.id) {
        alert('Tidak ada item yang dipilih untuk diganti namanya.');
        return;
    }

    // Disable button untuk prevent double submit
    const submitButton = document.querySelector('#modalRename button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = 'Menyimpan...';
    }

    // PERBAIKAN: Tentukan endpoint berdasarkan tipe item
    let endpoint, bodyParams;
    
    if (currentFileData.type === 'file') {
        endpoint = '/file/rename'; // Sesuaikan dengan route Anda
        bodyParams = `file_id=${currentFileData.id}&new_name=${encodeURIComponent(newName)}`;
    } else {
        endpoint = '/folders/rename';
        bodyParams = `folder_id=${currentFileData.id}&new_name=${encodeURIComponent(newName)}`;
    }

    // Kirim AJAX request
    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: bodyParams
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
            const itemType = currentFileData.itemType === 'file' ? 'file' : 'folder';
            sessionStorage.setItem('notificationMessage', `Nama ${itemType} berhasil diubah!`);
            sessionStorage.setItem('notificationType', 'success');
            location.reload();
        } else {
            let errorMessage = 'Gagal mengubah nama.';
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
        showNotification('Terjadi kesalahan saat mengubah nama: ' + error.message, 'error');
    })
    .finally(() => {
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

    // PERBAIKAN: Cek currentFileData bukan currentFileData
    if (!currentFileData || !currentFileData.id) {
        alert('Tidak ada data file/folder yang dipilih.');
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

// PERBAIKAN: Ganti semua currentFileData menjadi currentFileData
function fillModalWithCurrentData() {
    console.log('DEBUG: Fungsi fillModalWithCurrentData dipanggil');
    console.log('DEBUG: currentFileData saat modal dibuka', currentFileData);

    const nameElement = document.getElementById('detailName');
    if (nameElement) {
        nameElement.textContent = currentFileData.name || 'N/A';
    }

    let jenisText = 'N/A';

    // Pastikan type sudah diset dengan benar saat data diambil
    // Misal: currentFileData.type = 'file' atau 'folder'

    if (currentFileData.type === 'file') {
        // UNTUK FILE
        // Ambil ekstensi dari nama file. Ini adalah cara yang paling andal.
        if (currentFileData.name) {
            const extension = currentFileData.name.split('.').pop().toUpperCase();
            jenisText = extension + ' File';
        } else {
            jenisText = 'Jenis File Tidak Diketahui';
        }
    } else if (currentFileData.type === 'folder') {
        // UNTUK FOLDER
        if (currentFileData.folder_type === 'personal') {
            jenisText = 'Folder Personal';
        } else if (currentFileData.folder_type === 'shared' || currentFileData.isShared === '1') {
            jenisText = 'Folder Berbagi';
            if (currentFileData.sharedType) {
                jenisText += ` (${currentFileData.sharedType === 'full' ? 'Akses Penuh' : 'Hanya Baca'})`;
            }
        } else {
            jenisText = 'Folder Biasa';
        }
    }

const jenisElement = document.getElementById('detailJenis');
if (jenisElement) {
    jenisElement.textContent = jenisText;
}

    const ukuranElement = document.getElementById('detailUkuran');
    
    if (ukuranElement) { // Pastikan elemen ditemukan
        if (currentFileData.type === 'file') {
            const fileSize = Number(currentFileData.size);
            
            // Log ini untuk memastikan nilai fileSize sudah menjadi angka
            console.log('DEBUG: Nilai fileSize setelah konversi:', fileSize);
            
            if (fileSize > 0) {
                ukuranElement.textContent = formatFileSize(fileSize);
            } else {
                ukuranElement.textContent = '0 KB';
            }
        } else {
            ukuranElement.textContent = 'Tidak tersedia';
            // atau 'Menghitung...' jika kamu memiliki logika untuk folder
        }
    }

    const pemilikElement = document.getElementById('detailPemilik');
    if (pemilikElement) {
        console.log('Owner check:', {
            ownerName: currentFileData.ownerName,
            ownerId: currentFileData.ownerId
        });
        
        // SEDERHANA: Langsung gunakan ownerName dari data attribute
        if (currentFileData.ownerName && currentFileData.ownerName !== 'Unknown' && currentFileData.ownerName !== '' && currentFileData.ownerName !== 'null') {
            pemilikElement.textContent = currentFileData.ownerName;
        } else if (currentFileData.ownerId && currentFileData.ownerId !== '' && currentFileData.ownerId !== '0') {
            pemilikElement.textContent = 'User ID: ' + currentFileData.ownerId;
        } else {
            pemilikElement.textContent = 'Tidak diketahui';
        }
    }

    const dibuatElement = document.getElementById('detailDibuat');
    if (dibuatElement) {
        if (currentFileData.createdAt) {
            const createdDate = new Date(currentFileData.createdAt);
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
        if (currentFileData.updatedAt) {
            const updatedDate = new Date(currentFileData.updatedAt);
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

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
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

    console.log('Menampilkan modal delete untuk:', currentFileData);
    event.stopPropagation();
    isModalOpening = true;
    
    setTimeout(() => {
        closeAllModalsAndPopups();
        
        // Set nama file/folder yang akan dihapus
        const itemNameSpan = document.getElementById('deleteConfirmFolderName');
        if (itemNameSpan && currentFileData) {
            itemNameSpan.textContent = currentFileData.name;
        }
        
        // Update text modal berdasarkan tipe item
        const modalTitle = document.querySelector('#modalDeleteConfirm h3');
        const modalMessage = document.querySelector('#modalDeleteConfirm p');
        const deleteButton = document.querySelector('#modalDeleteConfirm button[onclick="confirmDeleteItem()"]');
        
        if (currentFileData && currentFileData.type) {
            if (currentFileData.type === 'folder') {
                if (modalTitle) modalTitle.textContent = 'Konfirmasi Hapus Folder';
                if (modalMessage) modalMessage.innerHTML = `Apakah Anda yakin ingin menghapus folder <strong id="deleteConfirmFolderName">${currentFileData.name}</strong>? Tindakan ini tidak dapat dibatalkan.`;
                if (deleteButton) deleteButton.textContent = 'Hapus Folder';
            } else {
                if (modalTitle) modalTitle.textContent = 'Konfirmasi Hapus File';
                if (modalMessage) modalMessage.innerHTML = `Apakah Anda yakin ingin menghapus file <strong id="deleteConfirmFolderName">${currentFileData.name}</strong>? Tindakan ini tidak dapat dibatalkan.`;
                if (deleteButton) deleteButton.textContent = 'Hapus File';
            }
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

// Fungsi untuk konfirmasi dan menjalankan delete (file atau folder)
function confirmDeleteItem() {
    if (!currentFileData || !currentFileData.id) {
        alert('Tidak ada item yang dipilih untuk dihapus.');
        return;
    }

    // Tentukan route dan parameter berdasarkan tipe
    let route, bodyParam, itemType;
    if (currentFileData.type === 'folder') {
        route = '/folders/delete';
        bodyParam = `folder_id=${currentFileData.id}`;
        itemType = 'Folder';
    } else {
        route = '/file/delete';
        bodyParam = `file_id=${currentFileData.id}`;
        itemType = 'File';
    }

    // Disable button untuk prevent double submit
    const deleteButton = document.querySelector('#modalDeleteConfirm button[onclick="confirmDeleteItem()"]');
    if (deleteButton) {
        deleteButton.disabled = true;
        deleteButton.innerHTML = `Menghapus ${itemType}...`;
    }

    // Kirim AJAX request
    fetch(route, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
            // Tambahkan CSRF token jika diperlukan
            // 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: bodyParam
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
            // Tutup modal terlebih dahulu
            closeDeleteConfirmModal();
            
            // Simpan notifikasi ke session storage
            sessionStorage.setItem('notificationMessage',` ${itemType} berhasil dihapus!`);
            sessionStorage.setItem('notificationType', 'success');
            
            // Reload halaman
            location.reload();
        } else {
            // Tampilkan pesan error
            let errorMessage = `Gagal menghapus ${itemType.toLowerCase()}.`;
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
        showNotification(`Terjadi kesalahan saat menghapus ${itemType.toLowerCase()}: ` + error.message, 'error');
    })
    .finally(() => {
        // Enable button kembali
        if (deleteButton) {
            deleteButton.disabled = false;
            deleteButton.innerHTML = currentFileData.type === 'folder' ? 'Hapus Folder' : 'Hapus File';
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