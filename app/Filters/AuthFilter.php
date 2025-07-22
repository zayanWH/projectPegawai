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
        
        // Cek apakah user sudah login
        if (!$session->get('isLoggedIn')) {
            log_message('info', 'AuthFilter: User not logged in. Redirecting to /login');
            return redirect()->to(base_url('login'));
        }

        // Jika user sudah login, cek role-nya
        $userRoleId = $session->get('role_id');
        $allowedRoles = $arguments; // Arguments akan berisi role_id yang diizinkan (misal: [1, 2])

        log_message('debug', 'AuthFilter: User is logged in. Current role_id: ' . var_export($userRoleId, true));
        log_message('debug', 'AuthFilter: Allowed roles for this route: ' . var_export($allowedRoles, true));

        // Jika tidak ada argumen role yang diberikan (misalnya, hanya perlu login), lewati saja
        if (empty($allowedRoles)) {
            log_message('info', 'AuthFilter: No specific roles required, proceeding (user is logged in).');
            return null;
        }

        // Periksa apakah role_id user ada di dalam daftar role yang diizinkan
        // Kita perlu memastikan $allowedRoles adalah array integer untuk perbandingan yang benar
        $allowedRoles = array_map('intval', $allowedRoles); // Konversi semua argumen ke integer
        
        if (!in_array((int)$userRoleId, $allowedRoles)) { // Konversi userRoleId ke integer juga
            log_message('warning', 'AuthFilter: User role_id ' . $userRoleId . ' is not in allowed roles ' . implode(', ', $allowedRoles) . '. Redirecting to /akses-ditolak');
            // Jika role tidak diizinkan, arahkan ke halaman "Akses Ditolak"
            return redirect()->to(base_url('akses-ditolak')); 
        }
        
        log_message('info', 'AuthFilter: User with role_id ' . $userRoleId . ' is allowed. Proceeding.');
        return null; // Filter lolos, izinkan akses
    }
    
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Kosong
    }
}