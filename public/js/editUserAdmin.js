document.addEventListener('DOMContentLoaded', function() {
        const modalEditUser = document.getElementById('modalEditUser');
        const editUserForm = document.getElementById('editUserForm');
        const editUserId = document.getElementById('editUserId');
        const editNamaLengkap = document.getElementById('editNamaLengkap');
        const editEmail = document.getElementById('editEmail');
        const editPassword = document.getElementById('editPassword');
        const editJabatan = document.getElementById('editJabatan');
        const editStatus = document.getElementById('editStatus');
        const cancelEditUserModal = document.getElementById('cancelEditUserModal');
        const togglePasswordVisibilityButtons = document.querySelectorAll('.toggle-password-visibility');

        const messageModal = document.getElementById('messageModal');
        const messageModalTitle = document.getElementById('messageModalTitle');
        const messageModalContent = document.getElementById('messageModalContent');
        const closeMessageModal = document.getElementById('closeMessageModal');

        // Fungsi untuk menampilkan modal pesan
        function showMessageModal(title, content) {
            messageModalTitle.textContent = title;
            messageModalContent.textContent = content;
            messageModal.classList.remove('hidden');
        }

        // Fungsi untuk menyembunyikan modal pesan
        function hideMessageModal() {
            messageModal.classList.add('hidden');
        }

        // Event listener untuk tombol OK di modal pesan
        closeMessageModal.addEventListener('click', hideMessageModal);

        // Event listener untuk tombol "Edit"
        document.querySelectorAll('.open-edit-user-modal').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.dataset.userId;
                // Fetch user data via AJAX
                fetch(`/admin/users/edit/${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const user = data.data;
                            const roles = data.roles;

                            // Isi form dengan data user
                            editUserId.value = user.id;
                            editNamaLengkap.value = user.name;
                            editEmail.value = user.email;
                            editPassword.value = ''; // Kosongkan password untuk keamanan
                            editStatus.value = user.is_active;

                            // Isi dropdown jabatan (roles)
                            editJabatan.innerHTML = ''; // Kosongkan opsi yang ada
                            roles.forEach(role => {
                                const option = document.createElement('option');
                                option.value = role.id;
                                option.textContent = role.name;
                                if (role.id == user.role_id) { // Bandingkan dengan role_id user
                                    option.selected = true;
                                }
                                editJabatan.appendChild(option);
                            });

                            modalEditUser.classList.remove('hidden');
                        } else {
                            showMessageModal('Error', data.message || 'Gagal mengambil data user.');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching user data:', error);
                        showMessageModal('Error', 'Terjadi kesalahan saat mengambil data user.');
                    });
            });
        });

        // Event listener untuk tombol "Batal" di modal edit
        cancelEditUserModal.addEventListener('click', function() {
            modalEditUser.classList.add('hidden');
        });

        // Event listener untuk toggle password visibility
        togglePasswordVisibilityButtons.forEach(button => {
            button.addEventListener('click', function() {
                const passwordInput = this.previousElementSibling;
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    this.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 .996-3.09 3.144-5.653 5.466-7.228M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>`; // Icon mata tertutup
                } else {
                    passwordInput.type = 'password';
                    this.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>`; // Icon mata terbuka
                }
            });
        });


        // Event listener untuk submit form edit user
        editUserForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Mencegah form submit secara default

            const formData = new FormData(this); // Mengambil semua data dari form

            fetch('/admin/users/update', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest' // Penting untuk CI4 isAJAX()
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showMessageModal('Sukses', data.message);
                        modalEditUser.classList.add('hidden');
                        // Opsional: Refresh halaman atau perbarui baris tabel secara dinamis
                        setTimeout(() => {
                        location.reload();
                    }, 1500);// Cara sederhana untuk refresh data
                    } else {
                        // Tampilkan pesan error validasi atau error lainnya
                        let errorMessage = data.message || 'Terjadi kesalahan saat menyimpan data.';
                        if (data.errors) {
                            errorMessage += '\n<ul>';
                            for (const field in data.errors) {
                                errorMessage += `<li>${data.errors[field]}</li>`;
                            }
                            errorMessage += '</ul>';
                        }
                        showMessageModal('Error', errorMessage);
                    }
                })
                .catch(error => {
                    console.error('Error updating user:', error);
                    showMessageModal('Error', 'Terjadi kesalahan jaringan atau server.');
                });
        });
    });

    