<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\{Account, Member, Server};
use App\Api;
use App\Craft\AccountCraft;
use App\Errors\MyErrors;
use App\Errors\ValidationErr;

$Account = Account::byToken();

try {
    $master = intval($_POST['master'] ?? 0)
        or throw new ValidationErr('master', 'Ошибка данных');

    $serverId = intval($_POST['serverId'] ?? 0)
        or throw new ValidationErr('serverId', 'Ошибка данных');

    $Server = Server::byId($serverId)
        or throw new ValidationErr('server not found', 'Сервер не указан');

    $isFollow = ($_POST['isFollow'] ?? null);
    if($isFollow === null){
        throw new ValidationErr('isFollow', 'Ошибка данных');
    }

    if($isFollow){
        Member::setFollow($Account->id, $master, $Server->group);
    }else{
        Member::unsetFollow($Account->id, $master, $Server->group);
    }
    AccountCraft::clearAllCrafts();
    Api::resultResponse();

} catch (MyErrors $err) {
    Api::errorResponse($err->getResponseMsg(), $err->getHttpStatus());
}