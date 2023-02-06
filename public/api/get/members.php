<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\{Account, Member, Server};
use App\Api;

$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

$ServerGroup = Server::getGroup(intval($_POST['serverId'] ?? 0))
or die(Api::errorMsg('Сервер не указан'));

$List = Member::getList($Account->id,$ServerGroup)
    or die(Api::errorMsg('members not found'));

$Members = [];
foreach ($List as $member){
    $member->initAccData();
    if(!$member->initLastPricedItem($ServerGroup)){
        continue;
    }
    $Members[] = $member;
}

echo Api::resultData($Members);