<?php

namespace Craft;

use PDO, Prof;

class Craft
{
    public int $id;
    /**
     * @var array<Mat>|null
     */
    public array|null  $Mats;
    public int|null    $result_item_id;
    public int|float   $result_amount = 1;
    public int|null    $dood_id;
    public string|null $dood_name;
    public int         $prof_id       = 25;
    public int|null    $prof_need;
    public Prof|null   $Prof;

    public function __set(string $name, $value): void{}

    public static function byId(int $id) : self|bool
    {
        $qwe = qwe("
            select *, craft_id id 
            from crafts 
            inner join items on items.item_id = crafts.result_item_id
                and items.on_off
                and crafts.on_off                     
                and crafts.craft_id = :id
            inner join doods on doods.dood_id = crafts.dood_id",
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
        $this->Mats = Mat::getList($this->id);
    }

    private function initProf(): void
    {
        $Prof = Prof::byNeed($this->prof_id, $this->prof_need);
        if($Prof){
            $this->Prof = $Prof;
        }
    }

    /**
     * @return array<self>|bool
     */
    public static function getList(int $item_id) : array|bool
    {
        $qwe = qwe("select *, craft_id id 
            from crafts 
            inner join items on items.item_id = crafts.result_item_id
                and items.on_off
                and crafts.on_off                   
                and crafts.result_item_id = :item_id
            inner join doods on doods.dood_id = crafts.dood_id",
        ['item_id'=>$item_id]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        $Crafts = $qwe->fetchAll(PDO::FETCH_CLASS,self::class);
        $List = [];
        foreach ($Crafts as $craft){
            $craft->initMats();
            //var_dump($craft->prof_need);
            $craft->initProf();
            $List[] = $craft;
        }
        return $List;
    }

}