<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';

use User\Account;
$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

$itemId = intval($_POST['itemId'] ?? 0)
or die(Api::errorMsg('id'));

$buyable = intval($_POST['buyable'] ?? 0);

$qwe = qwe("
update uacc_prices 
set buyOnly = :buyable 
where itemId = :itemId 
  and serverGroup = :serverGroup
  and accountId = :accountId",
[
    'buyable'     => $buyable,
    'itemId'      => $itemId,
    'serverGroup' => $Account->AccSets->serverGroup,
    'accountId'   => $Account->id
]
) or die(Api::errorMsg());

echo Api::resultMsg();