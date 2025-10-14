<?php

namespace App\Enums;

enum UserStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Inactive = 'inactive';
}
