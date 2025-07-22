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
                    'user_id' => $user['id'],
                    'role_id' => $user['role_id']
                ]);
                
                if ($user['role_id'] == 1) {
                    return redirect()->to('/admin/dashboard');
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