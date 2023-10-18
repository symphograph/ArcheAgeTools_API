<?php

namespace App\CTRL;

use App\Item\Item;
use App\User\AccSettings;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Errors\ValidationErr;

class ItemCTRL
{



    public static function get(): void
    {
        AccSettings::byJwt();
        $id = intval($_POST['id'] ?? 0)
        or throw new ValidationErr('invalid id');

        $Item = Item::byId($id)
        or throw new AppErr("item $id does not exist in DB",'Предмет не найден');

        $Item->initInfo();
        $Item->Info->initCategory($Item->categId);
        $Item->initPricing();
        $Item->Pricing->Price->initItemProps();
        $Item->initIsBuyOnly();

        Response::data($Item);
    }
}