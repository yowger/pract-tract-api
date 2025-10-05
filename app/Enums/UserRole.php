<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Director = 'director';
    case Agent = 'agent';
    case Advisor = 'advisor';
    case Student = 'student';
}
