<?php

namespace App\CTRL;



use App\Item\Price;
use App\PriceHistory;
use App\Transfer\User\PriceTransfer;
use App\User\AccSettings;
use App\User\Member;
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
}