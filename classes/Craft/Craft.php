<?php

namespace Craft;

use PDO;
use User\Prof;

class Craft
{
    public int $id;
    /**
     * @var array<Mat>|null
     */
    public ?array     $Mats;
    public ?int       $resultItemId;
    public int|float  $resultAmount = 1;
    public ?int       $doodId;
    public ?string    $doodName;
    public int        $profId       = 25;
    public ?int       $profNeed;
    public ?int       $laborNeed;
    public ?Prof      $Prof;
    public ?CountData $countData;

    public function __set(string $name, $value): void{}

    public static function byId(int $id) : self|bool
    {
        $qwe = qwe("
            select crafts.*, 
                   doods.id as doodId,
                   doods.name as doodName
            from crafts 
            inner join items on items.id = crafts.resultItemId
                and items.onOff
                and crafts.onOff                     
                and crafts.id = :id
            left join doods on doods.id = crafts.doodId",
            ['id' => $id]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        $Craft = $qwe->fetchObject(self::class);
        $Craft->id = $id;
        $Craft->initAllData();
        return $Craft;
    }

    private function initMats(): void
    {
        if($Mats = Mat::getCraftMats($this->id)){
            $this->Mats = $Mats;
        }
    }

    private function initProf(): void
    {
        $Prof = Prof::byNeed($this->profId, $this->profNeed);
        if($Prof){
            $this->Prof = $Prof;
        }
    }

    private function initCountData(): void
    {
        if($countData = CountData::byID($this->id)){
            $this->countData = $countData;
        }
    }

    private function initAllData(): void
    {
        self::initMats();
        self::initProf();
        self::initCountData();
    }

    /**
     * @param array<self> $Crafts
     * @return array<self>
     */
    private static function initDataInList(array $Crafts): array
    {
        $List = [];
        foreach ($Crafts as $craft){
            $craft->initAllData();
            $List[] = $craft;
        }
        return $List;
    }

    public static function getCraftIDs(int $resultItemId): array
    {
        $qwe = qwe("
            select id from crafts 
            where resultItemId = :resultItemId",
            ['resultItemId'=>$resultItemId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        return $qwe->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return array<self>|bool
     */
    public static function getList(int $itemId) : array|bool
    {
        $qwe = qwe("select crafts.*, 
                   doods.id as doodId,
                   doods.name as doodName
            from crafts 
            inner join items on items.id = crafts.resultItemId
                and items.onOff
                and crafts.onOff                   
                and crafts.resultItemId = :itemId
            left join doods on doods.id = crafts.doodId",
        ['itemId'=>$itemId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        $Crafts = $qwe->fetchAll(PDO::FETCH_CLASS,self::class);

        return self::initDataInList($Crafts);
    }

    /**
     * @return array<array<self>>
     */
    public static function allPotentialCrafts(int $itemId): array
    {
        $craftIDs = self::getCraftIDs($itemId);
        $craftIDsImpl = implode(',', $craftIDs);
        $allMats = Mat::allPotentialMats($itemId);
        $allMatsImpl = implode(',', $allMats);

        $qwe = qwe("
            select crafts.*, 
                   doods.name as  doodName 
            from crafts 
                 inner join items on crafts.resultItemId = items.id
                    and items.onOff
                    and crafts.onOff
                    and (
                            resultItemId in ($allMatsImpl) 
                            or crafts.id in ($craftIDsImpl)
                        )
                 left join doods on doods.id = crafts.doodId          
            order by deep desc, resultItemId"
        );
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        $Crafts = $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
        $Crafts = self::initDataInList($Crafts);
        $List = [];
        foreach ($Crafts as $craft){
            $List[$craft->resultItemId][] = $craft;
        }
        return $List;
    }



}