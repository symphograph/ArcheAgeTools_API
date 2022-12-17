<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/includes/config.php';

use User\{Account, Server};

$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

$server_id = intval($_POST['server_id'] ?? 0)
    or die(Api::errorMsg('server'));

$Server = Server::byId($server_id)
    or die(Api::errorMsg('server not found'));

$List = \User\Member::getList($Account->id,$Server->group)
    or die(Api::errorMsg('members not found'));

$Members = [];
foreach ($List as $member){
    $Acc = Account::byId($member->account_id);
    $Acc->Member = $member;
    $Members[] = $Acc;
}

echo Api::resultData($Members);