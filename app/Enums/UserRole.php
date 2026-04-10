<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Finance = 'finance';
    case Warehouse = 'warehouse';
    case Hr = 'hr';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Finance => 'Finance',
            self::Warehouse => 'Warehouse',
            self::Hr => 'Human Resource',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
