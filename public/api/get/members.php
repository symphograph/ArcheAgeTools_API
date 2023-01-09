<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/includes/config.php';

use User\{Account, Member, Server};

$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

$ServerGroup = Server::getGroup(intval($_POST['serverId'] ?? 0))
or die(Api::errorMsg('server not found'));

$List = Member::getList($Account->id,$ServerGroup)
    or die(Api::errorMsg('members not found'));

$Members = [];
foreach ($List as $member){
    $member->initAccData();
    $member->initLastPricedItem($ServerGroup);
    $Members[] = $member;
}

echo Api::resultData($Members);