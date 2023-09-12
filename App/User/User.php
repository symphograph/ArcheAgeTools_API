<?php

namespace App\User;

use Symphograph\Bicycle\DB;
use Symphograph\Bicycle\Token\Token;

class User
{
    public static function auth(): void
    {
        Token::validation($_SERVER['HTTP_ACCESSTOKEN']);
    }
}