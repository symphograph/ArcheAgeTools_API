<?php

namespace App\Craft;

use App\User\AccSettings;
use App\User\Prof;

class LaborData
{
    const defaultLaborCost = 300;
    public int $forThisCraftDefault = 0;
    public int $forThisCraftBonused = 0;
    public int|float $forOneUnitOfThisCraft = 0;
    public int|float $forAllCraftChain = 0;

    public static function byCraft(Craft $craft): self
    {
        $prof = Prof::getAccProfById($craft->profId);
        if(!$prof) {
            $prof = new Prof();
        }
        $LaborData = new self();
        $LaborData->forThisCraftDefault= $craft->laborNeed;
        $LaborData->forThisCraftBonused = self::getBonusedLabor($craft->laborNeed, $prof->laborBonus);
        $LaborData->forOneUnitOfThisCraft = $LaborData->forThisCraftBonused / $craft->resultAmount;

        return $LaborData;
    }

    public static function getLaborCost(): int
    {
        $AccSets = AccSettings::byGlobal();
        return $AccSets->getLaborCost();
    }

    public static function getBonusedLabor(int|float $laborNeed, int $laborBonus): int
    {
        return round($laborNeed * ((100 - $laborBonus) / 100));
    }
}