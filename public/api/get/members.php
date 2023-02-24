<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\{Account, Member, Server};
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Errors\ValidationErr;

$Account = Account::byToken();

$ServerGroup = Server::getGroup(intval($_POST['serverId'] ?? 0))
    or throw new ValidationErr('invalid serverId', 'Сервер не найден');

$List = Member::getList($Account->id, $ServerGroup)
    or throw new AppErr('members not found');

$Members = [];
foreach ($List as $member){
    $member->initAccData();
    if(!$member->initLastPricedItem($ServerGroup)){
        continue;
    }
    $Members[] = $member;
}

Response::data($Members);