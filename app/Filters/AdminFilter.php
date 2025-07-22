<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
// use CodeIgniter\Log\Logger; // Anda mungkin tidak perlu mengimpor ini secara eksplisit jika fungsi log_message sudah global

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // --- LOGGING UNTUK DEBUGGING ---
        // Ini akan muncul di writable/logs/log-YYYY-MM-DD.php Anda
        log_message('debug', 'AdminFilter activated for URI: ' . current_url());
        log_message('debug', 'AdminFilter - Session isLoggedIn: ' . var_export($session->get('isLoggedIn'), true));
        log_message('debug', 'AdminFilter - Session role_id: ' . var_export($session->get('role_id'), true));
        // --- END LOGGING ---

        // 1. Cek apakah user sudah login
        if (!$session->get('isLoggedIn')) {
            log_message('debug', 'AdminFilter: User not logged in. Redirecting to /login');
            // Jika belum login, kembalikan ke halaman login
            return redirect()->to(base_url('login'));
        }
        
        // 2. Cek apakah role_id user adalah 1 (Admin)
        // Menggunakan operator '===' (identik) untuk memastikan tipe data juga sama
        if ($session->get('role_id') != 1) {
            log_message('debug', 'AdminFilter: User is logged in but not Admin (role_id: ' . $session->get('role_id') . '). Redirecting to /akses-ditolak');
            // Jika sudah login tapi bukan admin, arahkan ke halaman "Akses Ditolak"
            return redirect()->to(base_url('akses-ditolak')); 
        }
        
        // Jika kedua cek berhasil (user sudah login dan adalah admin)
        log_message('debug', 'AdminFilter: User is Admin. Proceeding to admin dashboard.');
        return null; // Penting: Mengembalikan null menandakan bahwa filter berhasil dilewati
    }
    
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada yang perlu dilakukan setelah request selesai di filter ini
    }
}