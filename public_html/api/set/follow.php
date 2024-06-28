<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\{AccSets, Member, Server};
use Symphograph\Bicycle\Api\Response;
use App\Craft\AccountCraft;
use Symphograph\Bicycle\Errors\ValidationErr;

$AccSets = AccSets::byJwt();

$master = $_POST['master']
    ?? throw new ValidationErr('master');

$serverGroupId = $_POST['serverGroupId']
    ?? throw new ValidationErr('serverGroupId');

$isFollow = ($_POST['isFollow'] ?? null);
if($isFollow === null){
    throw new ValidationErr('isFollow');
}

if($isFollow){
    Member::setFollow($AccSets->accountId, $master, $serverGroupId);
}else{
    Member::unsetFollow($AccSets->accountId, $master, $serverGroupId);
}
AccountCraft::clearAllCrafts();
Response::success();