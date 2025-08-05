document.addEventListener('DOMContentLoaded', function () {
    const openCreateFolderBtn = document.getElementById('openCreateFolder');
    const modalCreateFolder = document.getElementById('modalCreateFolder');
    const cancelModalBtn = document.getElementById('cancelModal');
    const folderNameInput = document.getElementById('folderName');
    const folderTypeSelect = document.getElementById('folderType');
    const folderAccessSelect = document.getElementById('folderAccess');

    // Buka modal
    if (openCreateFolderBtn) {
        openCreateFolderBtn.addEventListener('click', function (event) {
            event.preventDefault();
            modalCreateFolder?.classList.remove('hidden');

            const dropdownMenu = document.getElementById('dropdownMenu');
            if (dropdownMenu) {
                dropdownMenu.classList.add('hidden');
            }
        });
    }

    // Tutup modal
    if (cancelModalBtn) {
        cancelModalBtn.addEventListener('click', function () {
            modalCreateFolder?.classList.add('hidden');
        });
    }

    // Klik luar modal untuk tutup
    if (modalCreateFolder) {
        modalCreateFolder.addEventListener('click', function (event) {
            if (event.target === modalCreateFolder) {
                modalCreateFolder.classList.add('hidden');
            }
        });
    }

    // Tombol buat folder
    const createFolderBtn = document.getElementById('createFolderBtn');
    if (createFolderBtn) {
        createFolderBtn.addEventListener('click', function () {
            const folderName = folderNameInput?.value.trim();
            const folderType = folderTypeSelect?.value;
            const folderAccess = folderAccessSelect?.value;

            if (!folderName) {
                alert('Nama folder tidak boleh kosong.');
                return;
            }

            console.log({
                name: folderName,
                type: folderType,
                access: folderAccess
            });

            // TODO: fetch() ke endpoint create folder di sini
            modalCreateFolder?.classList.add('hidden');
        });
    }
});
