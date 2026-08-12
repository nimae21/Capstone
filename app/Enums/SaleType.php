<?php

namespace App\Enums;

enum SaleType: string
{
    case Online = 'online';
    case Pos = 'pos';

    public function label(): string
    {
        return match($this) {
            self::Online => 'Online Order',
            self::Pos => 'In-Store (POS)',
        };
    }
}