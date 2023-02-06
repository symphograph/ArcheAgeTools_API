<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Craft\{Craft, CraftCounter, CraftPool};
use App\Item\{Item, Price};
use App\Test\Test;
use App\User\Account;


$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));
$Account->initMember();
$itemId = intval($_POST['itemId'] ?? 0) or die('item_id');

if($Pool = CraftPool::getByCache($itemId)){
    die(Api::resultData($Pool));
}

try {
    $craftCounter = CraftCounter::recountList([$itemId]);
    if(!empty($craftCounter->lost)){
        $Lost = Price::lostList($craftCounter->lost);
        die( Api::resultData(['Lost' => $Lost]));
    }
}catch (Error $error){
    die(Api::errorMsg('Почему-то не получается', $error));
}


foreach ($craftCounter->countedItems as $resultItemId){
    $CraftPool = CraftPool::getPoolWithAllData($resultItemId);
}
$Pool = CraftPool::getByCache($itemId)
or die(Api::errorMsg('Рецепты не найдены'));

echo Api::resultData($Pool);