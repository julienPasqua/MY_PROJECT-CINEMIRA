<?php

namespace App\Enum;

enum ReservationStatus: string
{
    case EN_ATTENTE = 'en_attente';
    case CONFIRMEE = 'confirmee';
    case ANNULEE = 'annulee';
    case EXPIREE = 'expiree';
    case REMBOURSEE = 'remboursee';
}


