<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/includes/config.php';

use Craft\CraftCounter;
use Item\{Item, Price};
use Packs\{PackIds, PackRoute};
use User\Account;

$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

$side = intval($_POST['side'] ?? 0);
$condition = intval($_POST['condition'] ?? 0);

$lostSolutions = [];
if(!$akhiumSolutionPrice = Price::bySaved(32103)){
    $lostSolutions[] = 32103;
}

if(!$alkaliSolutionPrice = Price::bySaved(32106)){
    $lostSolutions[] = 32106;
}

if(!empty($lostSolutions)){
    $Lost = Price::lostList($lostSolutions);
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
$currencyPrices = (object) [
    500 => $goldPrice,
    32106 => $alkaliSolutionPrice,
    32103 => $akhiumSolutionPrice
];
echo Api::resultData(['Packs' => $Packs, 'Lost' => [], 'currencyPrices' => $currencyPrices]);