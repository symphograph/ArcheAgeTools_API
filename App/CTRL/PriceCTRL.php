<?php

namespace App\CTRL;



use App\PriceHistory;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\ValidationErr;

class PriceCTRL
{
    public static function history(): void
    {
        $itemId = $_POST['itemId'] ?? throw new ValidationErr();
        $List = PriceHistory::getList($itemId) ?? [];
        Response::data($List);
    }
}