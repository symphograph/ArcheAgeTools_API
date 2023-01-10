<?php

namespace Craft;

class MatSum
{
    public function __construct(
        public int $craftCost,
        public int $sumSPM
    )
    {
    }

    public static function getSum(
        int|float $craftCost,
        int $sumSPM,
        Craft $craft
    ): self
    {
        $craftCost = self::addLaborCost($craftCost,$craft);
        $craftCost = round($craftCost / $craft->resultAmount);
        $sumSPM = round($sumSPM / $craft->resultAmount);
        return new self($craftCost, $sumSPM);
    }

    public static function getGroupSum(
        int|float $craftCost,
        int $groupAmount,
        int $sumSPM,
        Craft $craft
    ): self
    {
        $craftCost = self::addLaborCost($craftCost,$craft);
        $craftCost = round($craftCost / $groupAmount);
        $sumSPM = round($sumSPM / $craft->resultAmount);
        return new self($craftCost, $sumSPM);
    }

    private static function addLaborCost(int|float $craftCost, Craft $craft): int|float
    {
        $LaborData = LaborData::byCraft($craft);
        $craftCost += $LaborData->forThisCraftBonused * $LaborData::getLaborCost();
        return $craftCost;
    }

}