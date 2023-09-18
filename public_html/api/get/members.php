<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\{AccSettings, Member, Server};
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Errors\ValidationErr;

$AccSets = AccSettings::byJwt();
$serverGroupId = $_POST['serverGroupId'] ?? throw new ValidationErr('invalid serverId', 'Сервер не найден');


$List = Member::getList($AccSets->accountId, $serverGroupId)
    or throw new AppErr('members not found');

$Members = [];
foreach ($List as $member){
    if(!$member->initLastPricedItem($serverGroupId)){
        continue;
    }
    $Members[] = $member;
}

Response::data($Members);