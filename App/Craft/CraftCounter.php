<?php

namespace App\Craft;


use App\Craft\Craft\Craft;
use App\Craft\Craft\CraftList;
use App\Craft\UCraft\UCraft;
use App\Mat\MatSum;
use PDO;
use Symphograph\Bicycle\Logs\Log;


class CraftCounter
{

    public array $lost         = [];
    public array $countedItems = [];
    public array $groupCrafts  = [];
    public array $countedCrafts = [];


    private static function clearBuff(): void
    {
        BufferFirst::clearStorage();
        BufferSecond::clearStorage();
    }

    public static function recountList(array $itemIds): self
    {
        CraftCounter::clearBuff();
        $craftCounter = new self();

        foreach ($itemIds as $itemId){
            $craftCounter = CraftCounter::recountItem($itemId, $craftCounter);
        }
        CraftCounter::clearBuff();

        if (empty($craftCounter->lost)){
            LaborCounter::recountInList($craftCounter->countedCrafts);
        }else{
            UCraft::clearAllCrafts();
        }
        return $craftCounter;
    }

    private static function recountItem(int $itemId, ?self $CraftCounter = null): self
    {

        if(empty($CraftCounter)){
            $CraftCounter = new self();
        }
        if(in_array($itemId, $CraftCounter->countedItems)){
            return $CraftCounter;
        }

        $List = CraftList::allPotential($itemId)
            ->initData()
            ->getGrouppedByCol('resultItemId');

        foreach ($List as $resultItemId => $crafts){
            if(in_array($resultItemId, $CraftCounter->countedItems)){
                Log::msg("$resultItemId - уже считал",[], 'craft');
                continue;
            }

            foreach ($crafts as $craft){
                $matSum = $CraftCounter->matSumCost($craft);
                BufferFirst::putToStorage($craft, $matSum->craftCost, $matSum->sumSPM);
                $CraftCounter->countedCrafts[] = $craft->id;
            }
            BufferSecond::saveCrafts();
            $CraftCounter->countedItems[] = $resultItemId;
            CraftPool::getPoolWithAllData($resultItemId);
        }
        $CraftCounter->countedCrafts = array_unique($CraftCounter->countedCrafts);
        $CraftCounter->countedItems = array_unique($CraftCounter->countedItems);
        return $CraftCounter;
    }

    private function matSumCost(Craft $craft): MatSum
    {
        if($groupCraft = self::groupCraft($craft)){
            return $groupCraft;
        }

        $sum = $sumSPM = 0;

        foreach ($craft->Mats as $mat) {

            if (!$mat->need) {
                continue;
            }
            if(!$mat->initPrice()){
                if(!$mat->Item->craftable || $mat->isBuyOnly){
                    self::addToLost($mat->id);
                    continue;
                }
            }
            if ($mat->need > 0) {
                $spm = 0;
                if($Buffer = BufferSecond::byItemId($mat->id)){
                    $spm = $Buffer->spm;
                }
                $sumSPM += $spm;
            }

            $sum += $mat->Price->price * $mat->need;
        }
        return MatSum::getSum($sum, $sumSPM, $craft);
    }

    private function groupCraft(Craft $craft): MatSum|false
    {
        if(!in_array($craft->id, self::getGroupCrafts())){
            return false;
        }
        $groupCraft = GroupCraft::byCraftId($craft->id);
        if(!$groupCraft){
            return false;
        }
        if(!$groupCraft->groupAmount){
            return false;
        }
        $matSum = $groupCraft->getMatSum($craft, $this->lost);
        $this->lost = $matSum->lost;
        return $matSum;
    }

    private function addToLost(int $itemId): void
    {
        $this->lost[] = $itemId;
    }

    private function getGroupCrafts(): array
    {
        if(!empty($this->groupCrafts)){
            return $this->groupCrafts;
        }
        $qwe = qwe("select distinct craftId from craftGroups");
        $this->groupCrafts = $qwe->fetchAll(PDO::FETCH_COLUMN);
        return $this->groupCrafts;
    }

}