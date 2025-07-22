<?php

namespace App\Controllers;


class Login extends BaseController 
{
    public function index()
    {
        return view('auth/loginPage');
    }
    
    public function proses()
    {
        log_message('info', 'Login proses method called');
        
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        log_message('info', 'Email: ' . $email);
        log_message('info', 'Password length: ' . strlen($password));

        if (empty($email) || empty($password)) {
            log_message('info', 'Empty email or password');
            return redirect()->back()->with('error', 'Email dan password harus diisi');
        }
        $db = \Config\Database::connect();
        $user = $db->table('users')
                    ->where('email', $email)
                    ->where('is_active', 1)
                    ->get()
                    ->getRowArray();
        log_message('info', 'User found: ' . ($user ? 'YES' : 'NO'));
        if ($user) {
            log_message('info', 'User role_id: ' . $user['role_id']);
        }

        if ($user && password_verify($password, $user['password_hash'])) {
            log_message('info', 'Password verified successfully');

            $roleName = $db->table('roles') 
                ->where('id', $user['role_id'])
                ->get()
                ->getRow('name');

            $session = session();
            $session->set([
                'isLoggedIn' => true,
                'user_id' => $user['id'],
                'role_id' => $user['role_id'],
                'role' => strtolower($roleName), 
                'user_name' => $user['name'],
            ]);

            
            log_message('info', 'Session set for user_id: ' . $user['id']);

            switch ($user['role_id']) {
                case 1: 
                    log_message('info', 'Redirecting to admin dashboard');
                    return redirect()->to('/admin/dashboard');
                case 2: 
                    log_message('info', 'Redirecting to hrd dashboard');
                    return redirect()->to('/hrd/dashboard');
                case 3: 
                    log_message('info', 'Redirecting to direksi dashboard');
                    return redirect()->to('/direksi/dashboard'); 
                case 4: 
                    log_message('info', 'Redirecting to manager dashboard');
                    return redirect()->to('/manager/dashboard');
                case 5: 
                    log_message('info', 'Redirecting to supervisor dashboard');
                    return redirect()->to('/supervisor/dashboard'); 
                case 6: 
                    log_message('info', 'Redirecting to staff dashboard');
                    return redirect()->to('/staff/dashboard');
                default:
                    log_message('warning', 'Unknown role_id: ' . $user['role_id'] . '. Redirecting to login.');
                    return redirect()->back()->with('error', 'Role pengguna tidak dikenali.');
            }
            
        } else {
            if (!$user) {
                log_message('info', 'Login failed: User not found for email: ' . $email);
            } else {
                log_message('info', 'Login failed: Password incorrect for email: ' . $email);
            }
            
            return redirect()->back()->with('error', 'Email atau password salah');
        }
    }
    
    public function logout()
    {
        session()->destroy();
        log_message('info', 'User logged out. Session destroyed.');
        return redirect()->to('/login');
    }
}