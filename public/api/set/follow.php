<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/includes/config.php';

use User\{Account, Member, Server};
use Craft\AccountCraft;

$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

$master = intval($_POST['master'] ?? 0)
    or die(Api::errorMsg('master'));

$serverId = intval($_POST['serverId'] ?? 0)
or die(Api::errorMsg('server'));

$Server = Server::byId($serverId)
or die(Api::errorMsg('server not found'));

if(($_POST['isFollow'] ?? null) === true){
    Member::setFollow($Account->id, $master, $Server->group);
    AccountCraft::clearAllCrafts();
    die(Api::resultMsg());
}

if(($_POST['isFollow'] ?? null) === false){
    Member::unsetFollow($Account->id, $master, $Server->group);
    AccountCraft::clearAllCrafts();
    die(Api::resultMsg());
}

Api::errorMsg('Не получилось');