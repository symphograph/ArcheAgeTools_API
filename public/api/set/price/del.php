<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';

use App\Api;
use App\Craft\AccountCraft;
use App\User\Account;
$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

$itemId = intval($_POST['itemId'] ?? 0)
or die(Api::errorMsg('id'));

$qwe = qwe("
delete from uacc_prices 
       where accountId = :accountId 
         and itemId = :itemId 
         and serverGroup = :serverGroup",
['accountId' => $Account->id, 'itemId' => $itemId, 'serverGroup' => $Account->AccSets->serverGroup]
) or die(Api::errorMsg('Ошибка при удалении'));

AccountCraft::clearAllCrafts();

echo Api::resultMsg();