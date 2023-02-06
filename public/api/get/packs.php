<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Craft\CraftCounter;
use App\Item\{Item, Price};
use App\Packs\{PackIds, PackRoute};
use App\User\Account;

$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

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
    die(Api::resultData(['Packs' => [], 'Lost' => $Lost]));
}

if(!empty($_POST['addProfit'])){
    $uncounted = PackIds::getUncounted($side);
    if(!empty($uncounted)){
        $CraftCounter = CraftCounter::recountList($uncounted);
        if(!empty($CraftCounter->lost)){
            $Lost = Price::lostList($CraftCounter->lost);
            die(Api::resultData(['Packs' => [], 'Lost' => $Lost]));
        }
    }
}


$Packs = PackRoute::getList($side, !empty($_POST['addProfit']))
or die(Api::errorMsg('Паки не найдены'));

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
echo Api::resultData(['Packs' => $Packs, 'Lost' => [], 'currencyPrices' => $currencyPrices]);