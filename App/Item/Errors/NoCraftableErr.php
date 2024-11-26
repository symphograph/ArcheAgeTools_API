<?php

namespace App\Item\Errors;

use Symphograph\Bicycle\Errors\MyErrors;

class NoCraftableErr extends MyErrors
{
    public function __construct(int $itemId)
    {
        $msg = "Item $itemId must be craftable";
        parent::__construct($msg, "Предмет не крафтабельный");
    }
}