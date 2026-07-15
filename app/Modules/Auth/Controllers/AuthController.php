<?php

namespace Modules\Auth\Controllers;

use App\Controllers\BaseController;
use Modules\Auth\Models\AdminUserModel;
use Modules\Authorization\Application\AuthorizationService;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('admin_logged_in')) {
            return redirect()->to('/admin');
        }

        return view('Modules\Auth\Views\login', [
            'title' => 'Iniciar sesión',
        ]);
    }

    public function attemptLogin()
    {
        $email = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');

        $user = (new AdminUserModel())
            ->where('email', $email)
            ->where('status', 'active')
            ->first();

        if (! $user || ! password_verify($password, $user['password'])) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Correo o contraseña incorrectos.');
        }

        session()->regenerate();

        session()->set([
            'admin_logged_in' => true,
            'admin_user_id' => $user['id'],
            'admin_user_name' => $user['name'],
            'admin_user_email' => $user['email'],
            'admin_user_role' => $user['role'],
        ]);

        (new AuthorizationService())->warmSession((int) $user['id']);

        (new AdminUserModel())->update($user['id'], [
            'last_login_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin');
    }

    public function logout()
    {
        (new AuthorizationService())->clearSessionCache();

        session()->remove([
            'admin_logged_in',
            'admin_user_id',
            'admin_user_name',
            'admin_user_email',
            'admin_user_role',
        ]);

        return redirect()->to('/admin/login');
    }
}
