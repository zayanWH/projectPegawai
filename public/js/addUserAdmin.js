document.addEventListener('DOMContentLoaded', function() {
    const modalAddUser = document.getElementById('modalAddUser');
    const openAddUserModalBtn = document.getElementById('openAddUserModal');
    const cancelAddUserModalBtn = document.getElementById('cancelAddUserModal');
    const addUserForm = document.getElementById('addUserForm');
    const alertAddUser = document.getElementById('alertAddUser');
    const submitBtn = document.getElementById('submitAddUser');
    const addJabatanSelect = document.getElementById('addJabatan');

    // Fungsi untuk menampilkan/menyembunyikan modal
    function showModal() {
        modalAddUser.classList.remove('hidden');
    }

    function hideModal() {
        modalAddUser.classList.add('hidden');
        resetForm();
        hideAlert();
    }

    // Fungsi untuk reset form
    function resetForm() {
        addUserForm.reset();
        hideErrors();
    }

    // Fungsi untuk menyembunyikan error messages
    function hideErrors() {
        const errorElements = ['errorName', 'errorEmail', 'errorPassword', 'errorRoleId', 'errorIsActive'];
        errorElements.forEach(id => {
            const element = document.getElementById(id);
            element.textContent = '';
            element.classList.add('hidden');
        });
    }

    // Fungsi untuk menampilkan error messages
    function showErrors(errors) {
        hideErrors();
        Object.keys(errors).forEach(field => {
            const errorElement = document.getElementById(`error${field.charAt(0).toUpperCase() + field.slice(1).replace('_', '')}`);
            if (errorElement) {
                errorElement.textContent = errors[field];
                errorElement.classList.remove('hidden');
            }
        });
    }

    // Fungsi untuk menampilkan alert
    function showAlert(message, type = 'error') {
        alertAddUser.className = `mb-4 p-3 rounded-lg text-sm ${type === 'success' ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300'}`;
        alertAddUser.textContent = message;
        alertAddUser.classList.remove('hidden');
    }

    // Fungsi untuk menyembunyikan alert
    function hideAlert() {
        alertAddUser.classList.add('hidden');
    }

    // Fungsi untuk mengubah state tombol submit
    function setSubmitButtonState(loading = false) {
        const submitText = submitBtn.querySelector('.submit-text');
        const submitLoading = submitBtn.querySelector('.submit-loading');
        
        if (loading) {
            submitText.classList.add('hidden');
            submitLoading.classList.remove('hidden');
            submitBtn.disabled = true;
        } else {
            submitText.classList.remove('hidden');
            submitLoading.classList.add('hidden');
            submitBtn.disabled = false;
        }
    }

    // Fungsi untuk load roles dari server
    async function loadRoles() {
        try {
            const response = await fetch(`${window.location.origin}/admin/roles`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();
            
            if (result.status === 'success') {
                // Clear existing options except the first one
                addJabatanSelect.innerHTML = '<option value="" disabled selected>Pilih jabatan</option>';
                
                // Add roles to select
                result.data.forEach(role => {
                    const option = document.createElement('option');
                    option.value = role.id;
                    option.textContent = role.name;
                    addJabatanSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading roles:', error);
        }
    }

    // Event listeners
    openAddUserModalBtn.addEventListener('click', function() {
        showModal();
        loadRoles(); // Load roles saat modal dibuka
    });

    cancelAddUserModalBtn.addEventListener('click', hideModal);

    // Close modal jika klik di luar modal
    modalAddUser.addEventListener('click', function(e) {
        if (e.target === modalAddUser) {
            hideModal();
        }
    });

    // Toggle password visibility
    document.addEventListener('click', function(e) {
        if (e.target.closest('.toggle-password-visibility')) {
            const button = e.target.closest('.toggle-password-visibility');
            const input = button.parentNode.querySelector('input');
            
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        }
    });

    // Handle form submission
    addUserForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        hideAlert();
        hideErrors();
        setSubmitButtonState(true);

        const formData = new FormData(addUserForm);

        try {
            const response = await fetch(`${window.location.origin}/admin/users/add`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const result = await response.json();

            if (result.status === 'success') {
                showAlert(result.message, 'success');
                setTimeout(() => {
                    hideModal();
                    // Reload halaman untuk menampilkan data terbaru
                    window.location.reload();
                }, 1500);
            } else {
                if (result.errors) {
                    showErrors(result.errors);
                } else {
                    showAlert(result.message || 'Terjadi kesalahan saat menyimpan data.');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Terjadi kesalahan jaringan. Silakan coba lagi.');
        } finally {
            setSubmitButtonState(false);
        }
    });
});