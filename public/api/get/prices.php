<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/includes/config.php';

use User\{Account, Member, Server};
use Item\Price;

$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

$ServerGroup = Server::getGroup(intval($_POST['serverId'] ?? 0))
or die(Api::errorMsg('server not found'));

$memberId = intval($_POST['accId'] ?? 0) ?: $Account->id;
$List = Price::memberPriceList($memberId, $ServerGroup)
    or die(Api::errorMsg('Не найдено'));

$priceMember = new Member();
$priceMember->accountId = $memberId;
$priceMember->initAccData();
$priceMember->initIsFollow();
echo Api::resultData(['Prices' => $List, 'priceMember' => $priceMember]);
