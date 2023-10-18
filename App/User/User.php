<?php

namespace App\User;

use Symphograph\Bicycle\Env\Server\ServerEnv;
use Symphograph\Bicycle\Token\Token;

class User
{
    public static function auth(): void
    {
        Token::validation(ServerEnv::HTTP_ACCESSTOKEN());
    }
}