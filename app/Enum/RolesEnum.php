<?php

namespace App\Enum;

enum RolesEnum: string
{
    case SUPER = 'Super';

    case ADMIN = 'Admin';

    case USER = 'Usuário';

    case GUEST = 'Visitante';


    public static function getLabel(string $name): ?string
    {
        return self::tryFromName($name)?->value;
    }

    private static function tryFromName(string $name): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }

        return null;
    }
}
