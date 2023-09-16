<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\AccSettings;
use Symphograph\Bicycle\Api\Response;
use App\Craft\{CraftCounter, CraftPool};
use App\Item\{Price};
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Errors\ValidationErr;


$AccSets = AccSettings::byJwt();
$itemId = intval($_POST['itemId'] ?? false)
or throw new ValidationErr();

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
    or throw new AppErr('CraftPool is empty', 'Рецепты не найдены');

Response::data($Pool);