<?php

namespace App\Struct;

enum Role: string
{
    case ADMIN = 'ROLE_ADMIN';
    case USER = 'ROLE_USER';
    case USER_VERIFIED = 'ROLE_USER_VERIFIED';
}