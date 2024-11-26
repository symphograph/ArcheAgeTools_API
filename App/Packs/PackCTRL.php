<?php

namespace App\Packs;

use App\Craft\Craft\CraftList;
use App\Craft\CraftCounter;
use App\Price\Price;
use App\User\User;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Debug\Debug;
use Symphograph\Bicycle\HTTP\Request;

class PackCTRL
{

    public static function list(): void
    {
        User::auth();
        Request::checkEmpty(['side']);
        $debug = new Debug();

        /*
        if (AccSets::$current->authType === AccountType::Default->value) {
            AccSets::$current->accountId = 2205;
        }
        */

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
            $uncounted = PackIds::getUncounted($_POST['side']);
            if(!empty($uncounted)){
                $CraftCounter = CraftCounter::recountList($uncounted);
                if(!empty($CraftCounter->lost)){
                    $Lost = Price::lostList($CraftCounter->lost);
                    Response::data(['Packs' => [], 'Lost' => $Lost]);
                }
            }
        }

        $PackList = PackRouteList::bySide($_POST['side'])->initData();
        if(!empty($_POST['addProfit'])){
            $PackList->initProfit();
        }
        $Packs = $PackList->getList();

        foreach ($Packs as $Pack){
            $crafts = CraftList::byResultItemId($Pack->itemId)->initMats()->getList();
            $Craft = $crafts[0];
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
        $debug->printFooter();

        Response::data(['Packs' => $Packs, 'Lost' => [], 'currencyPrices' => $currencyPrices]);
    }
}