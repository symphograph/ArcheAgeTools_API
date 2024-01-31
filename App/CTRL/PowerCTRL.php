<?php

namespace App\CTRL;

use JetBrains\PhpStorm\NoReturn;
use Symphograph\Bicycle\Api\Response;

class PowerCTRL
{
    #[NoReturn] public static function get(): void
    {
        Response::data([]);
    }

    public static function put()
    {
    }
}