<?php

declare(strict_types=1);

namespace App\Core;

class Validator
{
    public static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public static function required(?string $value): bool
    {
        return $value !== null && trim($value) !== '';
    }
}
