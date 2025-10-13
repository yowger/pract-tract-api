<?php

namespace App\Enums;

enum StudentStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Completed = 'completed';
    case Inactive = 'inactive';
}
