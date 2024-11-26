<?php

namespace App\Test;

use App\Craft\{Craft\Craft, CraftCounter, UCraft\UCraft};
use App\Packs\{PackIds};
use Symphograph\Bicycle\Helpers\Arr;
use Symphograph\Bicycle\Helpers\ArrayHelper;
use Symphograph\Bicycle\Helpers\Math;

class Test
{
    public array $durations = [];

    public function medianDuration()
    {
        return Math::median($this->durations);
    }

    public static function ItemList()
    {

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
        return Math::median($this->durations)/1000000;
    }

    public function sortFunction(array $list): array
    {
        $sort = ['categId' => 'asc', 'name' => 'asc'];
        return Arr::sortMultiArrayByProp($list, $sort);
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
        UCraft::clearAllCrafts();
        $craftCounter = CraftCounter::recountList([$itemId]);
    }
}