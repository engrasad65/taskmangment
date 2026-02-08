<?php

declare(strict_types=1);

namespace App\Core;

class Permission
{
    public static function can(?string $role, string $permission, array $config): bool
    {
        if ($role === null) {
            return false;
        }

        $rolePermissions = $config['app']['role_permissions'][$role] ?? [];
        return in_array($permission, $rolePermissions, true);
    }
}
