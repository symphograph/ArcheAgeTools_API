<?php

namespace App\Test;

use App\Api;
use App\Errors\AppErr;
use App\Craft\{Craft, CraftCounter};
use App\Item\{Item, Price, Pricing};
use App\Packs\{Pack, PackIds};
use App\User\Account;

class Test
{
    public static function ItemList()
    {

    }

    public static function pricingByItemId(): void
    {
        $List = Item::searchList()
            or throw new AppErr('pricingByItemId err');
        foreach ($List as $item){
            if (!($Pricing = Pricing::byItemId($item->id))) {
                echo "<br>item_id: $item->id. err";
            }
        }
    }

    public static function PriceFinder(): void
    {
        $Account = Account::bySess();
        $Account->initMember();
        $List = Item::searchList();
        foreach ($List as $item){
            $Price = Price::bySaved($item->id,1);
            if(!$Price) continue;
            $Price->initLabel();
            echo $item->name . '<br>';
            printr($Price);
            echo '<hr>';
        }
    }

    public static function countPackCrafts(): void
    {
        //CraftCounter::clearBuff();
        $packIds = PackIds::getAll();
        $start = self::startTime('CraftCounter');
        $craftCounter = CraftCounter::recountList($packIds);

        echo self::scriptTime($start, 'CraftCounter');
        printr($craftCounter->lost);
    }

    public static function countAllCrafts(): void
    {
        //AccountCraft::clearAllCrafts();
        $allIds = Craft::getAllResultItems();
        $start = self::startTime('CraftCounter');
        $craftCounter = CraftCounter::recountList($allIds);

        echo self::scriptTime($start, 'CraftCounter');
        printr($craftCounter->lost);
    }

    public static function scriptTime(float $start, string $funcName = '$funcName'): string
    {
        $time = round(microtime(true) - $start, 4);
        return "<p>Время $funcName: $time сек.<p>";
    }

    public static function startTime(string $funcName = '$funcName'): float
    {
        //echo "<p>Старт: $funcName</p>";
        return microtime(true);
    }
}