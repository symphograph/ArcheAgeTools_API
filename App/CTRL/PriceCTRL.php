<?php

namespace App\CTRL;



use App\Craft\AccountCraft;
use App\Item\Price;
use App\PriceHistory;
use App\Transfer\User\PriceTransfer;
use App\User\AccSettings;
use App\User\Member;
use JetBrains\PhpStorm\NoReturn;
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

    public static function listOfMember(): void
    {
        $AccSets = AccSettings::byJwt();

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

    public static function del(): void
    {
        $AccSets = AccSettings::byJwt();
        $itemId = $_POST['itemId'] or throw new ValidationErr('itemId');

        Price::delFromDB($AccSets->accountId, $itemId, $AccSets->serverGroupId);
        AccountCraft::clearAllCrafts();

        Response::success();
    }

    #[NoReturn] public static function getBasedList(): void
    {
        $AccSets = AccSettings::byJwt();
        $List = Price::basedList();

        Response::data(['Prices'=>$List]);
    }

    public static function set(): void
    {
        $AccSets = AccSettings::byJwt();
        $itemId = intval($_POST['itemId'] ?? 0)
        or throw new ValidationErr('itemId');

        $price = preg_replace("/[^0-9]/", '', $_POST['price'] ?? 0);
        $price = intval($price);

        if (empty($AccSets->serverGroupId)) {
            throw new ValidationErr('Server is empty', 'Сервер не выбран');
        }

        $Price = Price::byInput($AccSets->accountId, $itemId, $AccSets->serverGroupId, $price);
        $Price->putToDB();

        AccountCraft::clearAllCrafts();

        Response::success();
    }
}