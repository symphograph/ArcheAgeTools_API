<?php

namespace Craft;

use Item\Price;
use User\Prof;

class LaborCounter
{
    public int $headCraftId;
    public int|float $laborSum = 0;

    public static function recountInList(array $crafts): void
    {
        foreach ($crafts as $craftId){
            $craft = Craft::byId($craftId);
            //printr($craft);
            $laborCounter = new self();
            $laborCounter->countChainLabor($craft);
            $laborCounter->saveLaborSum($craft);
        }
    }

    private function countChainLabor(Craft $craft, float|int $need = 1): void
    {
        $LaborData = LaborData::byCraft($craft);
        //printr($craft);
        $this->laborSum += $LaborData->forOneUnitOfThisCraft * $need;
        foreach ($craft->Mats as $mat){
            if(!$mat->craftable)
                continue;
            if(!($mat->need > 0))
                continue;
            $matCrafts = CraftPool::getPool($mat->id);
            $matMainCraft = $matCrafts->mainCraft;
            self::countChainLabor($matMainCraft, $mat->need/$craft->resultAmount);
        }
    }

    private function saveLaborSum(Craft $craft): void
    {
        $craft->countData->laborTotal = round($this->laborSum, 4);
        $craft->countData->putToDB();
    }

}