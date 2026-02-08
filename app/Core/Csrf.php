<?php

declare(strict_types=1);

namespace App\Core;

class Csrf
{
    public static function token(): string
    {
        if (!Session::has('_csrf_token')) {
            Session::set('_csrf_token', bin2hex(random_bytes(32)));
        }

        return (string) Session::get('_csrf_token');
    }

    public static function verify(?string $token): bool
    {
        $sessionToken = (string) Session::get('_csrf_token', '');
        return $token !== null && hash_equals($sessionToken, $token);
    }
}
