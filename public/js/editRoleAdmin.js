document.addEventListener('DOMContentLoaded', function() {
        const modalEditJabatan = document.getElementById('modalEditJabatan');
        const editJabatanForm = document.getElementById('editJabatanForm');
        const editJabatanId = document.getElementById('editJabatanId');
        const editNamaJabatan = document.getElementById('editNamaJabatan');
        const editLevel = document.getElementById('editLevel');
        const editMaxStorage = document.getElementById('editMaxStorage');
        const cancelEditJabatanModal = document.getElementById('cancelEditJabatanModal');

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

        // Event listener untuk tombol "Edit" di tabel jabatan
        document.querySelectorAll('.open-edit-jabatan-modal').forEach(button => {
            button.addEventListener('click', function() {
                const jabatanId = this.dataset.jabatanId;

                // Fetch role data via AJAX
                fetch(`/admin/jabatan/edit/${jabatanId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const role = data.data;

                            // Isi form dengan data jabatan
                            editJabatanId.value = role.id;
                            editNamaJabatan.value = role.name;
                            editLevel.value = role.level;
                            editMaxStorage.value = role.max_upload_size_mb;

                            modalEditJabatan.classList.remove('hidden');
                        } else {
                            showMessageModal('Error', data.message || 'Gagal mengambil data jabatan.');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching role data:', error);
                        showMessageModal('Error', 'Terjadi kesalahan saat mengambil data jabatan.');
                    });
            });
        });

        // Event listener untuk tombol "Batal" di modal edit jabatan
        cancelEditJabatanModal.addEventListener('click', function() {
            modalEditJabatan.classList.add('hidden');
        });

        // Event listener untuk submit form edit jabatan
        editJabatanForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Mencegah form submit secara default

            const formData = new FormData(this); // Mengambil semua data dari form

            fetch('/admin/jabatan/update', {
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
                        modalEditJabatan.classList.add('hidden');
                        // Opsional: Refresh halaman atau perbarui baris tabel secara dinamis
                        location.reload(); // Cara sederhana untuk refresh data
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
                    console.error('Error updating role:', error);
                    showMessageModal('Error', 'Terjadi kesalahan jaringan atau server.');
                });
        });
    });