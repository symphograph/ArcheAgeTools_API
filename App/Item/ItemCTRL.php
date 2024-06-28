<?php

namespace App\Item;

use App\User\AccSets;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\HTTP\Request;

class ItemCTRL
{
    public static function get(): void
    {
        AccSets::byJwt();
        Request::checkEmpty(['id']);

        $Item = Item::byId($_POST['id'])
        or throw new AppErr("item {$_POST['id']} does not exist in DB",'Предмет не найден');
        $Item->initData();
        $Item->initInfo();
        $Item->Info->initCategory($Item->categId);
        $Item->initPricing();
        $Item->Pricing->Price->initItemProps();
        $Item->initIsBuyOnly();

        Response::data($Item);
    }
}