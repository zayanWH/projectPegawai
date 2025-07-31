document.addEventListener('DOMContentLoaded', function() {
    const modalAddJabatan = document.getElementById('modalAddJabatan');
    const openAddJabatanModalBtn = document.getElementById('openAddJabatanModalBtn');
    const cancelAddJabatanModal = document.getElementById('cancelAddJabatanModal');
    const addJabatanForm = document.getElementById('addJabatanForm');

    // Fungsi untuk menampilkan modal
    if (openAddJabatanModalBtn) {
        openAddJabatanModalBtn.addEventListener('click', function() {
            modalAddJabatan.classList.remove('hidden');
            // Reset form saat modal dibuka
            addJabatanForm.reset();
            // Sembunyikan pesan error sebelumnya
            hideAddFormErrors();
        });
    }

    // Fungsi untuk menyembunyikan modal
    if (cancelAddJabatanModal) {
        cancelAddJabatanModal.addEventListener('click', function() {
            modalAddJabatan.classList.add('hidden');
        });
    }

    // Fungsi untuk menampilkan error validasi
    function showAddFormErrors(errors) {
        // Sembunyikan semua error sebelumnya
        hideAddFormErrors();

        if (errors.name) {
            document.getElementById('errorAddNamaJabatan').innerText = errors.name;
            document.getElementById('errorAddNamaJabatan').classList.remove('hidden');
        }
        if (errors.level) {
            document.getElementById('errorAddLevel').innerText = errors.level;
            document.getElementById('errorAddLevel').classList.remove('hidden');
        }
        if (errors.max_upload_size_mb) {
            document.getElementById('errorAddMaxStorage').innerText = errors.max_upload_size_mb;
            document.getElementById('errorAddMaxStorage').classList.remove('hidden');
        }
    }

    // Fungsi untuk menyembunyikan semua error validasi pada form tambah
    function hideAddFormErrors() {
        document.getElementById('errorAddNamaJabatan').classList.add('hidden');
        document.getElementById('errorAddLevel').classList.add('hidden');
        document.getElementById('errorAddMaxStorage').classList.add('hidden');
    }

    // Handle submit form tambah jabatan
    if (addJabatanForm) {
        addJabatanForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            try {
                const response = await fetch(`${window.location.origin}/admin/jabatan/add`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.status === 'success') {
                    alert(data.message);
                    modalAddJabatan.classList.add('hidden');
                    window.location.reload();
                } else {
                    if (data.errors) {
                        showAddFormErrors(data.errors);
                    } else {
                        alert('Error: ' + (data.message || 'Terjadi kesalahan tidak dikenal.'));
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan jaringan atau server tidak merespons.');
            }
        });
    }
});