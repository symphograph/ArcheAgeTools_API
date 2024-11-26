<?php

namespace App\Price;



use App\Craft\UCraft\UCraft;
use App\Currency\Currency;
use App\Item\Item;
use App\Item\Pricing;
use App\PriceHistory;
use App\Transfer\User\PriceTransfer;
use App\User\AccSets;
use App\User\Member\Member;
use App\User\User;
use JetBrains\PhpStorm\NoReturn;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Errors\ValidationErr;
use Symphograph\Bicycle\HTTP\Request;

class PriceCTRL
{
    #[NoReturn] public static function history(): void
    {
        User::auth();
        Request::checkEmpty(['itemId', 'serverGroupId']);
        $List = PriceHistory::getList($_POST['itemId'], $_POST['serverGroupId']) ?? [];
        Response::data($List);
    }

    public static function listOfMember(): void
    {
        User::auth();
        $AccSets = AccSets::$current;

        $accountId = ($_POST['accountId'] ?? $AccSets->accountId) or throw new ValidationErr();
        $priceMember = new Member();
        $priceMember->accountId = $accountId;
        $priceMember->initAccData();
        $priceMember->initIsFollow();
        if(!empty($priceMember->oldId)){
            PriceTransfer::byId($priceMember->accountId, $priceMember->oldId);
        }
        $List = Price::memberPriceList($accountId, $AccSets->serverGroupId);
        Response::data(['Prices' => $List, 'priceMember' => $priceMember]);
    }

    #[NoReturn] public static function del(): void
    {
        User::auth();
        Request::checkEmpty(['itemId']);

        Price::delFromDB(AccSets::$current->accountId, $_POST['itemId'], AccSets::$current->serverGroupId);
        UCraft::clearAllCrafts();

        Response::success();
    }

    #[NoReturn] public static function getBasedList(): void
    {
        User::auth();
        $List = Price::basedList();

        Response::data(['Prices'=>$List]);
    }

    public static function set(): void
    {
        User::auth();
        $AccSets = AccSets::$current;
        $itemId = intval($_POST['itemId'] ?? 0)
        or throw new ValidationErr('itemId');

        $price = preg_replace("/[^0-9]/", '', $_POST['price'] ?? 0);
        $price = intval($price);

        if (empty($AccSets->serverGroupId)) {
            throw new ValidationErr('Server is empty', 'Сервер не выбран');
        }

        $Price = Price::byInput($AccSets->accountId, $itemId, $AccSets->serverGroupId, $price);
        $Price->putToDB();

        UCraft::clearAllCrafts();

        Response::success();
    }

    public static function getCurrency(): void
    {
        User::auth();
        Request::checkEmpty(['id']);

        $id = $_POST['id'];
        $Currency = Currency::byId($id)
            ?: throw new AppErr(
                "Currency $id does not exist in DB",
                'Валюта не найдена'
            );

        $Currency->initTradeableItems()
            ->initPrice()
            ->initMonetizationItems();

        Response::data($Currency);
    }

    #[NoReturn] public static function get(): void
    {
        User::auth();
        Request::checkEmpty(['itemId']);

        $item = Item::byId($_POST['itemId']);
        if(!Pricing::isGoldable($item)) throw new AppErr('It is not goldable');
        $price = Price::bySaved($_POST['itemId']);
        if($price) $price->initItemProps();
        if(!$price) $price = Price::createEmpty($item->id);

        Response::data($price);
    }
}