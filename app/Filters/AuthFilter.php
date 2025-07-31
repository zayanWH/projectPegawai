<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // 1. Periksa apakah pengguna sudah login
        if (!$session->get('isLoggedIn')) {
            log_message('info', 'AuthFilter: User not logged in. Redirecting to /login');
            return redirect()->to(base_url('login'));
        }

        // Jika pengguna sudah login, dapatkan ID perannya
        $userRoleId = (int)$session->get('role_id'); // Pastikan ini adalah integer

        log_message('debug', 'AuthFilter: User is logged in. Current role_id: ' . var_export($userRoleId, true));

        // --- Logika akses mutlak untuk peran istimewa ---
        // Daftar role_id yang memiliki akses mutlak (bisa mengakses semua halaman yang terfilter)
        // SESUAIKAN ID INI DENGAN ID PERAN NYATA DI DATABASE ANDA!
        // Contoh: Admin, HRD, Direksi, Manajer mungkin memiliki akses mutlak
        $absoluteAccessRoles = [
            1, // Admin
            2, // HRD
            3, // Direksi
            4, // Manajer
            // Catatan: Supervisor (ID 5) TIDAK ada di sini,
            // sehingga aksesnya akan diperiksa per rute.
        ];

        // Jika role_id pengguna ada dalam daftar absoluteAccessRoles, abaikan semua pemeriksaan peran lainnya
        if (in_array($userRoleId, $absoluteAccessRoles)) {
            log_message('info', 'AuthFilter: User with role_id ' . $userRoleId . ' has absolute access. Proceeding.');
            return null; // Izinkan akses tanpa pemeriksaan lebih lanjut
        }
        // --- Akhir logika akses mutlak ---

        // $arguments akan berisi daftar role_id yang diizinkan untuk rute ini, 
        // yang datang dari konfigurasi Routes.php (contoh: ['filter' => 'auth:1,2'])
        $allowedRoles = $arguments; 

        log_message('debug', 'AuthFilter: Allowed roles for this route: ' . var_export($allowedRoles, true));

        // Jika tidak ada argumen peran yang diberikan (misalnya, hanya login yang diperlukan), lanjutkan saja
        if (empty($allowedRoles)) {
            log_message('info', 'AuthFilter: No specific roles required, proceeding (user is logged in).');
            return null;
        }

        // Pastikan $allowedRoles adalah array dan konversi semua argumen ke integer 
        // untuk perbandingan yang benar, karena $arguments mungkin datang sebagai array string.
        $allowedRoles = array_map('intval', (array) $allowedRoles); 
        
        // Konversi $userRoleId ke integer juga untuk perbandingan yang ketat
        if (!in_array($userRoleId, $allowedRoles)) { 
            log_message('warning', 'AuthFilter: User role_id ' . $userRoleId . ' is not in allowed roles [' . implode(', ', $allowedRoles) . ']. Redirecting to /akses-ditolak');
            // Jika peran tidak diizinkan, arahkan ke halaman "Akses Ditolak"
            return redirect()->to(base_url('akses-ditolak')); 
        }
        
        log_message('info', 'AuthFilter: User with role_id ' . $userRoleId . ' is allowed. Proceeding.');
        return null; // Filter lolos, izinkan akses
    }
    
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Metode ini kosong dan tidak melakukan apa-apa setelah request selesai
    }
}