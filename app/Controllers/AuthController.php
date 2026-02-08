<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin(?string $error = null): void
    {
        $this->view('auth/login', ['error' => $error]);
    }

    public function login(User $userModel): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $this->showLogin('Invalid CSRF token.');
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!Validator::required($username) || !Validator::required($password)) {
            $this->showLogin('Username and password are required.');
            return;
        }

        $user = $userModel->findByUsername($username);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->showLogin('Invalid credentials.');
            return;
        }

        Session::regenerate();
        Session::set('user', [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'role' => $user['role'],
        ]);

        $this->redirect($user['role'] === 'admin' ? '/admin/dashboard' : '/worker/dashboard');
    }

    public function logout(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $this->redirect('/');
        }

        Session::destroy();
        $this->redirect('/');
    }
}
