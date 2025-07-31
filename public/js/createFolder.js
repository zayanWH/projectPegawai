document.addEventListener('DOMContentLoaded', function() {
    const openCreateFolderBtn = document.getElementById('openCreateFolder');
    const modalCreateFolder = document.getElementById('modalCreateFolder');
    const cancelModalBtn = document.getElementById('cancelModal');
    const createFolderForm = document.getElementById('createFolderForm');
    const folderNameInput = document.getElementById('folderName');
    const folderTypeSelect = document.getElementById('folderType');
    const sharedOptionsDiv = document.getElementById('sharedOptions');
    const parentIdInput = document.getElementById('parentId');

    if (openCreateFolderBtn) {
        openCreateFolderBtn.addEventListener('click', function(event) {
            event.preventDefault();
            modalCreateFolder.classList.remove('hidden');
            const dropdownMenu = document.getElementById('dropdownMenu');
            if (dropdownMenu && !dropdownMenu.classList.contains('hidden')) {
                dropdownMenu.classList.add('hidden');
            }
        });
    }

    if (cancelModalBtn) {
        cancelModalBtn.addEventListener('click', function() {
            modalCreateFolder.classList.add('hidden');
            createFolderForm.reset();
            sharedOptionsDiv.classList.add('hidden');
        });
    }

    if (modalCreateFolder) {
        modalCreateFolder.addEventListener('click', function(event) {
            if (event.target === modalCreateFolder) {
                modalCreateFolder.classList.add('hidden');
                createFolderForm.reset();
                sharedOptionsDiv.classList.add('hidden');
            }
        });
    }

    if (folderTypeSelect) {
        folderTypeSelect.addEventListener('change', function() {
            if (this.value === 'shared') {
                sharedOptionsDiv.classList.remove('hidden');
            } else {
                sharedOptionsDiv.classList.add('hidden');
            }
        });
    }

    if (createFolderForm) {
        createFolderForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const folderName = folderNameInput.value.trim();
            const parentId = parentIdInput.value || null;
            const folderType = folderTypeSelect.value;
            let isShared = 0;
            let sharedType = null;
            let accessRoles = [];

            if (!folderName) {
                alert('Nama folder tidak boleh kosong.');
                return;
            }

            if (folderType === 'shared') {
                isShared = 1;
                sharedType = document.getElementById('sharedType').value;
                document.querySelectorAll('input[name="access_roles[]"]:checked').forEach(checkbox => {
                    accessRoles.push(checkbox.value);
                });
            }

            const csrfToken = document.querySelector('input[name="<?= csrf_token() ?>"]').value;
            const targetUrl = window.baseUrl + 'hrd/createFolder';

            fetch(targetUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': csrfToken
                },
                body: JSON.stringify({
                    name: folderName,
                    parent_id: parentId,
                    folder_type: folderType,
                    is_shared: isShared,
                    shared_type: sharedType,
                    access_roles: accessRoles
                })
            })
            .then(response => {
                const newCsrfToken = response.headers.get('X-CSRF-TOKEN');
                if (newCsrfToken) {
                    document.querySelector('input[name="<?= csrf_token() ?>"]').value = newCsrfToken;
                }
                if (!response.ok) {
                    return response.json().then(err => { throw err; }).catch(() => {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    alert('Folder berhasil dibuat!');
                    modalCreateFolder.classList.add('hidden');
                    createFolderForm.reset();
                    sharedOptionsDiv.classList.add('hidden');
                    location.reload();
                } else {
                    let errorMessage = 'Gagal membuat folder: ' + (data.message || 'Terjadi kesalahan.');
                    if (data.errors) {
                        for (const key in data.errors) {
                            errorMessage += `\n- ${data.errors[key]}`;
                        }
                    }
                    alert(errorMessage);
                }
            })
            .catch(error => {
                console.error('Error saat membuat folder:', error);
                alert('Terjadi kesalahan saat berkomunikasi dengan server. Silakan cek konsol developer untuk detail lebih lanjut.');
            });
        });
    }
});
