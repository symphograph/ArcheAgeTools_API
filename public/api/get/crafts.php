<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';

use Craft\{Craft, CraftCounter, CraftPool};
use Item\{Item, Price};
use Test\Test;
use User\Account;


$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));
$Account->initMember();
$itemId = intval($_POST['itemId'] ?? 0) or die('item_id');

if($Pool = CraftPool::getByCache($itemId)){
    die(Api::resultData($Pool));
}

$craftCounter = CraftCounter::recountList([$itemId]);
if(!empty($craftCounter->lost)){
    $Lost = Price::lostList($craftCounter->lost);
    die( Api::resultData(['Lost' => $Lost]));
}

foreach ($craftCounter->countedItems as $resultItemId){
    $CraftPool = CraftPool::getPoolWithAllData($resultItemId);
}
$Pool = CraftPool::getByCache($itemId)
or die(Api::errorMsg('Рецепты не найдены'));

echo Api::resultData($Pool);