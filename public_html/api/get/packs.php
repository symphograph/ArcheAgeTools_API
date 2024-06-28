<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Craft\Craft;
use App\Craft\CraftCounter;
use App\Packs\{PackIds, PackRoute};
use App\Price\Price;
use App\User\AccSets;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AppErr;


$AccSets = AccSets::byJwt();
$side = intval($_POST['side'] ?? 0);
$condition = intval($_POST['condition'] ?? 0);

$lostPrices = [];
if(!$akhiumSolutionPrice = Price::bySaved(32103)){
    $lostPrices[] = 32103;
}

if(!$alkaliSolutionPrice = Price::bySaved(32106)){
    $lostPrices[] = 32106;
}

if(!empty($_POST['addProfit']) && !$laborPrice = Price::bySaved(2)){
    $lostPrices[] = 2;
}

if(!empty($lostPrices)){
    $Lost = Price::lostList($lostPrices);
    Response::data(['Packs' => [], 'Lost' => $Lost]);
}

if(!empty($_POST['addProfit'])){
    $uncounted = PackIds::getUncounted($side);
    if(!empty($uncounted)){
        $CraftCounter = CraftCounter::recountList($uncounted);
        if(!empty($CraftCounter->lost)){
            $Lost = Price::lostList($CraftCounter->lost);
            Response::data(['Packs' => [], 'Lost' => $Lost]);
        }
    }
}


$Packs = PackRoute::getList($side, !empty($_POST['addProfit']))
    or throw new AppErr('packs is empty','Паки не найдены');
$List = [];
foreach ($Packs as $Pack){
    $Craft = Craft::getList($Pack->itemId)[0];
    $Pack->Mats = $Craft->Mats;
}

$goldPrice = new Price();
$goldPrice->itemId = 500;
$goldPrice->accountId = 0;
$goldPrice->price = 1;
$currencyPrices = [
    500 => $goldPrice,
    32106 => $alkaliSolutionPrice,
    32103 => $akhiumSolutionPrice
];
if(!empty($laborPrice)){
    $currencyPrices[] = $laborPrice;
}
Response::data(['Packs' => $Packs, 'Lost' => [], 'currencyPrices' => $currencyPrices]);