<?php

namespace App\Entity\Enum;

enum Print3DStatusEnum: string{
    case TODO = 'A faire';
    case IN_PROGRESS = 'En cours';
    case DONE = 'Fait';
}
