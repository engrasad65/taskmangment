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

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!Validator::required($email) || !Validator::required($password)) {
            $this->showLogin('Email and password are required.');
            return;
        }

        $user = $userModel->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->showLogin('Invalid credentials.');
            return;
        }

        Session::regenerate();
        Session::set('user', [
            'id' => (int) $user['id'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'role' => $user['role'],
        ]);

        $this->redirect($user['role'] === 'admin' ? '/admin/dashboard' : '/user/dashboard');
    }

    public function logout(): void
    {
        Session::destroy();
        $this->redirect('/');
    }
}
