<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/includes/config.php';

use Craft\CraftCounter;
use Item\Item;
use Packs\{PackIds, PackRoute};
use User\Account;

$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

$side = intval($_POST['side'] ?? 0);

if(!empty($_POST['addProfit'])){
    $uncounted = PackIds::getUncounted($side);
    if(!empty($uncounted)){
        $CraftCounter = CraftCounter::recountList($uncounted);
        if(!empty($CraftCounter->lost)){
            $Lost = Item::searchList($CraftCounter->lost);
            die(Api::resultData(['Packs' => [], 'Lost' => $Lost]));
        }
    }
}

$Packs = PackRoute::getList($side)
or die(Api::errorMsg('Паки не найдены'));

echo Api::resultData(['Packs' => $Packs, 'Lost' => []]);