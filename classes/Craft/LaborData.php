<?php

namespace Craft;

use User\Prof;

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
        $LaborData->forThisCraftBonused = round($craft->laborNeed * ((100 - $prof->laborBonus) / 100));
        $LaborData->forOneUnitOfThisCraft = $LaborData->forThisCraftBonused / $craft->resultAmount;

        return $LaborData;
    }

    public static function getLaborCost(): int
    {
        global $Account;
        return $Account->AccSets->getLaborCost();
    }
}