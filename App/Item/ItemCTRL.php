<?php

namespace App\Item;

use App\Craft\Craft\CraftList;
use App\Craft\UCraft\UCraft;
use App\Item\Errors\NoCraftableErr;
use App\Item\Errors\PersonalErr;
use App\User\AccSets;
use App\User\User;
use JetBrains\PhpStorm\NoReturn;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\HTTP\Request;
use Symphograph\Bicycle\PDO\DB;

class ItemCTRL
{
    public static function get(): void
    {
        User::auth();
        Request::checkEmpty(['itemId']);

        $Item = Item::byId($_POST['itemId'])
        or throw new AppErr("item {$_POST['id']} does not exist in DB",'Предмет не найден');

        $Item->initData();
        $Item->initInfo();
        $Item->Info->initCategory($Item->categId);
        $Item->initPricing();
        $Item->Pricing->Price->initItemProps();
        $Item->initIsBuyOnly();

        Response::data($Item);
    }

    public static function addToBuyable(): void
    {
        User::auth();
        Request::checkEmpty(['itemId']);
        $Item = Item::byId($_POST['itemId'])->initData();

        if(!$Item->craftable) {
            throw new NoCraftableErr($Item->id);
        }
        if($Item->personal) {
            throw new PersonalErr($Item->id);
        }

        $accountId = AccSets::$current->accountId;
        $sql = "replace into uacc_buyOnly (accountId, itemId) VALUES (:accountId, :itemId)";
        $params = ['accountId' => $accountId, 'itemId' => $Item->id];
        DB::qwe($sql, $params);
        UCraft::clearAllCrafts();

        Response::success();
    }

    #[NoReturn] public static function delFromBuyable(): void
    {
        User::auth();
        Request::checkEmpty(['itemId']);

        $accountId = AccSets::$current->accountId;
        $sql = "delete from uacc_buyOnly where accountId = :accountId and itemId = :itemId";
        DB::qwe($sql, ['accountId' => $accountId, 'itemId' => $_POST['itemId']]);
        UCraft::clearAllCrafts();
        Response::success();
    }

    #[NoReturn] public static function getResultItemIds(): void
    {
        User::auth();
        Request::checkEmpty(['itemId']);

        $ids = CraftList::getResultItemIds($_POST['itemId']);

        Response::data($ids);
    }
}