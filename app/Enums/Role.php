<?php

namespace App\Enums;

enum Role: string
{
    case Student = 'student';
    case Faculty = 'faculty';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Student => 'Student',
            self::Faculty => 'Faculty',
            self::Admin => 'Admin',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Student => 'bg-amber-100 text-amber-800 ring-amber-600/20',
            self::Faculty => 'bg-sky-100 text-sky-800 ring-sky-600/20',
            self::Admin => 'bg-rose-100 text-rose-800 ring-rose-600/20',
        };
    }

    /**
     * @return array<int, self>
     */
    public static function selectable(): array
    {
        return [self::Student, self::Faculty, self::Admin];
    }
}
