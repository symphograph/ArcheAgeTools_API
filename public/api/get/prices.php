<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Item\Price;
use App\Transfer\User\PriceTransfer;
use App\User\{AccSettings, Member, Server};
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AccountErr;

$AccSets = AccSettings::byJwt();

$ServerGroup = Server::getGroupId(intval($_POST['serverId'] ?? 0))
    or throw new AccountErr('ServerGroup is empty', 'Сервер не указан', 400);

$memberId = intval($_POST['accId'] ?? 0) ?: $AccSets->accountId;

$priceMember = new Member();
$priceMember->accountId = $memberId;
$priceMember->initAccData();
$priceMember->initIsFollow();

if(!empty($priceMember->oldId)){
    PriceTransfer::byArr($priceMember->oldId, $priceMember->accountId);
}

$List = Price::memberPriceList($memberId, $ServerGroup);

Response::data(['Prices' => $List, 'priceMember' => $priceMember]);