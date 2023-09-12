<?php

namespace App\Transfer\Deep;

use PDO;

class DeepFinder
{
    private array $deepList = [];
    private array $craftMats = [];
    private array $itemCrafts = [];
    public int $badCraft = 0;

    public function execute(): bool
    {
        $qwe = qwe("
            select crafts.id from crafts 
            inner join items 
                on items.id = crafts.resultItemId
                 and items.onOff
            where crafts.onOff
            /*and crafts.id >= 11021*/
            /*order by rand()*/
            /*and crafts.id > 12000*/
            /*limit 10*/
            "
        );
        $CraftList = $qwe->fetchAll(PDO::FETCH_COLUMN);
        foreach ($CraftList as $craftId){
            if(!self::deepFind($craftId, 0)){
                return false;
            }
        }
        asort($this->deepList);
        printr(count($this->deepList));
        printr($this->deepList);
        return self::updateAllDeeps();
    }

    private function deepFind(int $craftId, int $deep): bool
    {
        $deep++;
        if($deep > 30){
            $this->badCraft = $craftId;
            return false;
        }
        $mats = self::getCraftableMats($craftId);
        if(empty($mats)) return true;


        foreach ($mats as $matId){
            $Crafts = self::getCrafts($matId);
            if (empty($Crafts)) continue;
            $curDeep = $this->deepList[$matId] ?? 0;
            $this->deepList[$matId] = max($deep, $curDeep);
            foreach ($Crafts as $craftChildId){
                self::deepFind($craftChildId, $deep);
            }
        }
        return true;
    }

    private static function isCraftable(int $itemId): bool
    {
        $qwe = qwe("
            select resultItemId 
            from crafts 
            where resultItemId = :itemId 
              and onOff",
            ['itemId'=> $itemId]
        );
        return $qwe && $qwe->rowCount();
    }

    /**
     * @return array<int>
     */
    private function getCraftableMats(int $craftId): array
    {
        if(!empty($this->craftMats[$craftId])){
            return $this->craftMats[$craftId];
        }
        $qwe = qwe("
            select i.id
            from craftMaterials cm
            inner join items i 
                on cm.itemId = i.id
                and i.onOff
            inner join crafts on i.id = crafts.resultItemId
                and crafts.onOff
            where craftId = :craftId
            and cm.need > 0
            group by id",
            ['craftId'=>$craftId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        $arr = $qwe->fetchAll(PDO::FETCH_COLUMN);
        if(empty($arr))
            return [];
        $this->craftMats[$craftId] = $arr;
        return $arr;
    }

    /**
     * @return array<int>
     */
    private function getCrafts(int $resultItemId): array
    {
        if(!empty($this->itemCrafts[$resultItemId])){
            return $this->itemCrafts[$resultItemId];
        }
        $qwe = qwe("select * from crafts where resultItemId = :resultItemId and onOff", ['resultItemId'=>$resultItemId]);
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        $arr = $qwe->fetchAll(PDO::FETCH_COLUMN);
        if(empty($arr))
            return [];

        $this->itemCrafts[$resultItemId] = $arr;
        return $arr;
    }

    private function updateAllDeeps(): bool
    {
        foreach ($this->deepList as $craftId => $deep){
            if(!self::updateCraftDeep($deep, $craftId)){
                return false;
            }
        }
        return true;
    }

    private static function updateCraftDeep(int $deep, int $craftId): bool
    {
        return !!qwe("
            update crafts 
            set deep = :deep 
            where id = :id",
            ['deep' => $deep, 'id' => $craftId]
        );
    }
}