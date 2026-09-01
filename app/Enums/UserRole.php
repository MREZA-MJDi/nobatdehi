<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case BARBER = 'barber';
    case CUSTOMER = 'customer';
}
