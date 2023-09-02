<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\{AccSettings, Member, Server};
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Errors\ValidationErr;

$AccSets = AccSettings::byJwt();
$serverId = $_POST['serverId'] ?? throw new ValidationErr('invalid serverId', 'Сервер не найден');
$ServerGroup = Server::getGroupId($serverId)
    or throw new ValidationErr('invalid serverId', 'Сервер не найден');

$List = Member::getList($AccSets->accountId, $ServerGroup)
    or throw new AppErr('members not found');

$Members = [];
foreach ($List as $member){
    if(!$member->initLastPricedItem($ServerGroup)){
        continue;
    }
    $Members[] = $member;
}

Response::data($Members);