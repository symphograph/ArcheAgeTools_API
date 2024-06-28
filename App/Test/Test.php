<?php

namespace App\Test;

use App\Api;
use Symphograph\Bicycle\Errors\AppErr;
use App\Craft\{AccountCraft, Craft, CraftCounter};
use App\Item\{Item,Pricing};
use App\Packs\{Pack, PackIds};
use Symphograph\Bicycle\Helpers;
use Symphograph\Bicycle\Helpers\ArrayHelper;

class Test
{
    public array $durations = [];

    public function medianDuration()
    {
        return Helpers::median($this->durations);
    }

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
        $start = self::startTime();
        $craftCounter = CraftCounter::recountList($packIds);

        echo self::scriptTime($start, 'CraftCounter');
        printr($craftCounter->lost);
    }

    public static function countAllCrafts(): void
    {
        //AccountCraft::clearAllCrafts();
        $allIds = Craft::getAllResultItems();
        $start = self::startTime();
        $craftCounter = CraftCounter::recountList($allIds);

        echo self::scriptTime($start, 'CraftCounter');
        printr($craftCounter->lost);
    }

    public static function scriptTime(float $start, string $funcName = '$funcName'): string
    {
        $time = self::duration($start);
        return "<hr><p>Время $funcName: $time сек.<p>";
    }

    public static function duration(float $start): float
    {
        return  round(microtime(true) - $start, 6);
    }

    public static function startTime(): float
    {
        return microtime(true);
    }

    public function speedTestTime(string $fnName, int $count = 1, $arg = null): int|float
    {
        for ($i = $count; $i > 0; $i--) {
            $start = microtime(true);
            self::$fnName($arg);
            $this->durations[] = self::duration($start);
        }

        $this->durations = array_map(fn($var) => $var*1000000, $this->durations);
        return Helpers::median($this->durations)/1000000;
    }

    public function sortFunction(array $list): array
    {
        $sort = ['categId' => 'asc', 'name' => 'asc'];
        return ArrayHelper::sortMultiArrayByProp($list, $sort);
    }

    public function sqlBenchMark(): void
    {
        $qwe = qwe("
            select crafts.* from crafts 
            inner join items
            on crafts.resultItemId = items.id
            and items.onOff
            and crafts.onOff
            order by categId, name"
        );
        //$qwe = $qwe->fetchAll();
    }

    public function craftCount(int $itemId): void
    {
        AccountCraft::clearAllCrafts();
        $craftCounter = CraftCounter::recountList([$itemId]);
    }
}