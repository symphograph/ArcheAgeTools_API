<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Errors\MyErrors;
use App\Craft\{CraftCounter, CraftPool};
use App\Item\{Price};
use App\User\Account;



$Account = Account::byToken();
$Account->initMember();
$itemId = intval($_POST['itemId'] ?? 0)
or Api::errorResponse('itemId is empty', 400);

if($Pool = CraftPool::getByCache($itemId)){
    Api::dataResponse($Pool);
}

try {
    $craftCounter = CraftCounter::recountList([$itemId]);
    if(!empty($craftCounter->lost)){
        $Lost = Price::lostList($craftCounter->lost);
        Api::dataResponse(['Lost' => $Lost]);
    }
}catch (MyErrors $err){
    Api::errorResponse($err->getMessage());
}


foreach ($craftCounter->countedItems as $resultItemId){
    $CraftPool = CraftPool::getPoolWithAllData($resultItemId);
}
$Pool = CraftPool::getByCache($itemId)
or Api::errorResponse('Рецепты не найдены');

Api::dataResponse($Pool);