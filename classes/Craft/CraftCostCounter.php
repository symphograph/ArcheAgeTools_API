<?php

namespace Craft;

use Item\Price;

class CraftCostCounter
{
    public int $laborCost;
    public int $craftCost;
    public int $sumSPM;


    private function initLaborCost(): void
    {
        if($Price = Price::bySaved(2)){
            $this->laborCost = $Price->price;
            return;
        }
        $this->laborCost = 300;
    }

    public function getLaborCost(): int
    {
        if(empty($this->laborCost)){
            self::initLaborCost();
        }
        return $this->laborCost;
    }

    public static function clearBuff(): void
    {
        BufferFirst::clearDB();
        BufferSecond::clearDB();
    }

    public static function buffering(int $itemId, array $completed = [])
    {
        $CraftCounter = new self();
        $List = Craft::allPotentialCrafts($itemId);
        $countedCrafts = [];
        foreach ($List as $resultItemId => $crafts){
            if(array_key_exists($resultItemId, $completed)){
                continue;
            }
            foreach ($crafts as $craft){
                $matSum = $CraftCounter->matSumCost($craft);
                BufferFirst::putToDB($craft->id,$matSum->craftCost, $matSum->sumSPM);

                $countedCrafts[$craft->id] = $craft->resultItemId;
            }

            $completed[$resultItemId] = 1;
        }
    }

    private function matSumCost(Craft $craft): MatSum
    {
        if($groupCraft = self::groupCraft()){
            return $groupCraft;
        }

        $sum = $sumSPM = 0;
        foreach ($craft->Mats as $mat) {
            if (!$mat->need) {
                continue;
            }
            if(!$mat->initPrice() && !$mat->Item->craftable){
                self::addToLost($mat->id);
                continue;
            }
            if ($mat->need > 0) {
                $spm = 0;
                if($Buffer = BufferSecond::byItemId($mat->id)){
                    $spm = $Buffer->spm;
                }
                $sumSPM += $spm;
            }


            $sum += $mat->Price->price + $mat->need;
        }
        $craftCost = $sum + ($craft->laborNeed * self::getLaborCost());
        $craftCost = round($craftCost / $craft->resultAmount);
        $sumSPM = round($sumSPM / $craft->resultAmount);
        return new MatSum($craftCost, $sumSPM);
    }

    private static function groupCraft()
    {
        return false;
    }

    private static function addToLost(int $itemId): void
    {
        global $lost;
        if(!isset($lost)){
            $lost = [];
        }
        $lost[] = $itemId;
    }
}