<?php

namespace App\Craft;


use App\Errors\CraftCountErr;
use App\Item\Item;
use App\User\AccSettings;
use PDO;
use Symphograph\Bicycle\Errors\AppErr;


class CraftCounter
{

    public array $lost         = [];
    public array $countedItems = [];
    public array $groupCrafts  = [];
    public array $countedCrafts = [];


    public static function clearBuff(): void
    {
        BufferFirst::clearDB();
        BufferSecond::clearDB();
    }

    public static function recountList(array $itemIds): self
    {
        //AccountCraft::clearAllCrafts();
        CraftCounter::clearBuff();
        $craftCounter = new self();
        //$start = Test::startTime('recountItem');
        foreach ($itemIds as $itemId){
            $craftCounter = CraftCounter::recountItem($itemId, $craftCounter);
        }
        //echo Test::scriptTime($start, 'recountItem');
        CraftCounter::clearBuff();

        if (empty($craftCounter->lost)){
            LaborCounter::recountInList($craftCounter->countedCrafts);
        }else{
            AccountCraft::clearAllCrafts();
        }
        return $craftCounter;
    }

    public static function recountItem(int $itemId, ?self $CraftCounter = null): self
    {

        if(empty($CraftCounter)){
            $CraftCounter = new self();
        }
        if(in_array($itemId, $CraftCounter->countedItems)){
            return $CraftCounter;
        }

        $List = Craft::allPotentialCrafts($itemId);

        //printr($List);

        foreach ($List as $resultItemId => $crafts){
            if(in_array($resultItemId, $CraftCounter->countedItems)){
                continue;
            }
            $Item = Item::byId($resultItemId);
            //printr($Item->name);

            foreach ($crafts as $craft){

                $matSum = $CraftCounter->matSumCost($craft);
                $buff = BufferFirst::putToDB($craft->id,$matSum->craftCost, $matSum->sumSPM);
                if(!$buff){
                    continue;
                }
                $CraftCounter->countedCrafts[] = $craft->id;
            }
            BufferSecond::saveCrafts($resultItemId);
            $CraftCounter->countedItems[] = $resultItemId;
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

        if(!empty($craft->error)){
            throw new CraftCountErr('Craft '. $craft->id . 'is error');
        }

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

    /**
     * @return array<int>
     */
    public static function getBuyOnlyItems(): array
    {
        $AccSets = AccSettings::byGlobal();
        $qwe = qwe("
            select itemId 
            from uacc_buyOnly
            where accountId = :accountId",
            ['accountId' => $AccSets->accountId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        return $qwe->fetchAll(PDO::FETCH_COLUMN);
    }
}