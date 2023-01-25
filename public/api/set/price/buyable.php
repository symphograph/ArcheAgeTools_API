<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';

use App\Api;
use App\Craft\AccountCraft;
use App\Item\Item;
use App\User\Account;
$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

$itemId = intval($_POST['itemId'] ?? 0)
or die(Api::errorMsg('id'));

$buyable = intval($_POST['buyable'] ?? 0);
$Item = Item::byId($itemId)
or die(Api::errorMsg('Предмет не найден'));

if (!$buyable){
    $sql = "delete from uacc_buyOnly where accountId = :accountId and itemId = :itemId";
}else{
    if (!$Item->craftable)
        die(Api::errorMsg('Предмет не крафтабельный'));

    if ($Item->personal)
        die(Api::errorMsg('Персональный предмет'));

    $sql = "replace into uacc_buyOnly (accountId, itemId) VALUES (:accountId, :itemId)";
}

$qwe = qwe($sql,
[
    'accountId'   => $Account->id,
    'itemId'      => $itemId,
]
) or die(Api::errorMsg());

AccountCraft::clearAllCrafts();

echo Api::resultMsg();