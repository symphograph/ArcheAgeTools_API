<?php

namespace App\Test;

use App\Api;
use Symphograph\Bicycle\Errors\AppErr;
use App\Craft\{Craft, CraftCounter};
use App\Item\{Item,Pricing};
use App\Packs\{Pack, PackIds};

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
        $time = self::endTime($start);
        return "<hr><p>Время $funcName: $time сек.<p>";
    }

    public static function endTime(float $start): float
    {
        return  round(microtime(true) - $start, 4);
    }

    public static function startTime(): float
    {
        return microtime(true);
    }
}