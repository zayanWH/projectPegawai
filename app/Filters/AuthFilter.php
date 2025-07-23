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
        
        // Check if the user is logged in
        if (!$session->get('isLoggedIn')) {
            log_message('info', 'AuthFilter: User not logged in. Redirecting to /login');
            return redirect()->to(base_url('login'));
        }

        // If the user is logged in, check their role
        $userRoleId = $session->get('role_id');
        $allowedRoles = $arguments; // Arguments will contain the allowed role_ids (e.g., [1, 2])

        log_message('debug', 'AuthFilter: User is logged in. Current role_id: ' . var_export($userRoleId, true));
        log_message('debug', 'AuthFilter: Allowed roles for this route: ' . var_export($allowedRoles, true));

        // If no role arguments are provided (e.g., only login is required), just proceed
        if (empty($allowedRoles)) {
            log_message('info', 'AuthFilter: No specific roles required, proceeding (user is logged in).');
            return null;
        }

        // Ensure $allowedRoles is an array and convert all arguments to integers for correct comparison
        // The $arguments might come as an array of strings, so convert them to integers.
        $allowedRoles = array_map('intval', (array) $allowedRoles); 
        
        // Convert userRoleId to an integer as well for strict comparison
        if (!in_array((int)$userRoleId, $allowedRoles)) { 
            log_message('warning', 'AuthFilter: User role_id ' . $userRoleId . ' is not in allowed roles [' . implode(', ', $allowedRoles) . ']. Redirecting to /akses-ditolak');
            // If the role is not allowed, redirect to the "Access Denied" page
            return redirect()->to(base_url('akses-ditolak')); 
        }
        
        log_message('info', 'AuthFilter: User with role_id ' . $userRoleId . ' is allowed. Proceeding.');
        return null; // Filter passes, allow access
    }
    
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Empty
    }
}