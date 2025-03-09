<?php

namespace App\Enum;

enum RoleEnum: string
{
    case USER = 'ROLE_USER';
    case ADMIN = 'ROLE_ADMIN';

    public function getLabel(): string
    {
        return match($this) {
            self::USER => '👤 Utilisateur',
            self::ADMIN => '🔧 Administrateur',
        };
    }
}
