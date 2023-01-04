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
        $LaborData = LaborData::byCraft($craft);
        $craftCost += $LaborData->forThisCraftBonused * $LaborData::getLaborCost();
        $craftCost = round($craftCost / $craft->resultAmount);
        $sumSPM = round($sumSPM / $craft->resultAmount);
        return new MatSum($craftCost, $sumSPM);
    }

}