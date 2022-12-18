<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/includes/config.php';

use User\{Account, Server};

$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

$serverId = intval($_POST['serverId'] ?? 0)
    or die(Api::errorMsg('server'));

$Server = Server::byId($serverId)
    or die(Api::errorMsg('server not found'));

$List = \User\Member::getList($Account->id,$Server->group)
    or die(Api::errorMsg('members not found'));

$Members = [];
foreach ($List as $member){
    $Acc = Account::byId($member->accountId);
    $Acc->Member = $member;
    $Members[] = $Acc;
}

echo Api::resultData($Members);