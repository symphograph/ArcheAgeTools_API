<?php

namespace App\Category;

use App\Item\Category;
use App\User\User;
use JetBrains\PhpStorm\NoReturn;
use Symphograph\Bicycle\Api\Response;

class CategoryCTRL
{

    public static function get()
    {

    }

    #[NoReturn] public static function getListAsTree(): void
    {
        User::auth();
        $tree = Category::getTree();

        Response::data($tree);
    }
}