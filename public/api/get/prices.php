<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Errors\MyErrors;
use App\Item\Price;
use App\Transfer\PriceTransfer;
use App\User\{Account, Member, Server};

$Account = Account::byToken();

$ServerGroup = Server::getGroup(intval($_POST['serverId'] ?? 0))
    or Api::errorResponse('Сервер не указан', 400);

$memberId = intval($_POST['accId'] ?? 0) ?: $Account->id;

try {
    $priceMember = new Member();
    $priceMember->accountId = $memberId;
    $priceMember->initAccData();
    $priceMember->initIsFollow();

    if(!empty($priceMember->oldId)){
        PriceTransfer::importPrices($priceMember->oldId, $priceMember->accountId);
    }

    $List = Price::memberPriceList($memberId, $ServerGroup);
} catch (MyErrors $err) {
    Api::errorResponse($err->getResponseMsg());
}

Api::dataResponse(['Prices' => $List, 'priceMember' => $priceMember]);