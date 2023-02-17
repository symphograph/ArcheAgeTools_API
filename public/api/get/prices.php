<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Item\Price;
use App\Transfer\PriceTransfer;
use App\User\{Account, Member, Server};

$Account = Account::byToken();

$ServerGroup = Server::getGroup(intval($_POST['serverId'] ?? 0))
or die(Api::errorMsg('server not found'));

$memberId = intval($_POST['accId'] ?? 0) ?: $Account->id;

$priceMember = new Member();
$priceMember->accountId = $memberId;
$priceMember->initAccData();
$priceMember->initIsFollow();

if(!empty($priceMember->oldId)){
    PriceTransfer::importPrices($priceMember->oldId, $priceMember->accountId);
}

$List = Price::memberPriceList($memberId, $ServerGroup);

if(!$List){
    die(Api::resultData(['Prices' => [], 'priceMember' => $priceMember]));
}


echo Api::resultData(['Prices' => $List, 'priceMember' => $priceMember]);
