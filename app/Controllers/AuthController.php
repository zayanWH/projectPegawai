<?php

    namespace App\Controllers;

    class AuthController extends BaseController
    {
        public function login()
        {
            return view('login');
        }
        
        public function processLogin()
        {
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');
            
            $db = \Config\Database::connect();
            $user = $db->table('users')
                        ->where('email', $email)
                        ->where('is_active', 1)
                        ->get()
                        ->getRowArray();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                $session = session();
                $session->set([
                    'isLoggedIn' => true,
                    'id' => $user['id'], // <--- UBAH DARI 'user_id' MENJADI 'id' DI SINI
                    'role_id' => $user['role_id']
                ]);
                
                if ($user['role_id'] == 1) {
                    return redirect()->to('/admin/dashboard');
                } else {
                    // Pastikan Anda juga memiliki rute dan controller yang menangani redirect ini
                    return redirect()->to('/dashboard-pegawai'); // Contoh: redirect ke dashboard umum/manajer
                }
                
            } else {
                return redirect()->back()->with('error', 'Email atau password salah');
            }
        }
        
        public function logout()
        {
            session()->destroy();
            return redirect()->to('/login');
        }
    }