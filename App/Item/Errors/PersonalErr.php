<?php

namespace App\Item\Errors;

use Symphograph\Bicycle\Errors\MyErrors;

class PersonalErr extends MyErrors
{
    public function __construct(int $itemId)
    {
        $msg = "Item $itemId must Not be Personal in this context";
        parent::__construct($msg, "Предмет не должен быть персональным");
    }
}