<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class AccountEmail
{
    public static function normalize(string $email): string
    {
        return Str::lower(trim($email));
    }

    public static function exists(string $email): bool
    {
        return User::whereRaw('LOWER(email) = ?', [self::normalize($email)])->exists();
    }

    public static function storedAddress(string $email): string
    {
        // Preserve legacy account casing without rewriting stored email addresses.
        return User::where('email', trim($email))->value('email')
            ?? User::whereRaw('LOWER(email) = ?', [self::normalize($email)])->orderBy('id')->value('email')
            ?? self::normalize($email);
    }

    public static function newAccountRules(): array
    {
        return ['required', 'email', 'max:255', function ($attribute, $value, $fail) {
            if (self::exists((string) $value)) {
                $fail('This email is already registered. Please log in.');
            }
        }];
    }
}
