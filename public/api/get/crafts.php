<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';

use Craft\{Craft, CraftCounter, CraftPool};
use Item\{Item, Price};
use User\Account;


$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));
$Account->initMember();
$itemId = intval($_POST['itemId'] ?? 0) or die('item_id');
$craftCounter = CraftCounter::recountList([$itemId]);

if(!empty($craftCounter->lost)){
    $Lost = Price::lostList($craftCounter->lost);
    die( Api::resultData(['Lost' => $Lost]));
}

$Pool = CraftPool::getPool($itemId);
$Pool->initMatPrices();
echo Api::resultData($Pool);