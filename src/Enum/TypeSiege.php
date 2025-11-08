<?php

namespace App\Enum;

enum TypeSiege: string
{
    case NORMAL = 'standard';
    case VIP = 'vip';
    case OCCUPE = 'occupe';
    case HANDICAPE = 'handicape';
}
