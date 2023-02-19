<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\{Account, Member, Server};
use App\Api;

$Account = Account::byToken();

$ServerGroup = Server::getGroup(intval($_POST['serverId'] ?? 0))
or Api::errorResponse('Сервер не указан', 400);


$List = Member::getList($Account->id, $ServerGroup)
or Api::errorResponse('members not found');

$Members = [];
foreach ($List as $member){
    $member->initAccData();
    if(!$member->initLastPricedItem($ServerGroup)){
        continue;
    }
    $Members[] = $member;
}

Api::dataResponse($Members);