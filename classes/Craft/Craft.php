<?php

namespace Craft;

use PDO, Prof;

class Craft
{
    public int $id;
    /**
     * @var array<Mat>|null
     */
    public ?array    $Mats;
    public ?int      $resultItemId;
    public int|float $resultAmount = 1;
    public ?int      $doodId;
    public ?string   $doodName;
    public int       $profId       = 25;
    public ?int      $profNeed;
    public ?Prof     $Prof;

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
        $Craft->initMats();
        $Craft->initProf();
        return $Craft;
    }

    private function initMats(): void
    {
        if($Mats = Mat::getList($this->id)){
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

        $List = [];
        foreach ($Crafts as $craft){
            $craft->initMats();
            $craft->initProf();
            $List[] = $craft;
        }
        return $List;
    }

}