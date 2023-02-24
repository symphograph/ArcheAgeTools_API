<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\MyErrors;
use App\Craft\{CraftCounter, CraftPool};
use App\Item\{Price};
use App\User\Account;



$Account = Account::byToken();
$Account->initMember();
$itemId = intval($_POST['itemId'] ?? 0)
or Response::error('itemId is empty', 400);

if($Pool = CraftPool::getByCache($itemId)){
    Response::data($Pool);
}

$craftCounter = CraftCounter::recountList([$itemId]);
if(!empty($craftCounter->lost)){
    $Lost = Price::lostList($craftCounter->lost);
    Response::data(['Lost' => $Lost]);
}


foreach ($craftCounter->countedItems as $resultItemId){
    $CraftPool = CraftPool::getPoolWithAllData($resultItemId);
}
$Pool = CraftPool::getByCache($itemId)
or Response::error('Рецепты не найдены');

Response::data($Pool);