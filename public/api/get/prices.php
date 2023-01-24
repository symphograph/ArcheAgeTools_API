<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/includes/config.php';

use Item\Price;
use Transfer\PriceTransfer;
use User\{Account, Member, Server};

$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

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
