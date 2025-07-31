// File: public/js/searchUserAdmin.js

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchUser');
    const tableBody = document.querySelector('tbody');
    let searchTimeout;

    // Event listener untuk input search
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim();
        
        // Clear timeout sebelumnya untuk menghindari terlalu banyak request
        clearTimeout(searchTimeout);
        
        // Set timeout untuk delay pencarian (300ms)
        searchTimeout = setTimeout(() => {
            searchUsers(searchTerm);
        }, 300);
    });

    /**
     * Fungsi untuk melakukan pencarian user via AJAX
     * @param {string} searchTerm - Kata kunci pencarian
     */
    function searchUsers(searchTerm) {
        // Tampilkan loading state
        showLoading();

        // Gunakan URL relatif atau ambil dari window.location
        const baseUrl = window.location.origin + '/';
        const url = `${baseUrl}admin/search-users?search=${encodeURIComponent(searchTerm)}`;
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                updateTable(data.data);
            } else {
                console.error('Search error:', data.message);
                showError('Gagal melakukan pencarian');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            showError('Terjadi kesalahan saat melakukan pencarian');
        });
    }

    /**
     * Update tabel dengan data hasil pencarian
     * @param {Array} users - Data user hasil pencarian
     */
    function updateTable(users) {
        let html = '';
        
        if (users && users.length > 0) {
            users.forEach((user, index) => {
                html += `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${index + 1}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(user.name)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(user.email)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(user.role_name)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${user.is_active == 1 ? 'Aktif' : 'Non-aktif'}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" class="inline-flex items-center text-yellow-500 hover:text-yellow-700 mr-3 open-edit-user-modal" data-user-id="${user.id}">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M17.414 2.586a2 2 0 00-2.828 0l-1.793 1.793 2.828 2.828 1.793-1.793a2 2 0 000-2.828zM2 13.586V17h3.414l9.793-9.793-2.828-2.828L2 13.586z"></path>
                                </svg>
                            </button>
                            <button type="button" class="inline-flex items-center text-red-500 hover:text-red-700 open-delete-user-modal" data-user-id="${user.id}">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 011 1v1h2a1 1 0 110 2H8V3a1 1 0 011-1zm0 6a1 1 0 011 1v6a1 1 0 11-2 0V9a1 1 0 011-1zm6-3a1 1 0 011 1v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7a1 1 0 011-1h10z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </td>
                    </tr>
                `;
            });
        } else {
            html = `
                <tr>
                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        ${searchInput.value.trim() ? 'Tidak ada user yang ditemukan dengan kata kunci "' + escapeHtml(searchInput.value.trim()) + '"' : 'Tidak ada data user.'}
                    </td>
                </tr>
            `;
        }
        
        tableBody.innerHTML = html;
    }

    /**
     * Tampilkan loading state
     */
    function showLoading() {
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                    <div class="flex justify-center items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Mencari...
                    </div>
                </td>
            </tr>
        `;
    }

    /**
     * Tampilkan pesan error
     * @param {string} message - Pesan error
     */
    function showError(message) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-red-500 text-center">
                    ${escapeHtml(message)}
                </td>
            </tr>
        `;
    }

    /**
     * Escape HTML untuk mencegah XSS
     * @param {string} unsafe - String yang belum di-escape
     * @return {string} String yang sudah di-escape
     */
    function escapeHtml(unsafe) {
        if (typeof unsafe !== 'string') return unsafe;
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});