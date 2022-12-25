<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';

use User\Account;
$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

$itemId = intval($_POST['itemId'] ?? 0)
or die(Api::errorMsg('id'));

$buyable = intval($_POST['buyable'] ?? 0);
if (!$buyable){
    $sql = "delete from uacc_buyOnly where accountId = :accountId and itemId = :itemId";
}else{
    $sql = "replace into uacc_buyOnly (accountId, itemId) VALUES (:accountId, :itemId)";
}

$qwe = qwe($sql,
[
    'accountId'   => $Account->id,
    'itemId'      => $itemId,
]
) or die(Api::errorMsg());

echo Api::resultMsg();